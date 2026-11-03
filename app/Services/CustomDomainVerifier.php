<?php

namespace App\Services;

use App\Models\School;

/**
 * Verifies a principal-configured custom domain by performing live DNS lookups:
 *   1. The domain must point at this server (A record → server IP, or CNAME →
 *      the platform CNAME target).
 *   2. An ownership TXT record `_nshl-verify.<domain>` must contain the random
 *      token generated when the domain was saved.
 *
 * When both pass the school is marked verified and the domain goes live.
 * Nothing here performs any outbound request to the domain itself.
 */
class CustomDomainVerifier
{
    public function verify(School $school): array
    {
        $domain = $school->custom_domain;

        if (empty($domain)) {
            return $this->result(false, 'No custom domain is configured.', []);
        }

        // Expected server IP (configured, otherwise resolved from the platform domain).
        $serverIp = config('app.server_ip');
        if (empty($serverIp)) {
            $resolved = @gethostbyname(config('app.domain', 'nshlms.com'));
            $serverIp = ($resolved && $resolved !== config('app.domain', 'nshlms.com')) ? $resolved : null;
        }

        $cnameTarget = strtolower((string) config('app.domain_cname_target', 'target.' . config('app.domain', 'nshlms.com')));
        $token = $school->custom_domain_token;

        $checks = [];

        // ── Check 1: the domain points to this server ────────────────────────
        // The A record must resolve to the ORIGIN server IP (not a Cloudflare proxy
        // IP — pointing to Cloudflare IPs causes Error 1000 loops).  Alternatively
        // a CNAME to the platform domain works for subdomains (DNS-only mode).
        $pointsOk = false;
        $pointingDetail = '';

        $aRecords = @dns_get_record($domain, DNS_A) ?: [];
        $resolvedIps = collect($aRecords)->pluck('ip')->filter()->unique()->values()->all();

        // Build the list of acceptable IPs: configured SERVER_IP (origin) plus
        // any IPs the platform domain itself resolves to (covers multi-IP origins).
        $expectedIps = [];
        if (! empty($serverIp)) {
            $expectedIps[] = $serverIp;
        }
        foreach ((@dns_get_record(config('app.domain', 'nshlms.com'), DNS_A) ?: []) as $pr) {
            if (! empty($pr['ip'])) $expectedIps[] = $pr['ip'];
        }
        $expectedIps = array_unique($expectedIps);

        // Exact IP match against the origin server?
        $matchedIps = !empty($resolvedIps) ? array_intersect($resolvedIps, $expectedIps) : [];

        if (! empty($matchedIps)) {
            $pointsOk = true;
            $pointingDetail = "A record resolves to " . implode(', ', $matchedIps) . " (matches origin server).";
        } else {
            // Check CNAME to the platform domain / CNAME target
            $cnameRecords = @dns_get_record($domain, DNS_CNAME) ?: [];
            $cnameTargets = collect($cnameRecords)->pluck('target')->map(fn ($t) => strtolower(rtrim($t, '.')))->all();
            $acceptableCnames = array_unique(array_filter([
                $cnameTarget,
                strtolower(config('app.domain', 'nshlms.com')),
            ]));
            $matchedCname = !empty(array_intersect($cnameTargets, $acceptableCnames));

            if ($matchedCname) {
                $pointsOk = true;
                $pointingDetail = "CNAME points to " . implode(' / ', array_intersect($cnameTargets, $acceptableCnames)) . ".";
            } else {
                $pointingDetail = ! empty($resolvedIps)
                    ? 'A record resolves to ' . implode(', ', $resolvedIps)
                        . ' — expected origin IP ' . ($serverIp ?: implode(' or ', $expectedIps))
                        . '. If using Cloudflare, point the A record to your origin server IP, NOT a Cloudflare proxy IP (causes Error 1000).'
                    : 'No A or CNAME record found yet (DNS may still be propagating).';
            }
        }

        $checks[] = [
            'label'  => 'Domain points to this server',
            'passed' => $pointsOk,
            'detail' => $pointingDetail,
        ];

        // ── Check 2: ownership TXT record matches the token ──────────────────
        $txtOk = false;
        $txtDetail = '';

        if (empty($token)) {
            $txtDetail = 'No verification token on file — re-save the domain to generate one.';
        } else {
            $txtHost = '_nshl-verify.' . $domain;
            $txtRecords = @dns_get_record($txtHost, DNS_TXT) ?: [];
            $values = collect($txtRecords)
                ->flatMap(fn ($r) => $r['entries'] ?? [$r['txt'] ?? ''])
                ->map(fn ($v) => trim($v, '"'))
                ->all();

            if (in_array($token, $values, true)) {
                $txtOk = true;
                $txtDetail = "TXT record for {$txtHost} matches the token.";
            } else {
                $txtDetail = empty($values)
                    ? "No TXT record found at {$txtHost} yet."
                    : "TXT found but does not match the expected token.";
            }
        }

        $checks[] = [
            'label'  => 'Ownership TXT record',
            'passed' => $txtOk,
            'detail' => $txtDetail,
        ];

        $ok = $pointsOk && $txtOk;

        $message = $ok
            ? 'Domain verified successfully — your website is now live on this domain.'
            : 'Verification incomplete. Complete the DNS records shown above, wait a few minutes for propagation, then try again.';

        return $this->result($ok, $message, $checks);
    }

    private function result(bool $ok, string $message, array $checks): array
    {
        return [
            'ok'     => $ok,
            'message' => $message,
            'checks' => $checks,
        ];
    }
}

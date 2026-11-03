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
        $pointsOk = false;
        $pointingDetail = '';

        $aRecords = @dns_get_record($domain, DNS_A) ?: [];
        $resolvedIps = collect($aRecords)->pluck('ip')->filter()->unique()->values()->all();

        // Get ALL IPs the platform domain resolves to (may be multiple behind a CDN)
        $platformIps = [];
        foreach ((@dns_get_record(config('app.domain', 'nshlms.com'), DNS_A) ?: []) as $pr) {
            if (!empty($pr['ip'])) $platformIps[] = $pr['ip'];
        }
        if (!empty($serverIp)) $platformIps[] = $serverIp;
        $platformIps = array_unique($platformIps);

        // Exact IP match?
        $exactMatch = !empty($resolvedIps) && !empty(array_intersect($resolvedIps, $platformIps));

        if ($exactMatch) {
            $pointsOk = true;
            $matched = array_intersect($resolvedIps, $platformIps);
            $pointingDetail = "A record resolves to " . implode(', ', $matched) . " (matches server).";
        } else {
            // Check CNAME
            $cnameRecords = @dns_get_record($domain, DNS_CNAME) ?: [];
            $cnameTargets = collect($cnameRecords)->pluck('target')->map(fn ($t) => strtolower(rtrim($t, '.')))->all();
            if (in_array($cnameTarget, $cnameTargets, true)) {
                $pointsOk = true;
                $pointingDetail = "CNAME points to {$cnameTarget}.";
            } elseif ($this->isSameCloudflareRange($resolvedIps, $platformIps)) {
                // Both behind Cloudflare but different IPs from the same pool — same infra
                $pointsOk = true;
                $pointingDetail = "A record resolves to " . implode(', ', $resolvedIps)
                    . " — same Cloudflare infrastructure as server ("
                    . implode(', ', $platformIps) . ").";
            } else {
                $pointingDetail = ! empty($resolvedIps)
                    ? 'A record resolves to ' . implode(', ', $resolvedIps)
                        . ($serverIp ? " (expected {$serverIp})." : '.')
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

    /**
     * Check whether the custom domain's IPs and the platform's IPs are
     * both in the same Cloudflare IP range — meaning the custom domain is
     * proxied through the same CDN infrastructure as the server.
     *
     * Cloudflare uses these ranges: 104.21.0.0/16, 172.67.0.0/16,
     * 104.16.0.0/13, 141.101.64.0/18, 108.162.192.0/18, 188.114.96.0/20, etc.
     * For simplicity we check the two common /16 prefixes.
     */
    private function isSameCloudflareRange(array $domainIps, array $serverIps): bool
    {
        if (empty($domainIps) || empty($serverIps)) {
            return false;
        }

        // Cloudflare /16 prefixes commonly used for A records
        $cloudflareRanges = ['104.21.', '172.67.', '104.16.', '104.17.', '104.18.', '104.19.', '104.20.'];

        $domainIsCloudflare = false;
        $serverIsCloudflare  = false;

        foreach ($domainIps as $ip) {
            foreach ($cloudflareRanges as $range) {
                if (str_starts_with($ip, $range)) {
                    $domainIsCloudflare = true;
                    break 2;
                }
            }
        }

        foreach ($serverIps as $ip) {
            foreach ($cloudflareRanges as $range) {
                if (str_starts_with($ip, $range)) {
                    $serverIsCloudflare = true;
                    break 2;
                }
            }
        }

        return $domainIsCloudflare && $serverIsCloudflare;
    }
}

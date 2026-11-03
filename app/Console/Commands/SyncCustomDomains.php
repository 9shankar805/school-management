<?php

namespace App\Console\Commands;

use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Synchronises verified school custom domains into the live nginx + Apache
 * configurations so the web server routes them to the Laravel app.
 *
 * Run via the Laravel scheduler every minute (as root via cron). The command
 * is idempotent — it rebuilds the server_name / ServerAlias lines from the
 * database each run, so domains that are removed are cleaned up too.
 *
 *   php artisan custom-domain:sync
 */
class SyncCustomDomains extends Command
{
    protected $signature = 'custom-domain:sync';
    protected $description = 'Register verified custom domains with nginx + Apache';

    private const NGINX_CONF  = '/etc/nginx/sites-available/school-custom-domains.conf';
    private const APACHE_CONF = '/etc/apache2/sites-enabled/nshlms.conf';

    public function handle(): int
    {
        // ── 1. Collect every verified, active custom domain ──────────────
        $domains = School::whereNotNull('custom_domain')
            ->whereNotNull('custom_domain_verified_at')
            ->where('status', 'active')
            ->where('custom_domain', '!=', '')
            ->pluck('custom_domain')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($domains)) {
            $this->info('No verified custom domains to sync.');
            return self::SUCCESS;
        }

        // Build the full hostname list: domain + www.domain for each
        $hostnames = [];
        foreach ($domains as $d) {
            $hostnames[] = $d;
            $hostnames[] = 'www.' . $d;
        }
        $hostnameLine = implode(' ', array_unique($hostnames));

        $this->info('Syncing ' . count($domains) . ' custom domain(s): ' . implode(', ', $domains));

        $changed = false;

        // ── 2. Update nginx config ────────────────────────────────────────
        $changed = $this->syncNginx($hostnameLine) || $changed;

        // ── 3. Update Apache ServerAlias ─────────────────────────────────
        $changed = $this->syncApache($hostnameLine) || $changed;

        // ── 4. Reload services if anything changed ────────────────────────
        if ($changed) {
            $this->reloadServices();
        } else {
            $this->info('Configs already up to date — no reload needed.');
        }

        return self::SUCCESS;
    }

    /**
     * Rebuild the server_name lines in the nginx custom-domains config.
     * Uses special comment markers (# MANAGED:HTTP:SERVER_NAME and
     * # MANAGED:HTTPS:SERVER_NAME) to target only the correct lines —
     * so normal text/comment lines are never accidentally modified.
     */
    private function syncNginx(string $hostnameLine): bool
    {
        if (! file_exists(self::NGINX_CONF)) {
            $this->warn('nginx custom-domains config not found — skipping nginx.');
            return false;
        }

        $content = file_get_contents(self::NGINX_CONF);

        // Replace the server_name line that comes AFTER each managed marker comment
        // Pattern: marker on its own line, followed by the server_name line
        $pattern = '/([ \t]*#[ \t]*MANAGED:[A-Z:]+SERVER_NAME\n[ \t]*)server_name\s+[^;]+;/';

        $newContent = preg_replace_callback($pattern, function ($m) use ($hostnameLine) {
            return $m[1] . "server_name {$hostnameLine};";
        }, $content);

        if ($newContent === $content) {
            return false; // nothing changed
        }

        file_put_contents(self::NGINX_CONF, $newContent);
        $this->info("  nginx: server_name updated → {$hostnameLine}");
        return true;
    }

    /**
     * Rebuild the ServerAlias line in the Apache nshlms virtual host.
     * Preserves the original www.nshlms.com *.nshlms.com entries.
     */
    private function syncApache(string $hostnameLine): bool
    {
        if (! file_exists(self::APACHE_CONF)) {
            $this->warn('Apache nshlms.conf not found — skipping Apache.');
            return false;
        }

        $content  = file_get_contents(self::APACHE_CONF);
        $baseAlias = 'www.nshlms.com *.nshlms.com';
        $fullAlias = $baseAlias . ' ' . $hostnameLine;

        // Match the existing ServerAlias line
        $pattern = '/ServerAlias\s+[^\n]+/';

        if (! preg_match($pattern, $content)) {
            return false; // no ServerAlias line found
        }

        $existingAlias = trim(preg_replace('/^ServerAlias\s+/', '', preg_match($pattern, $content, $m) ? $m[0] : ''));

        if ($existingAlias === $fullAlias) {
            return false; // already in sync
        }

        $newContent = preg_replace($pattern, 'ServerAlias ' . $fullAlias, $content);
        file_put_contents(self::APACHE_CONF, $newContent);
        $this->info('  apache: ServerAlias updated → ' . $fullAlias);
        return true;
    }

    /**
     * Test and reload nginx + Apache. Logs failures but doesn't crash.
     */
    private function reloadServices(): void
    {
        // Test + reload nginx
        exec('nginx -t 2>&1', $nginxOut, $nginxCode);
        if ($nginxCode === 0) {
            exec('systemctl reload nginx 2>&1');
            $this->info('  nginx: reloaded OK');
        } else {
            $this->error('  nginx: config test FAILED — not reloaded');
            Log::error('custom-domain:sync nginx test failed', ['output' => implode("\n", $nginxOut)]);
        }

        // Test + reload Apache
        exec('apache2ctl configtest 2>&1', $apacheOut, $apacheCode);
        if ($apacheCode === 0) {
            exec('systemctl reload apache2 2>&1');
            $this->info('  apache: reloaded OK');
        } else {
            $this->error('  apache: config test FAILED — not reloaded');
            Log::error('custom-domain:sync apache test failed', ['output' => implode("\n", $apacheOut)]);
        }
    }
}

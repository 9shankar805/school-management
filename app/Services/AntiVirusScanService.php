<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Anti-virus scan hook for uploaded files.
 *
 * ─── Default behaviour ───────────────────────────────────────────────────────
 * When AV_SCAN_ENABLED=false (the default), every file is considered clean.
 * This keeps the app functional on servers without ClamAV installed.
 *
 * ─── Enabling ClamAV ─────────────────────────────────────────────────────────
 * 1. Install ClamAV on the server:
 *      sudo apt-get install clamav clamav-daemon   (Debian/Ubuntu)
 *      sudo yum install clamav clamav-update        (RHEL/Amazon Linux)
 * 2. Update virus definitions:  sudo freshclam
 * 3. Ensure `clamscan` is in $PATH.
 * 4. Set in .env:
 *      AV_SCAN_ENABLED=true
 *      AV_SCAN_BINARY=/usr/bin/clamscan    (path to clamscan binary)
 *
 * ─── Alternative: socket-based ClamAV daemon ─────────────────────────────────
 * Point AV_SCAN_BINARY at a wrapper script that uses `clamdscan --stream`
 * for faster scans via the ClamAV daemon socket instead of forking clamscan.
 *
 * ─── Alternative: cloud AV API ───────────────────────────────────────────────
 * Swap the scan() implementation to POST the file to VirusTotal, Cloudmersive,
 * or any REST-based AV API. Inject an HTTP client and resolve from the container.
 */
class AntiVirusScanService
{
    private bool   $enabled;
    private string $binary;
    private int    $timeoutSeconds;

    public function __construct()
    {
        $this->enabled        = (bool) env('AV_SCAN_ENABLED', false);
        $this->binary         = (string) env('AV_SCAN_BINARY', '/usr/bin/clamscan');
        $this->timeoutSeconds = (int) env('AV_SCAN_TIMEOUT', 30);
    }

    /**
     * Scan the file at $absolutePath.
     *
     * @param  string  $absolutePath  Real filesystem path to the uploaded tmp file.
     * @return AntiVirusScanResult
     */
    public function scan(string $absolutePath): AntiVirusScanResult
    {
        if (! $this->enabled) {
            return AntiVirusScanResult::clean();
        }

        if (! file_exists($absolutePath)) {
            Log::warning('AV scan: file not found', ['path' => $absolutePath]);
            return AntiVirusScanResult::clean(); // cannot scan; let other checks handle
        }

        return $this->runClamscan($absolutePath);
    }

    // -----------------------------------------------------------------------

    private function runClamscan(string $path): AntiVirusScanResult
    {
        // Build command — no shell expansion, use array form via proc_open
        $cmd = [
            $this->binary,
            '--no-summary',
            '--stdout',
            '--infected',
            $path,
        ];

        $process = proc_open(
            $cmd,
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
        );

        if (! is_resource($process)) {
            Log::error('AV scan: failed to start clamscan process');
            return AntiVirusScanResult::clean(); // fail open — log and proceed
        }

        $stdout = '';
        $deadline = time() + $this->timeoutSeconds;

        while (! feof($pipes[1]) && time() < $deadline) {
            $stdout .= fread($pipes[1], 4096);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // clamscan exit codes: 0 = clean, 1 = infected found, 2 = error
        if ($exitCode === 0) {
            return AntiVirusScanResult::clean();
        }

        if ($exitCode === 1) {
            // stdout line format: "/path/to/file: Eicar-Test-Signature FOUND"
            $threat = trim($stdout) ?: 'Unknown threat';
            Log::error('AV scan: threat detected', ['path' => $path, 'output' => $threat]);
            return AntiVirusScanResult::infected($threat);
        }

        // exit 2 = scan error (permissions, corrupt DB, etc.)
        Log::warning('AV scan: clamscan returned error', ['exit' => $exitCode, 'output' => $stdout]);
        return AntiVirusScanResult::clean(); // fail open on scan errors
    }
}

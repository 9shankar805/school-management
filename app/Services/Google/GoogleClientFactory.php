<?php

namespace App\Services\Google;

use App\Models\School;
use App\Models\SchoolGoogleConfig;
use Google\Client as GoogleClient;
use Exception;

class GoogleClientFactory
{
    /**
     * Resolve and configure a Google Client for a specific school
     */
    public static function forSchool(int|School $school): GoogleClient
    {
        $schoolId = $school instanceof School ? $school->id : (int) $school;

        $config = SchoolGoogleConfig::withoutGlobalScopes()
            ->where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            throw new Exception("Google Education Workspace is not configured or is inactive for school ID #{$schoolId}.");
        }

        return static::fromConfig($config);
    }

    /**
     * Build Google Client from a SchoolGoogleConfig instance
     */
    public static function fromConfig(SchoolGoogleConfig $config): GoogleClient
    {
        if (empty($config->service_account_json)) {
            throw new Exception("Google Service Account credentials have not been configured for {$config->workspace_domain}.");
        }

        $credentials = json_decode($config->service_account_json, true);
        if (!is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new Exception("Invalid or corrupt Google Service Account JSON key.");
        }

        $client = new GoogleClient();
        $client->setAuthConfig($credentials);
        $client->setApplicationName("NepalSchoolHub - " . ($config->workspace_domain ?? 'Education'));

        // Domain-wide delegation impersonation (impersonate school administrator)
        if (!empty($config->admin_email)) {
            $client->setSubject($config->admin_email);
        }

        // Essential Google Workspace for Education Scopes matching Domain-Wide Delegation
        $client->setScopes([
            'https://www.googleapis.com/auth/admin.directory.user',
            'https://www.googleapis.com/auth/classroom.courses',
            'https://www.googleapis.com/auth/classroom.rosters',
            'https://www.googleapis.com/auth/classroom.profile.emails',
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/calendar',
        ]);

        return $client;
    }

    /**
     * Test connection & verify authorization for a school
     */
    public static function testConnection(SchoolGoogleConfig $config): array
    {
        try {
            $client = static::fromConfig($config);

            // 1. Test token retrieval
            $token = $client->fetchAccessTokenWithAssertion();
            if (isset($token['error'])) {
                throw new Exception($token['error_description'] ?? $token['error']);
            }

            // 2. Test Classroom API read access
            $classroom = new \Google\Service\Classroom($client);
            $courses = $classroom->courses->listCourses(['pageSize' => 5]);
            $courseCount = count($courses->getCourses() ?? []);

            $config->update([
                'connection_status'  => 'connected',
                'last_error_message' => null,
            ]);

            return [
                'success' => true,
                'message' => "Successfully connected to Google Workspace domain '{$config->workspace_domain}'! Authenticated as {$config->admin_email}.",
                'sample_course_count' => $courseCount,
            ];
        } catch (\Throwable $e) {
            $config->update([
                'connection_status'  => 'error',
                'last_error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Google API authentication failed: " . $e->getMessage(),
            ];
        }
    }
}


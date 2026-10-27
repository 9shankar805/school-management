<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidEmailDomain implements Rule
{
    /**
     * Known disposable / temporary spam email domains
     */
    protected static array $blockedDomains = [
        'mailinator.com', '10minutemail.com', '10minutemail.net', 'guerrillamail.com',
        'guerrillamail.net', 'guerrillamail.org', 'tempmail.com', 'tempmail.net',
        'temp-mail.org', 'yopmail.com', 'yopmail.fr', 'yopmail.net', 'trashmail.com',
        'trashmail.net', 'trashmail.me', 'getairmail.com', 'sharklasers.com',
        'dispostable.com', 'throwawaymail.com', 'maildrop.cc', 'mohmal.com',
        'crazymailing.com', 'nada.ltd', 'burnermail.io', 'mytemp.email',
        'fakeinbox.com', 'getnada.com', 'inboxkitten.com', 'dropmail.me',
        'generator.email', 'fakemailgenerator.com', 'emailondeck.com',
        'armyspy.com', 'cuvox.de', 'dayrep.com', 'einrot.com', 'fleckens.hu',
        'gustr.com', 'jourrapide.com', 'rhyta.com', 'superrito.com', 'teleworm.us',
    ];

    protected string $errorMessage = 'The email address domain is invalid or temporary.';

    public function passes($attribute, $value): bool
    {
        if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errorMessage = 'Please provide a valid email address.';
            return false;
        }

        $parts = explode('@', strtolower(trim($value)));
        if (count($parts) !== 2) {
            return false;
        }

        $domain = $parts[1];

        // 1. Block disposable / fake temporary email providers
        if (in_array($domain, static::$blockedDomains, true)) {
            $this->errorMessage = 'Temporary or disposable email addresses are not allowed. Please use a permanent email.';
            return false;
        }

        // 2. Check domain has at least one period and valid syntax
        if (!str_contains($domain, '.') || strlen($domain) < 4) {
            $this->errorMessage = 'The email domain is invalid.';
            return false;
        }

        // 3. DNS check in production (check if domain has valid MX or A records)
        if (function_exists('checkdnsrr') && config('app.env') === 'production') {
            if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
                $this->errorMessage = 'The email domain does not have active mail servers.';
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->errorMessage;
    }
}

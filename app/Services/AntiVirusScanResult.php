<?php

namespace App\Services;

/**
 * Immutable value object returned by AntiVirusScanService::scan().
 */
final class AntiVirusScanResult
{
    public function __construct(
        private readonly bool    $clean,
        private readonly ?string $threatName = null,
    ) {}

    public function isClean(): bool    { return $this->clean; }
    public function threat(): ?string  { return $this->threatName; }

    public static function clean(): self
    {
        return new self(clean: true);
    }

    public static function infected(string $threatName): self
    {
        return new self(clean: false, threatName: $threatName);
    }
}

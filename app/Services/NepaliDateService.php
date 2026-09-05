<?php

namespace App\Services;

/**
 * NepaliDateService — Bikram Sambat (BS) ↔ Gregorian (AD) conversion.
 *
 * Pure PHP implementation; no external package required.
 * Algorithm: reference epoch 2000-01-01 AD = 2056-09-17 BS.
 *
 * Usage:
 *   $bs  = NepaliDateService::adToBS('2026-04-14');       // '2083-01-01'
 *   $ad  = NepaliDateService::bsToAD(2083, 1, 1);         // '2026-04-14'
 *   $fmt = NepaliDateService::formatBS('2026-04-14');      // '२०८३ बैशाख १'
 *   $fmt = NepaliDateService::formatBS('2026-04-14', false); // '2083 Baisakh 1'
 */
class NepaliDateService
{
    // ── Days in each BS month for years 2000–2090 BS ──────────────────────────
    // Index 0 = year 2000 BS, each sub-array = 12 months (Baisakh … Chaitra)
    private static array $bsCalendarData = [
        // 2000
        [30,32,31,32,31,30,30,30,29,30,29,31],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [30,32,31,32,31,30,30,30,29,30,29,31],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,29,31],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        // 2010
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        // 2020
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        // 2030
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        // 2040
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        // 2050
        [31,32,31,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        // 2060
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,30],
        [31,32,31,32,31,30,30,30,29,30,29,31],
        [30,32,31,32,31,30,30,30,29,30,29,31],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        // 2070
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        // 2080
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        [31,31,32,32,31,30,30,29,30,29,30,30],
        [31,32,31,32,31,30,30,30,29,29,30,31],
        [31,31,31,32,31,31,29,30,30,29,30,30],
        [31,31,32,31,31,31,30,29,30,29,30,30],
        // 2090
        [31,31,32,32,31,30,30,29,30,29,30,30],
    ];

    // Reference point: 1944-01-01 AD = 2000-09-17 BS
    private const REFERENCE_AD_YEAR  = 1944;
    private const REFERENCE_AD_MONTH = 1;
    private const REFERENCE_AD_DAY   = 1;
    private const REFERENCE_BS_YEAR  = 2000;
    private const REFERENCE_BS_MONTH = 9;
    private const REFERENCE_BS_DAY   = 17;

    private static array $monthsEn = [
        'Baisakh','Jestha','Ashadh','Shrawan',
        'Bhadra','Ashwin','Kartik','Mangsir',
        'Poush','Magh','Falgun','Chaitra',
    ];

    private static array $monthsNp = [
        'बैशाख','जेठ','असार','श्रावण',
        'भाद्र','आश्विन','कार्तिक','मंसिर',
        'पौष','माघ','फाल्गुन','चैत्र',
    ];

    private static array $nepaliDigits = ['०','१','२','३','४','५','६','७','८','९'];

    // ── AD → BS ──────────────────────────────────────────────────────────────

    /**
     * Convert an AD date string (YYYY-MM-DD) to a BS date string (YYYY-MM-DD).
     * Returns null if the date is outside supported range (1944–2033 AD).
     */
    public static function adToBS(string $adDate): ?string
    {
        try {
            [$adY, $adM, $adD] = array_map('intval', explode('-', $adDate));
        } catch (\Throwable $e) {
            return null;
        }

        // Count total AD days from reference date
        $adRef = mktime(0, 0, 0, self::REFERENCE_AD_MONTH, self::REFERENCE_AD_DAY, self::REFERENCE_AD_YEAR);
        $adCur = mktime(0, 0, 0, $adM, $adD, $adY);
        $totalDays = (int) round(($adCur - $adRef) / 86400);

        if ($totalDays < 0) {
            return null; // Before reference date
        }

        // Walk through BS calendar
        $bsY = self::REFERENCE_BS_YEAR;
        $bsM = self::REFERENCE_BS_MONTH;
        $bsD = self::REFERENCE_BS_DAY;

        while ($totalDays > 0) {
            $yearIdx = $bsY - 2000;
            if (! isset(self::$bsCalendarData[$yearIdx])) {
                return null; // Out of range
            }
            $daysInMonth = self::$bsCalendarData[$yearIdx][$bsM - 1];
            $remaining = $daysInMonth - $bsD;

            if ($totalDays <= $remaining) {
                $bsD += $totalDays;
                $totalDays = 0;
            } else {
                $totalDays -= ($remaining + 1);
                $bsD = 1;
                $bsM++;
                if ($bsM > 12) {
                    $bsM = 1;
                    $bsY++;
                }
            }
        }

        return sprintf('%04d-%02d-%02d', $bsY, $bsM, $bsD);
    }

    // ── BS → AD ──────────────────────────────────────────────────────────────

    /**
     * Convert BS year/month/day to AD date string (YYYY-MM-DD).
     */
    public static function bsToAD(int $bsY, int $bsM, int $bsD): ?string
    {
        if ($bsY < 2000 || $bsY > 2090) {
            return null;
        }

        // Count total BS days from reference point
        $totalDays = 0;

        // Days remaining in reference BS month
        $refYearIdx = self::REFERENCE_BS_YEAR - 2000;
        $totalDays += self::$bsCalendarData[$refYearIdx][self::REFERENCE_BS_MONTH - 1] - self::REFERENCE_BS_DAY;

        // Full months after reference month in reference year
        for ($m = self::REFERENCE_BS_MONTH + 1; $m <= 12; $m++) {
            $totalDays += self::$bsCalendarData[$refYearIdx][$m - 1];
        }

        // Full BS years from (reference year + 1) up to (target year - 1)
        for ($y = self::REFERENCE_BS_YEAR + 1; $y < $bsY; $y++) {
            $yIdx = $y - 2000;
            if (! isset(self::$bsCalendarData[$yIdx])) return null;
            foreach (self::$bsCalendarData[$yIdx] as $days) {
                $totalDays += $days;
            }
        }

        if ($bsY > self::REFERENCE_BS_YEAR) {
            // Full months in target year up to target month - 1
            $tYIdx = $bsY - 2000;
            for ($m = 1; $m < $bsM; $m++) {
                $totalDays += self::$bsCalendarData[$tYIdx][$m - 1];
            }
            // Days in target month
            $totalDays += $bsD;
        } else {
            // Same year as reference — just count days within reference month
            $totalDays = $bsD - self::REFERENCE_BS_DAY;
        }

        // Add total days to reference AD date
        $adTimestamp = mktime(0, 0, 0, self::REFERENCE_AD_MONTH, self::REFERENCE_AD_DAY + $totalDays, self::REFERENCE_AD_YEAR);
        return date('Y-m-d', $adTimestamp);
    }

    // ── Formatting ────────────────────────────────────────────────────────────

    /**
     * Format an AD date string as a BS date string.
     *
     * @param  string $adDate    YYYY-MM-DD Gregorian date
     * @param  bool   $nepali    true = Devanagari, false = English
     * @param  string $format    'short' = YYYY-MM-DD | 'long' = 'YYYY MonthName DD'
     * @return string            e.g. '२०८३ बैशाख १४' or '2083 Baisakh 14'
     */
    public static function formatBS(string $adDate, bool $nepali = true, string $format = 'long'): string
    {
        $bs = self::adToBS($adDate);
        if (! $bs) {
            return $adDate; // Fall back to raw AD date
        }

        [$y, $m, $d] = array_map('intval', explode('-', $bs));

        if ($format === 'short') {
            if ($nepali) {
                return self::toNepaliDigits($y) . '-'
                    . self::toNepaliDigits($m, 2) . '-'
                    . self::toNepaliDigits($d, 2);
            }
            return $bs;
        }

        // Long format
        $monthName = $nepali
            ? (self::$monthsNp[$m - 1] ?? "M{$m}")
            : (self::$monthsEn[$m - 1] ?? "M{$m}");

        if ($nepali) {
            return self::toNepaliDigits($y) . ' ' . $monthName . ' ' . self::toNepaliDigits($d);
        }
        return "{$y} {$monthName} {$d}";
    }

    /**
     * Quick BS year from AD date string.
     */
    public static function bsYear(string $adDate): ?int
    {
        $bs = self::adToBS($adDate);
        return $bs ? (int) explode('-', $bs)[0] : null;
    }

    /**
     * Get the current Nepal fiscal year label (e.g. "2081/82").
     * Nepal fiscal year starts 1 Shrawan (mid-July).
     */
    public static function currentFiscalYear(): string
    {
        $today = date('Y-m-d');
        $bs = self::adToBS($today);
        if (! $bs) return '';
        [$y, $m] = array_map('intval', explode('-', $bs));
        // Fiscal year starts Shrawan (month 4)
        if ($m >= 4) {
            return "{$y}/" . substr((string)($y + 1), 2);
        } else {
            return ($y - 1) . '/' . substr((string)$y, 2);
        }
    }

    /**
     * Convert an integer to Nepali (Devanagari) digit string.
     */
    public static function toNepaliDigits(int $number, int $pad = 0): string
    {
        $str = $pad > 0 ? str_pad((string)$number, $pad, '0', STR_PAD_LEFT) : (string)$number;
        $result = '';
        foreach (str_split($str) as $ch) {
            $result .= self::$nepaliDigits[(int)$ch];
        }
        return $result;
    }

    /**
     * Get BS month name for a given AD date.
     *
     * @param  string $adDate  YYYY-MM-DD
     * @param  bool   $nepali  Devanagari or English name
     */
    public static function bsMonthName(string $adDate, bool $nepali = true): string
    {
        $bs = self::adToBS($adDate);
        if (! $bs) return '';
        $m = (int) explode('-', $bs)[1];
        return $nepali
            ? (self::$monthsNp[$m - 1] ?? '')
            : (self::$monthsEn[$m - 1] ?? '');
    }
}

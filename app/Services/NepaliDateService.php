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

    /**
     * Standard Nepali Tithis list (15 in Shukla Paksha + 15 in Krishna Paksha).
     */
    public static array $tithis = [
        'प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पञ्चमी',
        'षष्ठी', 'सप्तमी', 'अष्टमी', 'नवमी', 'दशमी',
        'एकादशी', 'द्वादशी', 'त्रयोदशी', 'चतुर्दशी', 'पूर्णिमा',
        'प्रतिपदा', 'द्वितीया', 'तृतीया', 'चतुर्थी', 'पञ्चमी',
        'षष्ठी', 'सप्तमी', 'अष्टमी', 'नवमी', 'दशमी',
        'एकादशी', 'द्वादशी', 'त्रयोदशी', 'चतुर्दशी', 'औंसी'
    ];

    /**
     * Complete Nepal National Festivals, Tithis, and Public Holidays by BS Month.
     */
    public static function getMonthEventsData(int $bsMonth): array
    {
        // Reference lookup for month 1 (Baisakh) to 12 (Chaitra)
        $monthFestivals = [
            1 => [ // बैशाख (Baisakh)
                1 => ['title' => 'नयाँ वर्ष / बिस्केट जात्रा प्रारम्भ', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                5 => ['title' => 'मातातीर्थ औंसी (आमाको मुख हेर्ने दिन)', 'tithi' => 'औंसी', 'is_holiday' => false, 'category' => 'celebration'],
                11 => ['title' => 'लोकतन्त्र दिवस', 'tithi' => 'षष्ठी', 'is_holiday' => true, 'category' => 'holiday'],
                18 => ['title' => 'अन्तर्राष्ट्रिय मजदुर दिवस (May Day)', 'tithi' => 'त्रयोदशी', 'is_holiday' => true, 'category' => 'holiday'],
                25 => ['title' => 'बुद्ध जयन्ती / उभौली पर्व / चण्डी पूर्णिमा', 'tithi' => 'पूर्णिमा', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            2 => [ // जेठ (Jestha)
                11 => ['title' => 'मोहिनी एकादशी व्रत', 'tithi' => 'एकादशी', 'is_holiday' => false, 'category' => 'academic'],
                15 => ['title' => 'गणतन्त्र दिवस (Republic Day)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                22 => ['title' => 'विश्व वातावरण दिवस (World Environment Day)', 'tithi' => 'सप्तमी', 'is_holiday' => false, 'category' => 'academic'],
                30 => ['title' => 'गंगा दशहरा / निर्जला एकादशी', 'tithi' => 'एकादशी', 'is_holiday' => false, 'category' => 'celebration'],
            ],
            3 => [ // असार (Ashadh)
                8 => ['title' => 'अन्तर्राष्ट्रिय योग दिवस (Yoga Day)', 'tithi' => 'सप्तमी', 'is_holiday' => false, 'category' => 'academic'],
                15 => ['title' => 'राष्ट्रिय धान दिवस / असार १५ (दही चिउरा खाने दिन)', 'tithi' => 'चतुर्दशी', 'is_holiday' => false, 'category' => 'celebration'],
                29 => ['title' => 'भानु जयन्ती / गुरु पूर्णिमा', 'tithi' => 'पूर्णिमा', 'is_holiday' => false, 'category' => 'celebration'],
            ],
            4 => [ // श्रावण (Shrawan)
                1 => ['title' => 'साउने संक्रान्ति / लुतो फाल्ने दिन', 'tithi' => 'प्रतिपदा', 'is_holiday' => false, 'category' => 'celebration'],
                15 => ['title' => 'खीर खाने दिन', 'tithi' => 'पूर्णिमा', 'is_holiday' => false, 'category' => 'celebration'],
                20 => ['title' => 'नाग पञ्चमी', 'tithi' => 'पञ्चमी', 'is_holiday' => false, 'category' => 'celebration'],
                31 => ['title' => 'जनै पूर्णिमा / रक्षा बन्धन / गाईजात्रा', 'tithi' => 'पूर्णिमा', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            5 => [ // भाद्र (Bhadra)
                8 => ['title' => 'श्रीकृष्ण जन्माष्टमी', 'tithi' => 'अष्टमी', 'is_holiday' => true, 'category' => 'holiday'],
                15 => ['title' => 'कुशे औंसी (बुवाको मुख हेर्ने दिन / मोती जयन्ती)', 'tithi' => 'औंसी', 'is_holiday' => false, 'category' => 'celebration'],
                28 => ['title' => 'दर खाने दिन', 'tithi' => 'द्वितीया', 'is_holiday' => false, 'category' => 'celebration'],
                29 => ['title' => 'हरितालिका तीज (महिला बिदा)', 'tithi' => 'तृतीया', 'is_holiday' => true, 'category' => 'holiday'],
                31 => ['title' => 'ऋषि पञ्चमी / गणेश चतुर्थी', 'tithi' => 'पञ्चमी', 'is_holiday' => false, 'category' => 'celebration'],
            ],
            6 => [ // असोज (Ashoj)
                1 => ['title' => 'विश्वकर्मा पूजा / कन्या संक्रान्ति', 'tithi' => 'षष्ठी', 'is_holiday' => false, 'category' => 'celebration'],
                3 => ['title' => 'संविधान दिवस / राष्ट्रिय दिवस', 'tithi' => 'अष्टमी', 'is_holiday' => true, 'category' => 'holiday'],
                9 => ['title' => 'इन्द्रजात्रा (काठमाडौँ उपत्यका बिदा)', 'tithi' => 'चतुर्दशी', 'is_holiday' => true, 'category' => 'holiday'],
                11 => ['title' => 'सोह्र श्राद्ध प्रारम्भ (प्रतिपदा श्राद्ध)', 'tithi' => 'प्रतिपदा', 'is_holiday' => false, 'category' => 'academic'],
                19 => ['title' => 'विश्व शिक्षक दिवस / दशमी श्राद्ध', 'tithi' => 'दशमी', 'is_holiday' => false, 'category' => 'academic'],
                24 => ['title' => 'पितृ विसर्जन / औंसी श्राद्ध / विश्व मानसिक स्वास्थ्य दिवस', 'tithi' => 'औंसी', 'is_holiday' => true, 'category' => 'holiday'],
                25 => ['title' => 'घटस्थापना व्रत / नवरात्र आरम्भ (बडादशैं प्रारम्भ)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                27 => ['title' => 'अन्तर्राष्ट्रिय प्रकोप जोखिम न्यूनीकरण दिवस', 'tithi' => 'तृतीया', 'is_holiday' => false, 'category' => 'academic'],
                29 => ['title' => 'विश्व हातधुने दिवस / ग्रामीण महिला दिवस', 'tithi' => 'पञ्चमी', 'is_holiday' => false, 'category' => 'academic'],
                30 => ['title' => 'विश्व खाद्य दिवस', 'tithi' => 'षष्ठी', 'is_holiday' => false, 'category' => 'academic'],
                31 => ['title' => 'फूलपाती / दशैं बिदा प्रारम्भ / अन्तर्राष्ट्रिय गरिबी निवारण दिवस', 'tithi' => 'सप्तमी', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            7 => [ // कात्तिक (Kartik)
                1 => ['title' => 'महाअष्टमी / कालरात्रि (दशैं बिदा)', 'tithi' => 'अष्टमी', 'is_holiday' => true, 'category' => 'holiday'],
                2 => ['title' => 'महानवमी / आयुध पूजा (दशैं बिदा)', 'tithi' => 'नवमी', 'is_holiday' => true, 'category' => 'holiday'],
                3 => ['title' => 'विजयादशमी (दशैं टीका)', 'tithi' => 'दशमी', 'is_holiday' => true, 'category' => 'holiday'],
                4 => ['title' => 'पापांकुशा एकादशी / दशैं बिदा', 'tithi' => 'एकादशी', 'is_holiday' => true, 'category' => 'holiday'],
                8 => ['title' => 'कोजाग्रत पूर्णिमा (दशैं समापन)', 'tithi' => 'पूर्णिमा', 'is_holiday' => false, 'category' => 'celebration'],
                14 => ['title' => 'काग तिहार / यमपञ्चक प्रारम्भ', 'tithi' => 'त्रयोदशी', 'is_holiday' => false, 'category' => 'celebration'],
                15 => ['title' => 'कुकुर तिहार / नरक चतुर्दशी', 'tithi' => 'चतुर्दशी', 'is_holiday' => false, 'category' => 'celebration'],
                16 => ['title' => 'लक्ष्मी पूजा / सुखरात्रि (तिहार बिदा)', 'tithi' => 'औंसी', 'is_holiday' => true, 'category' => 'holiday'],
                17 => ['title' => 'गाई पूजा / गोवर्धन पूजा / म्ह पूजा', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                18 => ['title' => 'भाइटीका / किजापूजा (तिहार बिदा)', 'tithi' => 'द्वितीया', 'is_holiday' => true, 'category' => 'holiday'],
                22 => ['title' => 'छठ पर्व (सूर्य षष्ठी व्रत)', 'tithi' => 'षष्ठी', 'is_holiday' => true, 'category' => 'holiday'],
                27 => ['title' => 'हरिबोधिनी एकादशी (तुलसी विवाह)', 'tithi' => 'एकादशी', 'is_holiday' => false, 'category' => 'celebration'],
            ],
            8 => [ // मंसिर (Mangsir)
                10 => ['title' => 'लैंगिक हिंसा विरुद्धको १६ दिने अभियान प्रारम्भ', 'tithi' => 'दशमी', 'is_holiday' => false, 'category' => 'academic'],
                15 => ['title' => 'विश्व एड्स दिवस', 'tithi' => 'पूर्णिमा', 'is_holiday' => false, 'category' => 'academic'],
                18 => ['title' => 'अन्तर्राष्ट्रिय अपाङ्गता दिवस', 'tithi' => 'तृतीया', 'is_holiday' => false, 'category' => 'academic'],
                24 => ['title' => 'अन्तर्राष्ट्रिय मानव अधिकार दिवस', 'tithi' => 'नवमी', 'is_holiday' => false, 'category' => 'academic'],
                28 => ['title' => 'उधौली पर्व / धान्य पूर्णिमा / योमरी पुन्ही', 'tithi' => 'पूर्णिमा', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            9 => [ // पौष (Poush)
                10 => ['title' => 'क्रिसमस डे (Christmas Day)', 'tithi' => 'एकादशी', 'is_holiday' => true, 'category' => 'holiday'],
                15 => ['title' => 'तमु ल्होसार (गुरुङ समुदायको नयाँ वर्ष)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                27 => ['title' => 'पृथ्वी जयन्ती / राष्ट्रिय एकता दिवस', 'tithi' => 'त्रयोदशी', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            10 => [ // माघ (Magh)
                1 => ['title' => 'माघे संक्रान्ति / माघी पर्व (मकर संक्रान्ति)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                16 => ['title' => 'सहिद दिवस (Martyrs Day)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
                18 => ['title' => 'सोनाम ल्होसार (तामाङ नयाँ वर्ष)', 'tithi' => 'तृतीया', 'is_holiday' => true, 'category' => 'holiday'],
                23 => ['title' => 'श्रीपञ्चमी / सरस्वती पूजा (अक्षरारम्भ)', 'tithi' => 'पञ्चमी', 'is_holiday' => false, 'category' => 'celebration'],
            ],
            11 => [ // फाल्गुन (Falgun)
                1 => ['title' => 'जनयुद्ध दिवस', 'tithi' => 'त्रयोदशी', 'is_holiday' => false, 'category' => 'academic'],
                7 => ['title' => 'राष्ट्रिय प्रजातन्त्र दिवस (Democracy Day)', 'tithi' => 'सप्तमी', 'is_holiday' => true, 'category' => 'holiday'],
                12 => ['title' => 'ग्याल्पो ल्होसार (शेर्पा नयाँ वर्ष)', 'tithi' => 'द्वादशी', 'is_holiday' => true, 'category' => 'holiday'],
                17 => ['title' => 'महाशिवरात्रि / सेना दिवस', 'tithi' => 'चतुर्दशी', 'is_holiday' => true, 'category' => 'holiday'],
                24 => ['title' => 'अन्तर्राष्ट्रिय महिला दिवस (८ मार्च)', 'tithi' => 'सप्तमी', 'is_holiday' => true, 'category' => 'holiday'],
                29 => ['title' => 'फागु पूर्णिमा / होली पर्व (पहाडी बिदा)', 'tithi' => 'पूर्णिमा', 'is_holiday' => true, 'category' => 'holiday'],
                30 => ['title' => 'फागु पूर्णिमा / होली पर्व (तराई बिदा)', 'tithi' => 'प्रतिपदा', 'is_holiday' => true, 'category' => 'holiday'],
            ],
            12 => [ // चैत्र (Chaitra)
                15 => ['title' => 'घोडेजात्रा (काठमाडौँ उपत्यका बिदा)', 'tithi' => 'औंसी', 'is_holiday' => true, 'category' => 'holiday'],
                24 => ['title' => 'चैते दशैं / राम नवमी व्रत', 'tithi' => 'नवमी', 'is_holiday' => true, 'category' => 'holiday'],
                30 => ['title' => 'शैक्षिक सत्र समापन तथा वार्षिक परीक्षा नतिजा', 'tithi' => 'पूर्णिमा', 'is_holiday' => false, 'category' => 'academic'],
            ],
        ];

        return $monthFestivals[$bsMonth] ?? [];
    }

    /**
     * Get total days in a given BS month.
     */
    public static function getDaysInBSMonth(int $bsYear, int $bsMonth): int
    {
        $yearIdx = $bsYear - 2000;
        if (!isset(self::$bsCalendarData[$yearIdx]) || $bsMonth < 1 || $bsMonth > 12) {
            return 30;
        }
        return self::$bsCalendarData[$yearIdx][$bsMonth - 1];
    }

    /**
     * Get complete start/end date, BS labels, and 12-month calendar breakdown for a Nepali Fiscal Year.
     * Nepali FY runs from Shrawan 1 (Month 4) to Ashadh 31 (Month 3 of next BS year).
     *
     * @param string|int|null $fy E.g. '2081/82', '2081', or null for current
     * @return array
     */
    public static function getFiscalYearDetails(?string $fy = null): array
    {
        if (empty($fy)) {
            $fy = self::currentFiscalYear();
        }

        $parts = explode('/', trim((string)$fy));
        $startYear = (int) $parts[0];
        if ($startYear < 2000) $startYear += 2000;
        if ($startYear < 2000 || $startYear > 2089) {
            $startYear = 2081;
        }
        $endYear = $startYear + 1;

        $startAd = self::bsToAD($startYear, 4, 1);
        $daysInAshadh = self::getDaysInBSMonth($endYear, 3);
        $endAd = self::bsToAD($endYear, 3, $daysInAshadh);

        $monthsOrder = [
            ['month' => 4,  'year' => $startYear, 'quarter' => 'Q1', 'q_np' => 'प्रथम त्रैमासिक (Q1)'],
            ['month' => 5,  'year' => $startYear, 'quarter' => 'Q1', 'q_np' => 'प्रथम त्रैमासिक (Q1)'],
            ['month' => 6,  'year' => $startYear, 'quarter' => 'Q1', 'q_np' => 'प्रथम त्रैमासिक (Q1)'],
            ['month' => 7,  'year' => $startYear, 'quarter' => 'Q2', 'q_np' => 'दोस्रो त्रैमासिक (Q2)'],
            ['month' => 8,  'year' => $startYear, 'quarter' => 'Q2', 'q_np' => 'दोस्रो त्रैमासिक (Q2)'],
            ['month' => 9,  'year' => $startYear, 'quarter' => 'Q2', 'q_np' => 'दोस्रो त्रैमासिक (Q2)'],
            ['month' => 10, 'year' => $startYear, 'quarter' => 'Q3', 'q_np' => 'तेस्रो त्रैमासिक (Q3)'],
            ['month' => 11, 'year' => $startYear, 'quarter' => 'Q3', 'q_np' => 'तेस्रो त्रैमासिक (Q3)'],
            ['month' => 12, 'year' => $startYear, 'quarter' => 'Q3', 'q_np' => 'तेस्रो त्रैमासिक (Q3)'],
            ['month' => 1,  'year' => $endYear,   'quarter' => 'Q4', 'q_np' => 'चौथो त्रैमासिक (Q4)'],
            ['month' => 2,  'year' => $endYear,   'quarter' => 'Q4', 'q_np' => 'चौथो त्रैमासिक (Q4)'],
            ['month' => 3,  'year' => $endYear,   'quarter' => 'Q4', 'q_np' => 'चौथो त्रैमासिक (Q4)'],
        ];

        $months = [];
        foreach ($monthsOrder as $mo) {
            $mNum = $mo['month'];
            $mYear = $mo['year'];
            $daysInM = self::getDaysInBSMonth($mYear, $mNum);
            $mStart = self::bsToAD($mYear, $mNum, 1);
            $mEnd   = self::bsToAD($mYear, $mNum, $daysInM);
            $months[] = [
                'bs_month'   => $mNum,
                'bs_year'    => $mYear,
                'name_en'    => self::$monthsEn[$mNum - 1] ?? '',
                'name_np'    => self::$monthsNp[$mNum - 1] ?? '',
                'label'      => (self::$monthsNp[$mNum - 1] ?? '') . ' (' . (self::$monthsEn[$mNum - 1] ?? '') . ')',
                'quarter'    => $mo['quarter'],
                'quarter_np' => $mo['q_np'],
                'start_ad'   => $mStart,
                'end_ad'     => $mEnd,
                'days'       => $daysInM,
            ];
        }

        $fyKey = "{$startYear}/" . substr((string)$endYear, 2);
        $fyNp = "आ.व. " . self::toNepaliDigits($startYear) . "/" . self::toNepaliDigits((int)substr((string)$endYear, 2), 2);

        return [
            'fy_key'        => $fyKey,
            'fy_np'         => $fyNp,
            'start_year_bs' => $startYear,
            'end_year_bs'   => $endYear,
            'start_ad'      => $startAd,
            'end_ad'        => $endAd,
            'as_of_ad'      => $endAd,
            'as_of_bs'      => "{$endYear} असार {$daysInAshadh}",
            'as_of_bs_np'   => self::toNepaliDigits($endYear) . " असार " . self::toNepaliDigits($daysInAshadh),
            'months'        => $months,
        ];
    }

    /**
     * Get available recent fiscal years list.
     */
    public static function getFiscalYearList(int $count = 6): array
    {
        $curFy = self::currentFiscalYear();
        $start = (int) explode('/', $curFy)[0];
        $list = [];
        for ($i = 0; $i < $count; $i++) {
            $y = $start - $i;
            $nextY = $y + 1;
            $key = "{$y}/" . substr((string)$nextY, 2);
            $list[] = [
                'key'   => $key,
                'start' => $y,
                'end'   => $nextY,
                'label' => "FY {$key} (" . self::toNepaliDigits($y) . "/" . self::toNepaliDigits((int)substr((string)$nextY, 2), 2) . ")",
            ];
        }
        return $list;
    }

    /**
     * Get day of the week in Nepali Devanagari.
     */
    public static function getDayOfWeekNepali(string $adDate): string
    {
        $days = [
            0 => 'आइतबार (Sunday)',
            1 => 'सोमबार (Monday)',
            2 => 'मङ्गलबार (Tuesday)',
            3 => 'बुधबार (Wednesday)',
            4 => 'बिहीबार (Thursday)',
            5 => 'शुक्रबार (Friday)',
            6 => 'शनिबार (Saturday)',
        ];
        $w = (int) date('w', strtotime($adDate));
        return $days[$w] ?? '';
    }
}


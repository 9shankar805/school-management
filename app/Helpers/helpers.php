<?php

if (!function_exists('formatNPR')) {
    /**
     * Format a number as Nepali Rupees using the Indian/Nepali digit-grouping system:
     * comma after first 3 digits, then every 2 digits (lakh/crore system).
     * e.g. 1000000 → "10,00,000.00"
     *
     * @param  float|int|null $amount
     * @param  bool           $withSymbol  Prepend "रू " prefix
     * @param  int            $decimals    Decimal places (default 2)
     * @return string
     */
    function formatNPR($amount, bool $withSymbol = true, int $decimals = 2): string
    {
        $amount   = (float) $amount;
        $negative = $amount < 0;
        $abs      = abs($amount);
        $decPart  = number_format($abs, $decimals, '.', '');
        $parts    = explode('.', $decPart);
        $intPart  = $parts[0];
        $fracPart = $parts[1] ?? str_repeat('0', $decimals);

        $len = strlen($intPart);
        if ($len <= 3) {
            $formattedInt = $intPart;
        } else {
            $last3 = substr($intPart, -3);
            $rest  = substr($intPart, 0, -3);
            // Now split $rest into groups of 2 from the right
            $restFormatted = [];
            $restLen       = strlen($rest);
            $offset        = $restLen % 2;
            if ($offset > 0) {
                $restFormatted[] = substr($rest, 0, $offset);
                $rest = substr($rest, $offset);
            }
            if ($rest !== '') {
                $restFormatted = array_merge($restFormatted, str_split($rest, 2));
            }
            $formattedInt = implode(',', $restFormatted) . ',' . $last3;
        }

        $out = $formattedInt . '.' . $fracPart;
        if ($negative) {
            $out = '-' . $out;
        }
        return $withSymbol ? "रू {$out}" : $out;
    }
}

if (!function_exists('formatRs')) {
    /**
     * Alias of formatNPR using "Rs." prefix and optional decimal trimming.
     */
    function formatRs($amount, bool $withSymbol = true, int $decimals = 2): string
    {
        $formatted = formatNPR($amount, false, $decimals);
        return $withSymbol ? "Rs. {$formatted}" : $formatted;
    }
}

if (!function_exists('bsDate')) {
    /**
     * Convert an AD date string to a Bikram Sambat (BS) formatted string.
     *
     * @param  string|null $adDate   YYYY-MM-DD
     * @param  bool        $nepali   true = Devanagari, false = English
     * @param  string      $format   'long' | 'short'
     * @return string
     */
    function bsDate(?string $adDate, bool $nepali = true, string $format = 'long'): string
    {
        if (!$adDate) return '';
        return \App\Services\NepaliDateService::formatBS($adDate, $nepali, $format);
    }
}

if (!function_exists('bsYear')) {
    function bsYear(?string $adDate): string
    {
        if (!$adDate) return '';
        return (string) (\App\Services\NepaliDateService::bsYear($adDate) ?? '');
    }
}

if (!function_exists('nepaliDigits')) {
    function nepaliDigits(int $n): string
    {
        return \App\Services\NepaliDateService::toNepaliDigits($n);
    }
}

if (!function_exists('ordinal')) {
    /**
     * Convert a number to its ordinal string: 1 → 1st, 2 → 2nd, 3 → 3rd, 4 → 4th
     */
    function ordinal(int $number): string
    {
        $suffix = match (true) {
            ($number % 100 >= 11 && $number % 100 <= 13) => 'th',
            ($number % 10 === 1) => 'st',
            ($number % 10 === 2) => 'nd',
            ($number % 10 === 3) => 'rd',
            default              => 'th',
        };
        return $number . $suffix;
    }
}

if (!function_exists('nprWords')) {
    /**
     * Convert an amount into Nepali Rupees in words (e.g. "Three Thousand Five Hundred Rupees Only").
     */
    function nprWords(float|int|null $amount): string
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return 'Zero Rupees Only';
        }

        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $convertGroup = function ($n) use (&$convertGroup, $ones, $tens) {
            $output = '';
            if ($n >= 100) {
                $output .= $ones[intval($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n >= 20) {
                $output .= $tens[intval($n / 10)] . ' ';
                $n %= 10;
            }
            if ($n > 0) {
                $output .= $ones[$n] . ' ';
            }
            return trim($output);
        };

        $crore    = intval($amount / 10000000);
        $amount   = fmod($amount, 10000000);
        $lakh     = intval($amount / 100000);
        $amount   = fmod($amount, 100000);
        $thousand = intval($amount / 1000);
        $amount   = fmod($amount, 1000);
        $hundreds = intval($amount);
        $paisa    = intval(round(($amount - $hundreds) * 100));

        $result = '';
        if ($crore > 0) $result .= $convertGroup($crore) . ' Crore ';
        if ($lakh > 0) $result .= $convertGroup($lakh) . ' Lakh ';
        if ($thousand > 0) $result .= $convertGroup($thousand) . ' Thousand ';
        if ($hundreds > 0) $result .= $convertGroup($hundreds) . ' ';

        $result = trim($result) . ' Rupees';
        if ($paisa > 0) {
            $result .= ' and ' . $convertGroup($paisa) . ' Paisa';
        }
        return $result . ' Only';
    }
}

if (!function_exists('bsMonthRange')) {
    /**
     * Format a Bikram Sambat month range label.
     */
    function bsMonthRange(int $from, ?int $to = null, ?int $year = null, bool $nepali = true): string
    {
        return \App\Services\NepaliDateService::formatBsRange($from, $to ?: $from, $year, $nepali);
    }
}

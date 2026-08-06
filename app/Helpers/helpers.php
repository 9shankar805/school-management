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

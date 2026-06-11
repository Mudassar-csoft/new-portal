<?php

namespace App\Support;

class AmountToWords
{
    /**
     * @var array<int, string>
     */
    private const ONES = [
        0 => 'Zero',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen',
    ];

    /**
     * @var array<int, string>
     */
    private const TENS = [
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety',
    ];

    /**
     * @var array<int, string>
     */
    private const SCALES = [
        1000000000 => 'Arab',
        10000000 => 'Crore',
        100000 => 'Lakh',
        1000 => 'Thousand',
    ];

    public static function forRupees(float|int|string $amount): string
    {
        $normalized = round((float) $amount, 2);
        $isNegative = $normalized < 0;
        $normalized = abs($normalized);

        $rupees = (int) floor($normalized);
        $paisa = (int) round(($normalized - $rupees) * 100);

        if ($paisa === 100) {
            $rupees++;
            $paisa = 0;
        }

        $words = self::convertInteger($rupees) . ' Rupees';

        if ($paisa > 0) {
            $words .= ' and ' . self::convertInteger($paisa) . ' Paisa';
        }

        return $isNegative ? 'Minus ' . $words : $words;
    }

    private static function convertInteger(int $number): string
    {
        if ($number < 20) {
            return self::ONES[$number];
        }

        if ($number < 100) {
            $tens = intdiv($number, 10);
            $remainder = $number % 10;

            return self::TENS[$tens] . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        if ($number < 1000) {
            $hundreds = intdiv($number, 100);
            $remainder = $number % 100;

            return self::ONES[$hundreds] . ' Hundred' . ($remainder > 0 ? ' ' . self::convertInteger($remainder) : '');
        }

        $parts = [];

        foreach (self::SCALES as $value => $label) {
            if ($number < $value) {
                continue;
            }

            $parts[] = self::convertInteger(intdiv($number, $value)) . ' ' . $label;
            $number %= $value;
        }

        if ($number > 0) {
            $parts[] = self::convertInteger($number);
        }

        return implode(' ', $parts);
    }
}

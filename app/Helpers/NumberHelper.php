<?php

if (! function_exists('numberToArabicText')) {
    function numberToArabicText($number)
    {
        $number = intval($number);

        if ($number == 0) {
            return 'صفر';
        }

        if ($number < 0) {
            return 'سالب '.numberToArabicText(abs($number));
        }

        $arabicOnes = [
            0 => 'صفر', 1 => 'واحد', 2 => 'اثنان', 3 => 'ثلاثة', 4 => 'أربعة',
            5 => 'خمسة', 6 => 'ستة', 7 => 'سبعة', 8 => 'ثمانية', 9 => 'تسعة',
            10 => 'عشرة', 11 => 'أحد عشر', 12 => 'اثنا عشر', 13 => 'ثلاثة عشر',
            14 => 'أربعة عشر', 15 => 'خمسة عشر', 16 => 'ستة عشر', 17 => 'سبعة عشر',
            18 => 'ثمانية عشر', 19 => 'تسعة عشر',
        ];

        $arabicTens = [
            20 => 'عشرون', 30 => 'ثلاثون', 40 => 'أربعون', 50 => 'خمسون',
            60 => 'ستون', 70 => 'سبعون', 80 => 'ثمانون', 90 => 'تسعون',
        ];

        $arabicHundreds = [
            100 => 'مائة', 200 => 'مائتان', 300 => 'ثلاثمائة', 400 => 'أربعمائة',
            500 => 'خمسمائة', 600 => 'ستمائة', 700 => 'سبعمائة', 800 => 'ثمانمائة',
            900 => 'تسعمائة',
        ];

        $result = [];

        if ($number >= 1000000000000) {
            $trillions = intval($number / 1000000000000);
            $result[] = convertGroup($trillions, $arabicOnes, $arabicTens, $arabicHundreds).' تريليون';
            $number %= 1000000000000;
        }

        if ($number >= 1000000000) {
            $billions = intval($number / 1000000000);
            $result[] = convertGroup($billions, $arabicOnes, $arabicTens, $arabicHundreds).' مليار';
            $number %= 1000000000;
        }

        if ($number >= 1000000) {
            $millions = intval($number / 1000000);
            $result[] = convertGroup($millions, $arabicOnes, $arabicTens, $arabicHundreds).' مليون';
            $number %= 1000000;
        }

        if ($number >= 1000) {
            $thousands = intval($number / 1000);
            if ($thousands == 1) {
                $result[] = 'ألف';
            } elseif ($thousands == 2) {
                $result[] = 'ألفان';
            } elseif ($thousands <= 10) {
                $result[] = convertGroup($thousands, $arabicOnes, $arabicTens, $arabicHundreds).' آلاف';
            } else {
                $result[] = convertGroup($thousands, $arabicOnes, $arabicTens, $arabicHundreds).' ألف';
            }
            $number %= 1000;
        }

        if ($number > 0) {
            $result[] = convertGroup($number, $arabicOnes, $arabicTens, $arabicHundreds);
        }

        return implode(' و', $result);
    }
}

if (! function_exists('convertGroup')) {
    function convertGroup($number, $arabicOnes, $arabicTens, $arabicHundreds)
    {
        $number = intval($number);

        if ($number == 0) {
            return '';
        }

        if ($number < 20) {
            return $arabicOnes[$number] ?? '';
        }

        if ($number < 100) {
            $tens = intval($number / 10) * 10;
            $ones = $number % 10;

            if ($ones == 0) {
                return $arabicTens[$tens] ?? '';
            }

            return $arabicOnes[$ones].' و'.$arabicTens[$tens];
        }

        if ($number < 1000) {
            $hundreds = intval($number / 100) * 100;
            $remainder = $number % 100;

            $result = $arabicHundreds[$hundreds] ?? '';

            if ($remainder > 0) {
                $result .= ' و'.convertGroup($remainder, $arabicOnes, $arabicTens, $arabicHundreds);
            }

            return $result;
        }

        return '';
    }
}

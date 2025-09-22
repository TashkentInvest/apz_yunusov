<?php

namespace App\Services;

class NumberToTextService
{
    private $units = ["бир", "икки", "уч", "тўрт", "беш", "олти", "етти", "саккиз", "тўққиз"];
    private $teens = ["ўн", "ўн бир", "ўн икки", "ўн уч", "ўн тўрт", "ўн беш", "ўн олти", "ўн етти", "ўн саккиз", "ўн тўққиз"];
    private $tens = ["йигирма", "ўттиз", "қирқ", "эллик", "олтмиш", "етмиш", "саксон", "тўқсон"];
    private $hundreds = ["бир юз", "икки юз", "уч юз", "тўрт юз", "беш юз", "олти юз", "етти юз", "саккиз юз", "тўққиз юз"];

    public function convert($number)
    {
        $number = round($number);

        if ($number == 0) return "ноль";

        $text = "";

        // Миллиардлар
        if ($number >= 1000000000) {
            $billions = floor($number / 1000000000);
            $text .= $this->convertTriade($billions) . " миллиард ";
            $number = $number % 1000000000;
        }

        // Миллионлар
        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $text .= $this->convertTriade($millions) . " миллион ";
            $number = $number % 1000000;
        }

        // Мингликлар
        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            $text .= $this->convertTriade($thousands) . " минг ";
            $number = $number % 1000;
        }

        // Қолган
        if ($number > 0) {
            $text .= $this->convertTriade($number);
        }

        return trim($text);
    }

    private function convertTriade($number)
    {
        $number = floor($number);
        $text = "";

        // Юзликлар
        if ($number >= 100) {
            $hundredDigit = floor($number / 100);
            $text .= $this->hundreds[$hundredDigit - 1] . " ";
            $number = $number % 100;
        }

        // Ўнликлар
        if ($number >= 20) {
            $tenDigit = floor($number / 10);
            $text .= $this->tens[$tenDigit - 2] . " ";
            $number = $number % 10;
        } elseif ($number >= 10) {
            $text .= $this->teens[$number - 10] . " ";
            $number = 0;
        }

        // Бирликлар
        if ($number > 0) {
            $text .= $this->units[$number - 1] . " ";
        }

        return trim($text);
    }
}

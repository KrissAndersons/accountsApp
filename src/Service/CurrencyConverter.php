<?php

namespace App\Service;

class CurrencyConverter
{
    public function convert(float $rateFrom, float $rateTo, float $amount): float
    {
        return round(($amount*$rateTo)/$rateFrom, 2);
    }
}
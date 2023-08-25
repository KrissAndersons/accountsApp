<?php

namespace App\Entity\Traits;

trait AmountTransformTrait
{
    /**
     * toDatabase
     *
     * @param  string $amount
     * @return string
     */
    private function toDatabase(string $amount): string
    {
        return (string)$amount*10000;
    }

    /**
     * humanReadable
     *
     * @param  string $amount
     * @return string
     */
    private function humanReadable(string $amount): string
    {
        return (string)($amount/10000);
    }

}
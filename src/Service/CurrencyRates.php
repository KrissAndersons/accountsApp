<?php

namespace App\Service;

use Exception;

class CurrencyRates
{

    private const RATES_SERVICE_URL = 'https://api.exchangerate.host/latest';

    public array  $rates     = [];
    public string $ratesDate = '';

    public function __construct()
    {
        $this->setCurrencyRates();
    }

    public function setCurrencyRates(): void
    {
        
        $responseJson = file_get_contents(self::RATES_SERVICE_URL);

        if (false === $responseJson) {
            return;
        }

        try {

            $response = json_decode($responseJson);

            if (true === $response->success) {

                $this->ratesDate = $response->date;

                $rates = [];
                foreach ($response->rates as $isoCode => $rate) {

                    $rates[$isoCode] = $rate;
                }

                $this->rates = $rates;
            }

        } catch(Exception $e) {
            // Here and with erlier return shoud be somekind of alert system about service unavailability
            return;
        }
    }

    public function getRate(string $isoCode): ?float
    {
        if (isset($this->rates[$isoCode])) {
            return $this->rates[$isoCode];
        }

        return null;
    }
}

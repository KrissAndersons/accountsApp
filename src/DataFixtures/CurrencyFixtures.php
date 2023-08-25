<?php

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\RatesMetadata;
use App\Service\CurrencyRates;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CurrencyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $ratesService = new CurrencyRates();
        $rates        = $ratesService->rates;
        $date         = $ratesService->ratesDate;

        $ratesMetadata = new RatesMetadata();
        $ratesMetadata->setUpdatedAt(date_create($date));
        $manager->persist($ratesMetadata);
        $manager->flush();
        $manager->clear();

        $batchSize = 20;
        $i         = 1;
        foreach ($rates as $key => $rate) {

            $currency = new Currency();
            $currency->setIsoCode($key);  
            $currency->setRate($rate);
            $manager->persist($currency);

            if (true === in_array($key, ['EUR', 'USD', 'GBP', 'CHF'], true)) {
                $manager->flush();
                $this->addReference($key, $currency);
                $manager->clear();
            }

            if (0 === ($i % $batchSize)) {
                $manager->flush();
                $manager->clear();
            }
            ++$i;
        }
        $manager->flush();
        $manager->clear();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Transaction;
use App\Service\CurrencyConverter;
use App\Service\CurrencyRates;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;


class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $ratesServ = new CurrencyRates();
        $converter = new CurrencyConverter();

        $reference_map = [
            'account',
            'account01',
            'account1',
            'account11',
            'account12',
            'account2',
            'account3',
        ];

        $map_copy = $reference_map;
        
        for ($i=0; $i < 30; $i++) { 
        
            foreach ($reference_map as $key => $reference) {

                unset($map_copy[$key]);
                $map_copy = array_values($map_copy);

                $accountFrom = $this->getReference($reference);
                $accountTo   = $this->getReference($map_copy[rand(0, 5)]);

                $currencyFrom = $accountFrom->getCurrency();
                $currencyTo   = $accountTo->getCurrency();

                $ratesFrom = $ratesServ->getRate($currencyFrom->getIsoCode());
                $ratesTo   = $ratesServ->getRate($currencyTo->getIsoCode());

                $amount = round(rand(100, 30000)/100, 2);

                $transaction = new Transaction();
                $transaction->setAccountFrom($accountFrom);
                $transaction->setAccountTo($accountTo);

                $transaction->setAmountFrom($converter->convert($ratesTo, $ratesFrom, $amount));
                $transaction->setAmountTo($amount);

                $transaction->setCurrencyFrom($currencyFrom);
                $transaction->setCurrencyTo($currencyTo);

                $transaction->setRateFrom($ratesFrom);
                $transaction->setRateTo($ratesTo);

                $date = new DateTime();
                $transaction->setCreatedAt($date->modify('-' . (string)rand(1, 30) . ' day'));

                $manager->persist($transaction);    

                $map_copy = $reference_map;
            }

            $manager->flush();
            $manager->clear();
        }
    }

    public function getDependencies()
    {
        return [
            AccountFixtures::class,        
        ];
    }
}

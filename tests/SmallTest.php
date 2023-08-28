<?php

namespace App\Tests;

use App\Entity\Account;
use App\Entity\Client;
use App\Entity\Currency;
use App\Entity\RatesMetadata;
use App\Entity\Transaction;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SmallTest extends KernelTestCase
{

    private $em;

    public function setUp(): void
    {
        $kernel = self::bootKernel();
    
        $this->em = $kernel->getContainer()->get('doctrine')->getManager(); 

    }

    public function tearDown(): void
    {

        $this->em->clear();
        $this->em = null;

        parent::tearDown();
    }

    public function testEntityCreation(): void
    {

        $clientFirst = new Client();
        $clientFirst->setName('clientFirst');
        $this->em->persist($clientFirst);

        $clientSecond = new Client();
        $clientSecond->setName('clientSecond');
        $this->em->persist($clientSecond);

        $this->em->flush();

        $clientRepo = $this->em->getRepository(Client::class);

        $clientResult = $clientRepo->findBy(['name' => 'clientFirst']);

        $this->assertNotEmpty($clientResult);
        $this->assertEquals('clientFirst', $clientResult[0]->getName());
        
        $currencyYYY = new Currency();
        $currencyYYY->setRate(0.5);
        $currencyYYY->setIsoCode('YYY');
        $this->em->persist($currencyYYY);

        $currencyZZZ = new Currency();
        $currencyZZZ->setRate(100);
        $currencyZZZ->setIsoCode('ZZZ');
        $this->em->persist($currencyZZZ);

        $this->em->flush();

        $currencyRepo  = $this->em->getRepository(Currency::class);

        $currencyYYYResult = $currencyRepo->findBy(['isoCode' => 'YYY']);
        $this->assertNotEmpty($currencyYYYResult);
        $this->assertEquals('YYY', $currencyYYYResult[0]->getIsoCode());

        $accountYYY = new Account();
        $accountYYY->setClient($clientFirst);
        $accountYYY->setCurrency($currencyYYY);
        $accountYYY->setAmount(100);
        $this->em->persist($accountYYY);

        $accountZZZ = new Account();
        $accountZZZ->setClient($clientSecond);
        $accountZZZ->setCurrency($currencyZZZ);
        $accountZZZ->setAmount(500);
        $this->em->persist($accountZZZ);

        $this->em->flush();

        $accountRepo = $this->em->getRepository(Account::class);
        
        $accountYYYResult = $accountRepo->findBy(['client' => $clientFirst->getId()]);
        $this->assertNotEmpty($accountYYYResult);
        $this->assertEquals(100, $accountYYYResult['0']->getAmount());

        $accountZZZResult = $accountRepo->findBy(['client' => $clientSecond->getId()]);
        $this->assertNotEmpty($accountZZZResult);
        $this->assertEquals(500, $accountZZZResult['0']->getAmount());

        $transaction = new Transaction();
    
        $transaction->setAccountFrom($accountYYY);
        $transaction->setAccountTo($accountZZZ);
        $transaction->setAmountFrom(45);
        $transaction->setAmountTo(62);
        $transaction->setCurrencyFrom($currencyYYY);
        $transaction->setCurrencyTo($currencyZZZ);
        $transaction->setRateFrom($currencyYYY->getRate());
        $transaction->setRateTo($currencyZZZ->getRate());
        $datetime = new DateTime();
        $transaction->setCreatedAt($datetime);

        $this->em->persist($transaction);
        $this->em->flush();

        $transactionRepo   = $this->em->getRepository(Transaction::class);
        $transactionResult = $transactionRepo->findBy(['accountFrom' => $accountYYY->getId()]);

        $this->assertNotEmpty($transactionResult);
        $this->assertEquals(45, $transactionResult['0']->getAmountFrom());
        $this->assertEquals(62, $transactionResult['0']->getAmountTo());
        $this->assertEquals($datetime->getTimestamp(), $transactionResult['0']->getCreatedAt()->getTimestamp());
        $this->assertEquals(0.5, $transactionResult['0']->getRateFrom());
        $this->assertEquals(100, $transactionResult['0']->getRateTo());

        $meta = new RatesMetadata();
        $meta->setUpdatedAt($datetime);

        $this->em->persist($meta);
        $this->em->flush();

        $metaRepo   = $this->em->getRepository(RatesMetadata::class);
        $metaResult = $metaRepo->findAll();

        $this->assertEquals($datetime->getTimestamp(), $metaResult['0']->getUpdatedAt()->getTimestamp());
    }
}

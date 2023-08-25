<?php

namespace App\DataFixtures;

use App\Entity\Account;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AccountFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $account = new Account();
        $account->setClient($this->getReference('client'));
        $account->setCurrency($this->getReference('EUR'));
        $account->setAmount('100');
        $manager->persist($account);

        $account01 = new Account();
        $account01->setClient($this->getReference('client'));
        $account01->setCurrency($this->getReference('GBP'));
        $account01->setAmount('300');
        $manager->persist($account01);
        
        $account1 = new Account();
        $account1->setClient($this->getReference('client1'));
        $account1->setCurrency($this->getReference('GBP'));
        $account1->setAmount('200');
        $manager->persist($account1);

        $account11 = new Account();
        $account11->setClient($this->getReference('client1'));
        $account11->setCurrency($this->getReference('USD'));
        $account11->setAmount('600');
        $manager->persist($account11);

        $account12 = new Account();
        $account12->setClient($this->getReference('client1'));
        $account12->setCurrency($this->getReference('CHF'));
        $account12->setAmount('1200');
        $manager->persist($account12);

        $account2 = new Account();
        $account2->setClient($this->getReference('client2'));
        $account2->setCurrency($this->getReference('USD'));
        $account2->setAmount('300');
        $manager->persist($account2);

        $account3 = new Account();
        $account3->setClient($this->getReference('client3'));
        $account3->setCurrency($this->getReference('CHF'));
        $account3->setAmount('400');
        $manager->persist($account3);

        $manager->flush();

        $this->addReference('account', $account);
        $this->addReference('account01', $account01);
        $this->addReference('account1', $account1);
        $this->addReference('account11', $account11);
        $this->addReference('account12', $account12);
        $this->addReference('account2', $account2);
        $this->addReference('account3', $account3);

        $manager->clear();
    }

    public function getDependencies()
    {
        return [
            ClientFixtures::class,
            CurrencyFixtures::class,
        ];
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $clientNames = ['client', 'client1', 'client2', 'client3', 'client4', 'client5', 'client6'];

        foreach ($clientNames as $name) {
            $client = new Client();
            $client->setName($name);
            $manager->persist($client);
            $manager->flush();
            $this->addReference($name, $client);    
            $manager->clear();
        }
    }
}

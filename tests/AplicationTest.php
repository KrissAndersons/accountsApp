<?php

namespace App\Tests;

use App\DataFixtures\AccountFixtures;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\CurrencyFixtures;
use App\DataFixtures\FullFixtures;
use App\DataFixtures\TransactionFixtures;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp\Client;


use Liip\TestFixturesBundle\Services\DatabaseToolCollection;

use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;


class AplicationTest extends WebTestCase
{
    private $em;
    private $client;
    private $guzzleClient;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        $this->guzzleClient = new Client([
            'base_uri' => 'http://nginx',
        ]);
    
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->em->clear();
        $this->em = null;
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Hi, this is my accounts app. Here are some endpoints for easier manual testing.');

        $response     = $this->guzzleClient->get('/api/clients');
        $responseData = json_decode($response->getBody());

        $this->assertTrue($responseData->succes);
        
        $clientId = null;
        foreach ($responseData->clients as $id => $name) {
            if ('client' === $name)
            {
                $clientId = $id;
            }
        }

        $this->assertNotNull($clientId);


        //dd($responseData);

        // $data = [];
        // $response = $guzzleClient->get('/api/clients', [
        //     'body' => json_encode($data)
        // ]);

    }

}

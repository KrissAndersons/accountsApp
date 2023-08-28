<?php

namespace App\Tests;

use App\Service\CurrencyConverter;
use App\Service\CurrencyRates;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp\Client;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class AplicationTest extends WebTestCase
{
    private $client;
    private $guzzleClient;

    const FIRST_CLIENT  = 'client';
    const SECOND_CLIENT = 'client1';

    const CURRENCY_FROM = 'GBP';
    const CURRENCY_TO   = 'USD';


    private function guzzleGet(string $endPoint): mixed
    {
        return json_decode($this->guzzleClient->get($endPoint)->getBody());
    }

    private function guzzlePost(string $endPoint, array $data): mixed
    {
        $response = $this->guzzleClient->post($endPoint, [
            'body'    => json_encode($data),
            'timeout' => 5,
        ]);

        return json_decode($response->getBody());
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->guzzleClient = new Client([
            'base_uri' => 'http://nginx',
        ]);
    
    }

    public function testWelcome(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Hi, this is my accounts app. Here are some endpoints for easier manual testing.');
    }

    public function testResponses(): void
    {
        
        $endPoint     = '/api/clients';
        $responseData = $this->guzzleGet($endPoint);
        $this->assertTrue($responseData->success, 'Success false on ' . $endPoint);

        $firstClientId  = null;
        $secondClientId = null;
        foreach ($responseData->clients as $id => $name) {
            
            if (self::FIRST_CLIENT === $name)
            {
                $firstClientId = $id;
            }
            
            if (self::SECOND_CLIENT === $name)
            {
                $secondClientId = $id;
            }
        }
        $this->assertNotNull($firstClientId, 'Missing first client "client"!');
        $this->assertNotNull($secondClientId, 'Missing second client "client2"!');

        $endPoint     = '/api/clients/' . $firstClientId . '/accounts';
        $responseData = $this->guzzleGet($endPoint);
        $this->assertTrue($responseData->success, 'Success false on ' . $endPoint);

        $accounfFromId      = null;
        $accountFromBalance = 0;
        foreach ($responseData->accounts as $id => $row) {
            
            if ( self::CURRENCY_FROM === $row->currency)
            {
                $accounfFromId      = $id;
                $accountFromBalance = $row->balance;
            }
        }
        $this->assertNotNull($accounfFromId, 'Missing accountFromId!');
        $this->assertGreaterThan(0, $accountFromBalance, 'Balance to low to test!');

        $endPoint     = '/api/clients/' . $secondClientId . '/accounts';
        $responseData = $this->guzzleGet($endPoint);
        $this->assertTrue($responseData->success, 'Success false on ' . $endPoint);

        $accounfToId = null;
        foreach ($responseData->accounts as $id => $row) {
            
            if ( self::CURRENCY_TO === $row->currency)
            {
                $accounfToId = $id;
            }
        }
        $this->assertNotNull($accounfToId, 'Missing accounfToId!');

        $endPoint     = '/api/clients/tivdidiv/accounts';
        $responseData = $this->guzzleGet($endPoint);
        $this->assertFalse($responseData->success, 'Success true o_0 on ' . $endPoint);

        $limit        = 15;
        $endPoint     = '/api/accounts/' . $accounfFromId . '/transactions/5/' . $limit;
        $responseData = $this->guzzleGet($endPoint);
        $this->assertTrue($responseData->success, 'Success false on ' . $endPoint);
        $this->assertCount($limit, (array)$responseData->transactions, $limit . ' transactions expected on ' . $endPoint);

        $previous = null;
        foreach ($responseData->transactions as $id => $transaction) {
            
            if (null !== $previous) {
                $this->assertFalse($previous < $transaction->createdAt->date, 'Wrong transaction order!');
            }

            $previous = $transaction->createdAt->date;
        }

        $endPoint     = '/api/transactions';
        $postData = [
            'badKey' => 'badData'
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input on ' . $endPoint);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => $accounfToId, 
            'amount'        => 1.236,
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input, amount has to many decimals on ' . $endPoint);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => $accounfToId, 
            'amount'        => -100,
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input, negative amount on ' . $endPoint);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => $accounfToId, 
            'amount'        => 'ghj.12',
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input, amount non numeric on ' . $endPoint);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => 'tivdidiv', 
            'amount'        => 100,
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input, account id non numeric on ' . $endPoint);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => 'tivdidiv', 
            'amount'        => 100,
            'isoCode'       => 'EUR', 
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input, wrong currency ' . $endPoint);

        $rates     = new CurrencyRates();
        $rateFrom  = $rates->getRate(self::CURRENCY_FROM);
        $rateTo    = $rates->getRate(self::CURRENCY_TO);
        $converter = new CurrencyConverter();

        $availableFunds = $converter->convert($rateFrom, $rateTo, $accountFromBalance);
        $amountToLarge  = $availableFunds + 1000;
        $amountHalf     = round($availableFunds/2, 2);

        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => $accounfToId, 
            'amount'        => $amountToLarge,
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertFalse($responseData->success, 'Bad input amount to large' . $endPoint);


        $postData = [
            'accountFromId' => $accounfFromId, 
            'accountToId'   => $accounfToId, 
            'amount'        => $amountHalf,
            'isoCode'       => self::CURRENCY_TO,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertTrue($responseData->success, 'Transfer failed' . $endPoint);
        
        $transaction = $responseData->transaction;
        $this->assertEquals($accounfFromId, $transaction->accountFrom, 'Sender account mismach!');
        $this->assertEquals($accounfToId, $transaction->accountTo, 'Receiver account mismach!');
        $this->assertEquals(self::CURRENCY_FROM, $transaction->currencyFrom, 'Sender currency mismach!');
        $this->assertEquals(self::CURRENCY_TO, $transaction->currencyTo, 'Receiver currency mismach!');
        $this->assertEquals($amountHalf, $transaction->amountTo, 'Amount sent mismach!');

        $postData = [
            'accountFromId' => $accounfToId, 
            'accountToId'   => $accounfFromId, 
            'amount'        => round($accountFromBalance/2, 2),
            'isoCode'       => self::CURRENCY_FROM,
        ];
        $responseData = $this->guzzlePost($endPoint, $postData);
        $this->assertTrue($responseData->success, 'Return transfer failed' . $endPoint);

    }

}

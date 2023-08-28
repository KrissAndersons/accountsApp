<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Currency;
use App\Entity\RatesMetadata;
use App\Entity\Transaction;
use App\Service\CurrencyConverter;
use App\Service\CurrencyRates;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/transactions', name: 'api_')]
class TransactionsController extends AbstractController
{

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('', name: 'create_transaction', methods:['POST'])]
    public function create(Request $request): JsonResponse
    {
        $postData = json_decode($request->getContent(), true);
        
        $requiredKeys = ['accountFromId', 'accountToId', 'amount', 'isoCode'];

        if (count(array_intersect_key(array_flip($requiredKeys), $postData)) !== count($requiredKeys)) {
            return $this->json([
                'success' => false,
                'error'  => 'Missing input data.',
            ]);
        }

        if (!is_numeric($postData['amount']) || preg_match('/\.\d{3,}/', $postData['amount']) || 0 >= $postData['amount']) {
            return $this->json([
                'success' => false,
                'error'  => 'Bad amount format.',
            ]);
        }
        $amount = $postData['amount'];

        $accountRepo = $this->em->getRepository(Account::class);
        $accountFrom = $accountRepo->find($postData['accountFromId']);
        $accountTo   = $accountRepo->find($postData['accountToId']);

        if (null === $accountFrom || null === $accountTo) {
            return $this->json([
                'success' => false,
                'error'  => 'Accounts not found',
            ]);
        }

        $currencyRepo = $this->em->getRepository(Currency::class);
        $currency     = $currencyRepo->findBy(['isoCode' => $postData['isoCode']]);

        if (empty($currency)) {
            return $this->json([
                'success' => false,
                'error'  => 'Receiver\'s currency not suported',
            ]);
        }

        $currencyFrom = $accountFrom->getCurrency();
        $currencyTo   = $accountTo->getCurrency();
         
        if ($currencyTo->getIsoCode() !== $currency[0]->getIsoCode()) {
            return $this->json([
                'success' => false,
                'error'  => 'Transaction not allowed, currency must match receiver\'s account currency',
            ]);
        }
        
        $ratesServ = new CurrencyRates();

        $metaRepo = $this->em->getRepository(RatesMetadata::class);
        $metadata = $metaRepo->findAll();

        // this if should be somwhere else
        if (!empty($ratesServ->ratesDate)) 
        {
            $metaRepo  = $this->em->getRepository(RatesMetadata::class);
            $metadata  = $metaRepo->findAll();
            $ratesDate = new DateTime($ratesServ->ratesDate);

            if ($ratesDate > $metadata['0']->getUpdatedAt()) {

                $metadata['0']->setUpdatedAt($ratesDate);

                $this->em->persist($metadata['0']);
                $this->em->flush();
                $this->em->clear();

                $q = $this->em->createQuery('select c from App\Entity\Currency c');
                $i = 0;
                foreach ($q->toIterable() as $currency) {
                    
                    $newRate = $ratesServ->getRate($currency->getIsoCode());
                    if (null !== $newRate) {
                        $currency->setRate($newRate);
                    }

                    ++$i;
                    if (($i % 20) === 0) {
                        $this->em->flush();
                        $this->em->clear();
                    }
                }
                $this->em->flush();
                $this->em->clear();
            }
        }        

        $ratesFrom = $ratesServ->getRate($currencyFrom->getIsoCode());
        $ratesTo   = $ratesServ->getRate($currencyTo->getIsoCode());

        if (null === $ratesFrom || null === $ratesTo){
            $ratesFrom = $currencyFrom->getRate();
            $ratesTo   = $currencyTo->getRate();
        }

        $converter   = new CurrencyConverter();

        $amountFrom = $converter->convert($ratesTo, $ratesFrom, $amount);

        if ($accountFrom->getAmount() < $amountFrom) {
            return $this->json([
                'success' => false,
                'error'  => 'Insufficient funds',
            ]);
        }

        $accountFrom->setAmount($accountFrom->getAmount() - $amountFrom);
        $accountTo->setAmount($accountTo->getAmount() + $amount);
    
        $transaction = new Transaction();
        $transaction->setAccountFrom($accountFrom);
        $transaction->setAccountTo($accountTo);

        $transaction->setAmountFrom($amountFrom);
        $transaction->setAmountTo($amount);

        $transaction->setCurrencyFrom($currencyFrom);
        $transaction->setCurrencyTo($currencyTo);

        $transaction->setRateFrom($ratesFrom);
        $transaction->setRateTo($ratesTo);

        $transaction->setCreatedAt(new DateTime());

        $this->em->getConnection()->beginTransaction();

        try {

            $this->em->persist($accountFrom);
            $this->em->persist($accountTo);
            $this->em->persist($transaction);

            $this->em->flush();

            $this->em->getConnection()->commit();

        } catch (Exception $e) {

            $this->em->getConnection()->rollback();
            $this->em->clear();

            return $this->json([
                'success' => false,
                'error'  => 'Transaction failed.',
            ]);

        }

        return $this->json([
            'success'      => true,
            'transaction' => [
                'id'           => $transaction->getId(),
                'accountFrom'  => $transaction->getAccountFrom()->getId(),
                'accountTo'    => $transaction->getAccountTo()->getId(),
                'currencyFrom' => $transaction->getCurrencyFrom()->getIsoCode(),
                'currencyTo'   => $transaction->getCurrencyTo()->getIsoCode(),
                'rateFrom'     => $transaction->getRateFrom(),
                'rateTo'       => $transaction->getRateTo(),
                'amountFrom'   => $transaction->getAmountFrom(),
                'amountTo'     => $transaction->getAmountTo(),
                'createdAt'    => $transaction->getCreatedAt(),
            ],
        ]);

    }
}

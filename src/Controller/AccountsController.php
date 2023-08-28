<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/accounts', name: 'api_')]
class AccountsController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/{accountId}/transactions/{offset}/{limit}', name: 'get_transactions', methods:['GET'])]
    public function accountTransactions(int $accountId, int $offset = 0, int $limit = 5): JsonResponse
    {
        //for large data offset should not be used, in this case I would use created_at as offset

        $accountsRepo = $this->em->getRepository(Account::class);
        $account      = $accountsRepo->find($accountId);

        if (null === $account) {
            return $this->json([
                'success' => false,
                'error'  => 'Account not found',
            ]);
        }

        $transactionsRepo = $this->em->getRepository(Transaction::class);
        $transactions     = $transactionsRepo->getTransactions($accountId, $offset, $limit);

        $transactionList = [];
        foreach ($transactions as $transaction) {

            $transactionList[$transaction->getId()] = [
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
            ];
        }

        return $this->json([
            'success'       => true,
            'transactions' => $transactionList,
        ]); 
    }
}

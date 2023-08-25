<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/clients', name: 'api_')]
class ClientsController extends AbstractController
{

    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'clients', methods:['GET'])]
    public function clients(): JsonResponse
    {
        $repo    = $this->em->getRepository(Client::class);
        $clients = $repo->findAll();

        $clientList = [];
        foreach ($clients as $client) {

            $clientList[$client->getId()] = $client->getName();
        }

        return $this->json([
            'succes'  => true,
            'clients' => $clientList,
        ]);
    }
    
    
    #[Route('/{clientId}/accounts', name: 'client_accounts', methods:['GET'])]
    public function clientAccounts($clientId): JsonResponse
    {
        $repo   = $this->em->getRepository(Client::class);
        $client = $repo->find($clientId);
        
        if (null === $client) {
            return $this->json([
                'succes' => false,
                'error'  => 'Client not found.',
            ]);
        }

        $accounts    = $client->getAccounts();
        $accountList = [];
        foreach ($accounts as $account) {

            $accountList[$account->getId()] = [
                'balance'  => $account->getAmount(),
                'currency' => $account->getCurrency()->getIsoCode(),
            ];
        }

        return $this->json([
            'succes'   => true,
            'accounts' => $accountList,
        ]);
    }
}

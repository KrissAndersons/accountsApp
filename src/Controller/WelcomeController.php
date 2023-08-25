<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Client;
use App\Service\CurrencyConverter;
use App\Service\CurrencyRates;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WelcomeController extends AbstractController
{

    private $em;

    /**
     * __construct
     *
     * @param  EntityManagerInterface $em
     * @return void
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'app_welcome', methods:['GET'])]
    public function index(): Response
    {
        $repo    = $this->em->getRepository(Client::class);
        $clients = $repo->findBy(['name' => 'client1']);
        
        $clientId  = '';
        $accountId = '';
        if (!empty($clients)){
            $clientId = $clients[0]->getId();

            $accounts = $clients[0]->getAccounts();
            if (!empty($accounts)) {
                $accountId = $accounts[0]->getId();
            }
        }

        return $this->render('index.html.twig', [ 
            'client_id'  => $clientId, 
            'account_id' => $accountId,
        ]);
    }
}

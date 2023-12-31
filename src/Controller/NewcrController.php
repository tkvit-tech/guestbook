<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewcrController extends AbstractController
{
    #[Route('/newcr', name: 'app_newcr')]
    public function index(): Response
    {
        return $this->render('newcr/index.html.twig', [
            'controller_name' => 'NewcrController',
        ]);
    }
}

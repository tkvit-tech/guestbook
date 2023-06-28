<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{
    //#[Route('/conference', name: 'app_conference')]
    #[Route('/', name: 'homepage')]
    public function index(Request $request): Response
    {
/*        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
        ]);

*/
        $name = $request->query->get('hello');
        return new Response(<<<EOF
            <html>
                <body>привет $name
                    <img src="/images/under-construction.png" />
                </body>
            </html>
            EOF
        );
    }
}

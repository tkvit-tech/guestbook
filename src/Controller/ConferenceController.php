<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ConferenceController extends AbstractController
{
    //#[Route('/conference', name: 'app_conference')]
    #[Route('/', name: 'homepage')]
    public function index(Environment $environment, ConferenceRepository $conferenceRepository): Response
    {
/*        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
        ]);
*/
    return new Response($environment->render('conference/index.html.twig', [
       'conferences' => $conferenceRepository->findAll()
    ]));

    }

    #[Route('/conference/{id}', name: 'conference')]
    public function show(Request $request,#[Autowire('%photo_dir%')] string $photoDir, Environment $environment, int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($id, $offset);
        $conf = $entityManager->getRepository(Conference::class)->find($id);
//dd($paginator);
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            //dd($form['photoFilename']->getData()->getClientOriginalName());
            //echo "1_".$form['photoFilename']; die();
            $comment = $form->getData();
            $comment->setCreatedAt(new \DateTimeImmutable('now'));
            //$comment->setPhotoFilename('1.jpg');
            if ($photo = $form['photoFilename']->getData()) {
                               $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                                $photo->move($photoDir, $filename);
                                $comment->setPhotoFilename($filename);
                            }
            $comment->setConference($conf);
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('conference', ['id' => $id]);
        }
         return new Response($environment->render('conference/show.html.twig', [
             'conference' => $id,
                         'conf' => $conf,
                         'comments' => $paginator,
                         'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
                         'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
             'comment_form' => $form->createView(),
         ]));
    }
}

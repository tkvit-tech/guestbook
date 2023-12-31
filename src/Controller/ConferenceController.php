<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
//use App\SpamChecker;
use App\Message\CommentMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

class ConferenceController extends AbstractController
{
    const PREFIX = 'REDIS_TEST';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $bus,
        private Client $client
     ) {
     }

    //#[Route('/conference', name: 'app_conference')]
    #[Route('/', name: 'homepage')]
    public function index(Environment $environment, ConferenceRepository $conferenceRepository): Response
    {
/*        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
        ]);
*/
        //dd($this->client);
        //$this->client->connect("redis", 6379);
        //$this->client->set('123','456',(new \DateTime())->format('Ymd'));
        //$this->client->hmset("123", array(1,2,3));
        //echo $this->client->hmget("123",array(1,2,3));

        $key = \sprintf('%s:%d', self::PREFIX, (new \DateTime())->format('Ymd'));

        $data = [];


        if (!$this->client->exists($key)) {

            $this->client->pipeline(

                function ($pipe) use ($key, $data) {



                    $pipe->rpush(\sprintf('%s:key_list', self::PREFIX), [$key]);

                    $pipe->hmset(

                        $key,

                        ['json' => json_encode(['first' => '123','second' => '456'])]

                    );

                }

            );

        }


        //dump($this->client->lrange(\sprintf('%s:key_list', self::PREFIX), 0, -1));

        //dump(json_decode($this->client->hgetall($key)['json']));
        //die();

    return new Response($environment->render('conference/index.html.twig', [
       'conferences' => $conferenceRepository->findAll()
    ]));

    }

     #[Route('/conference_header', name: 'conference_header')]
     public function conferenceHeader(ConferenceRepository $conferenceRepository): Response
     {
         return $this->render('conference/header.html.twig', [
             'conferences' => $conferenceRepository->findAll(),
         ]);
     }

    #[Route('/conference/{id}', name: 'conference')]
    public function show(Request $request,#[Autowire('%photo_dir%')] string $photoDir, NotifierInterface $notifier, Environment $environment, int $id, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
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
            //echo "11".dd($comment);
            $entityManager->persist($comment);
            $this->entityManager->flush();
            $context = [
                                'user_ip' => $request->getClientIp(),
                                'user_agent' => $request->headers->get('user-agent'),
                                'referrer' => $request->headers->get('referer'),
                                'permalink' => $request->getUri(),
                            ];
            //echo "__".$spamChecker->getSpamScore($comment, $context);die();
 //                       if (2 === $spamChecker->getSpamScore($comment, $context)) {
 //                           throw new \RuntimeException('Blatant spam, go away!');
 //           }
 //           $entityManager->flush();
            $this->bus->dispatch(new CommentMessage($comment->getId(), $context));
            $notifier->send(new Notification('Thank you for the feedback; your comment will be posted after moderation.', ['browser']));
            return $this->redirectToRoute('conference', ['id' => $id]);
        }
        if ($form->isSubmitted()) {
            $notifier->send(new Notification('Can you check your submission? There are some problems with it.', ['browser']));
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

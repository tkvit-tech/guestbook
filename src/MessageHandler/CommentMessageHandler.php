<?php

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;
//use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
//use Symfony\Component\Mailer\MailerInterface;
use App\Notification\CommentReviewNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use App\ImageOptimizer;

#[AsMessageHandler]
class CommentMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SpamChecker $spamChecker,
        private CommentRepository $commentRepository,
        private MessageBusInterface $bus,
        private WorkflowInterface $commentStateMachine,
        //private MailerInterface $mailer,
        //#[Autowire('%admin_email%')] private string $adminEmail,
        private NotifierInterface $notifier,
        private ImageOptimizer $imageOptimizer,
        #[Autowire('%photo_dir%')] private string $photoDir,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function __invoke(CommentMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return;
        }
        //echo "!!!_";
        //dd($this->spamChecker->getSpamScore($comment, $message->getContext()));
        /*if ($this->spamChecker->getSpamScore($comment, $message->getContext()) == 2 || $this->spamChecker->getSpamScore($comment, $message->getContext()) == 1) {
            $comment->setState('spam');
        } else {
            $comment->setState('published');
        }*/

        if ($this->commentStateMachine->can($comment, 'accept')) {
                    $score = $this->spamChecker->getSpamScore($comment, $message->getContext());
                    $transition = match ($score) {
                            2 => 'reject_spam',
                            1 => 'might_be_spam',
                            default => 'accept',
        };
                    //echo $transition."___";
                    //die();
            $this->commentStateMachine->apply($comment, $transition);
            $this->entityManager->flush();
            $this->bus->dispatch($message);
        } elseif ($this->commentStateMachine->can($comment, 'publish') || $this->commentStateMachine->can($comment, 'publish_ham')) {
                                $this->commentStateMachine->apply($comment, $this->commentStateMachine->can($comment, 'publish') ? 'publish' : 'publish_ham');
                                $this->entityManager->flush();
            $this->bus->dispatch($message);

                    /*
                                $this->mailer->send((new NotificationEmail())
                                    ->subject('New comment posted')
                                    ->htmlTemplate('emails/comment_notification.html.twig')
                                    ->from($this->adminEmail)
                                    ->to($this->adminEmail)
                                    ->context(['comment' => $comment])
                                );*/
            $this->notifier->send(new CommentReviewNotification($comment), ...$this->notifier->getAdminRecipients());
        }
        elseif ($this->commentStateMachine->can($comment, 'optimize')) {
            if ($comment->getPhotoFilename()) {
                    $this->imageOptimizer->resize($this->photoDir.'/'.$comment->getPhotoFilename());
            }
                    $this->commentStateMachine->apply($comment, 'optimize');
                    $this->entityManager->flush();

        }
        elseif ($this->logger) {
                    $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }

        //$this->entityManager->flush();
    }

}
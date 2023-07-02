<?php

namespace App\EventSubscriber;

use App\Repository\ConferenceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

class TwigMainMenuSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $conferenceRepository;

    public function __construct(Environment $environment, ConferenceRepository $conferenceRepository)
    {
        $this->twig = $environment;
        $this->conferenceRepository = $conferenceRepository;
    }
    public function onControllerEvent(ControllerEvent $event): void
    {
        // ...
        $this->twig->addGlobal('conferences', $this->conferenceRepository->findAll());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => 'onControllerEvent',
        ];
    }
}

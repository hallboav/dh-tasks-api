<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Task;
use App\Message\CompletedTaskMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

final class TaskSubscriber implements EventSubscriberInterface
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                ['onPostWrite', EventPriorities::POST_WRITE],
            ],
        ];
    }

    /**
     * Triggered after DataPersister has been called.
     *
     * @param ViewEvent $event
     *
     * @return void
     */
    public function onPostWrite(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $previousTask = $request->attributes->get('previous_data');
        $task = $event->getControllerResult();

        if (!($request->isMethod(Request::METHOD_PATCH) && $task instanceof Task && $previousTask instanceof Task)) {
            return;
        }

        $this->bus->dispatch(new CompletedTaskMessage($previousTask, $task));
    }
}

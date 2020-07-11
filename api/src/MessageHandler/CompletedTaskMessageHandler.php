<?php

namespace App\MessageHandler;

use App\Message\CompletedTaskMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CompletedTaskMessageHandler implements MessageHandlerInterface
{
    public function __invoke(CompletedTaskMessage $message)
    {
        $message->getTask()->getTitle();
    }
}

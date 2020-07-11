<?php

namespace App\Message;

use App\Entity\Task;

class CompletedTaskMessage
{
    private $previousTask;
    private $task;

    public function __construct(Task $previousTask, Task $task)
    {
        $this->previousTask = $previousTask;
        $this->task = $task;
    }

    public function getPreviousTask(): Task
    {
        return $this->previousTask;
    }

    public function getTask(): Task
    {
        return $this->task;
    }
}

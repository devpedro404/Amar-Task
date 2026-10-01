<?php

namespace App\Actions\Tasks;

use App\Models\Task;

class ToggleTaskCompletion
{
    public function execute(Task $task): Task
    {
        $task->update([
            'completed_at' => $task->isCompleted() ? null : now(),
        ]);

        return $task;
    }
}

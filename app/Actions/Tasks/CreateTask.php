<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;

class CreateTask
{
    /**
     * @param  array{title: string, description?: string|null}  $data
     */
    public function execute(User $owner, array $data): Task
    {
        return $owner->tasks()->create($data);
    }
}

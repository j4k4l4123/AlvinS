<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-list-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List users and roles for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::with('roles')->orderBy('id')->get();

        foreach ($users as $user) {
            $this->line(sprintf(
                '%s | %s | %s | %s',
                $user->id,
                $user->name,
                $user->email,
                $user->roles->pluck('name')->implode(',') ?: '-'
            ));
        }

        return self::SUCCESS;
    }
}

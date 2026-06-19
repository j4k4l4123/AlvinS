<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DebugCheckPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-check-password {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check whether a password matches a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('User not found');
            return self::FAILURE;
        }

        $matches = Hash::check($this->argument('password'), $user->password);
        $this->line($matches ? 'MATCH' : 'NO_MATCH');

        return $matches ? self::SUCCESS : self::FAILURE;
    }
}

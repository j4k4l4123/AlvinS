<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DebugSessionConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-session-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show resolved session configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keys = [
            'app.env',
            'app.url',
            'session.driver',
            'session.domain',
            'session.path',
            'session.secure',
            'session.same_site',
            'session.cookie',
        ];

        foreach ($keys as $key) {
            $value = Config::get($key);
            $this->line($key . ' = ' . var_export($value, true));
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class DebugConfig extends Command
{
    protected $signature = 'debug:config';
    protected $description = 'Debug configuration';

    public function handle()
    {
        $this->info('=== CONFIGURATION DEBUG ===');
        $this->info('SESSION_DRIVER env: ' . env('SESSION_DRIVER'));
        $this->info('SESSION_DRIVER config: ' . config('session.driver'));
        $this->info('DB_CONNECTION env: ' . env('DB_CONNECTION'));
        $this->info('DB_CONNECTION config: ' . config('database.default'));
        
        $this->info('=== DATABASE CONNECTIONS ===');
        foreach (config('database.connections') as $name => $connection) {
            $this->info("$name: " . ($connection['driver'] ?? 'unknown'));
        }
        
        return 0;
    }
}
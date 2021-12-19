<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Parse extends Command
{
    protected $signature = 'parse';

    protected $description = 'Parse CSV to create Markdown table';

    public function handle() : void
    {
        
    }
}

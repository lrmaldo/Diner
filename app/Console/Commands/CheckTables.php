<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $names = array_map(function ($t) {
            return array_values((array) $t)[0];
        }, $tables);
        $this->info(implode(', ', $names));
    }
}

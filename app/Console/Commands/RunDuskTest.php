<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class RunDuskTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dusk:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run Laravel Dusk tests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $process = new Process(['php', 'artisan', 'dusk']);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Dusk tests failed.');
            return 1;
        }

        $this->info('Dusk tests passed.');
        return 0;
    }
}


<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use App\Helpers\JUnitReporter;

class RunIntegrationTest extends Command
{
    protected $signature = 'test:integration-report';

    public function handle()
    {
        $process = new Process([
            PHP_BINARY,
            base_path('artisan'),
            'test',
            '--testsuite=Integration',
            '--log-junit=' . storage_path('logs/junit.xml'),
        ]);

        $process->setTimeout(null);

        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            $this->warn('Integration test finished with failures/errors.');
        }

        JUnitReporter::send(storage_path('logs/junit.xml'));

        $this->info('Report uploaded successfully.');
    }
}
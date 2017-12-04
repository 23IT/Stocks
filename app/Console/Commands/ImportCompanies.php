<?php

namespace App\Console\Commands;

use App\Company;
use Illuminate\Console\Command;
use Mockery\Exception;

class ImportCompanies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:import-companies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports the company dictionary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dictionary = file('http://bossa.pl/pub/ciagle/mstock/metacgl.lst');

        $dictionary = array_splice($dictionary,3, count($dictionary)-4);

        $dictionary = array_map(function($line){
            return explode(',', preg_replace("/\s+/", ',', rtrim($line)));
        }, $dictionary);

        $elementCount = count($dictionary);

        $bar = $this->output->createProgressBar($elementCount);

        foreach ($dictionary as $line) {
            try {
                $result = Company::updateOrCreate([
                    'symbol' => $line[2],
                ], [
                    'date_from' => $line[0],
                    'date_to' => $line[1],
                    'description' => isset($line[3]) ? $line[3] : '',
                ]);
            } catch(Exception $e) {
                $this->error('An error occurred!');
                $this->error($e->getMessage());
                dump($line);
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->info("\n" . $elementCount . ' Records imported/updated.');
    }
}

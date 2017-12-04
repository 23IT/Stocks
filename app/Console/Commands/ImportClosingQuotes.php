<?php

namespace App\Console\Commands;

use App\ClosingQuote;
use Illuminate\Console\Command;
use Mockery\Exception;

class ImportClosingQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:import-closing-quotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $data = file('http://bossa.pl/pub/ciagle/mstock/sesjacgl/sesjacgl.prn');

        $data = array_map(function($line){
            return explode(',', rtrim($line));
        }, $data);

        $elementCount = count($data);

        $bar = $this->output->createProgressBar($elementCount);

        foreach ($data as $line) {
            try {
                $quote = new ClosingQuote([
                    'symbol' => $line[0],
                    'date_stamp' => $line[1],
                    'date_quote' => date_create_from_format('Ymd', $line[1]),
                    'open' => $line[2],
                    'high' => $line[3],
                    'low' => $line[4],
                    'close' => $line[5],
                    'volumes' => $line[6]
                ]);
                $quote->save();
            } catch (Exception $e) {
                $this->error('An error occurred!');
                $this->error($e->getMessage());
                dump($line);
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();

        $this->info("\nImported $elementCount records.");
    }
}

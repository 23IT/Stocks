<?php

namespace App\Console\Commands;

use App\Company;
use App\IntraDayQuote;
use Faker\Provider\DateTime;
use Illuminate\Console\Command;

class ImportHistoricalStocksData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:import-historical-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The url path to the files
     *
     * @var string
     */
    protected $url = 'http://bossa.pl/pub/intraday/mstock/cgl/';

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

        $previousWorkDay = new \DateTime('@' . strtotime('today -1 Weekday'));

        $companies = Company::where('date_to', '>=', $previousWorkDay)->get();

        $companyCount = count($companies);

        if ($companyCount == 0) {
            $this->error('There are no Company records in the database. Please run the stocks:import-companies command.');
            exit();
        }

        $directory = file_get_contents($this->url);

        preg_match_all("<a href=\x22(.+?)\x22>", $directory, $files);

        $files = $files[1];
        array_shift($files);

        $downloads = [];

        foreach ($files as $filename) {
            $symbol = strtoupper(substr($filename,0, -4));
            $downloads[$symbol] = $filename;
        }

        foreach ($companies as $company) {

            if (!array_key_exists($company->symbol, $downloads)) {
                $this->warn("Could not find a file corresponding to the following symbol: $company->symbol");
                $this->warn("The stock was monitored from {$company->date_from->format('Y-m-d')} to {$company->date_to->format('Y-m-d')}\n");
                continue;
            }

            $this->info("Downloading {$downloads[$company->symbol]}...");

            $source = $this->url . $downloads[$company->symbol];
            $destination = '/tmp/' . $downloads[$company->symbol];

            if (!copy($source, $destination)) {
                $errors = error_get_last();
                $this->error('File copy error: ' . $errors['type']);
                $this->error($errors['message']);
                dump($errors);
                exit();
            } else {
                $this->info('Complete!');
            }

            $extractPath = '/tmp/stocks_extracted/';
            $filePath = $extractPath . substr($downloads[$company->symbol], 0, -3) . 'prn';

            $this->info("Extracting archive \"$destination\" to \"$extractPath\"...");

            $archive = new \ZipArchive();
            $archive->open($destination);
            $archive->extractTo($extractPath);

            $this->info("Done!");

            $data = file($filePath);

            $dataBar = $this->output->createProgressBar(count($data));
            $dataBar->setMessage("Importing $company->symbol...");

            foreach ($data as $row) {

                $line = explode(',', $row);

                $quote = new IntraDayQuote([
                    'symbol' => $line[0],
                    'unknown_value1' => $line[1],
                    'date_stamp' => $line[2],
                    'time_stamp' => $line[3],
                    'datetime_quote' => date_create_from_format('Ymd His', $line[2] . ' ' . $line[3]),
                    'open' => $line[4],
                    'high' => $line[5],
                    'low' => $line[6],
                    'close' => $line[7],
                    'volumes' => $line[8],
                    'unknown_value2' => $line[9]
                ]);
                $quote->save();
                $dataBar->advance();
            }
            $dataBar->finish();

        }
    }
}

<?php

namespace App\Console\Commands;

use App\Company;
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
        $companies = Company::all();

        $companyCount = count($companies);

        if ($companyCount == 0) {
            $this->error('There are no Company records in the database. Please run the stocks:import-companies command.');
            exit();
        }

        $directory = file_get_contents($this->url);

        preg_match_all("<a href=\x22(.+?)\x22>", $directory, $files);

        //$files = str_replace('a href=', '', $files);
        //$files = str_replace('"', '', $files);
        $files = $files[1];
        array_shift($files);

        dd($files);

        foreach ($companies as $company) {
            echo $company->symbol . "\n";

            if (!copy($this->url . $company->symbol . '.zip', "/tmp/$company->symbol.zip")) {
                $errors = error_get_last();
                $this->error('File copy error: ' . $errors['type']);
                $this->error($errors['message']);
                dd($errors);
                exit();
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ImportPrnFileData;
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
    protected $url = 'http://bossa.pl/pub/metastock/mstock/sesjaall/';

    /**
     * The path to store the downloaded data
     *
     * @var string
     */
    protected $downloadPath = '/var/www/stocks_archives/';

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

        $directory = file_get_contents($this->url);

        preg_match_all("<a href=\x22(.+?zip)\x22>", $directory, $files);
        preg_match_all("/(.+?zip).*(([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d{2}:\d{2})))/", $directory, $dates);

        $dates = array_reverse($dates[2], false);

        $files = array_reverse($files[1], false);

        for ($i=0 ; $i < count($files) ; $i++) {
            $filename = $this->downloadPath . $files[$i];
            $modified = date_create_from_format('Y-m-d H:i', $dates[$i]);

            if ((file_exists($filename) &&  date_create(filemtime($filename)) < $modified) ||  !file_exists($filename)) {

                $this->info("FILE $filename NOT FOUND LOCALLY, DOWNLOADING...\n");

                $source = $this->url . $files[$i];

                $this->downloadFile($source, $filename);

            } else {
                $this->info("FILE $filename EXISTS, USING LOCAL COPY...\n");
            }

        }

        $cnt = $this->importDirectory($this->downloadPath);

        $this->info("Successfully created $cnt import jobs\n");
    }

    protected function downloadFile($source, $destination)
    {
        if (!copy($source, $destination)) {
            $errors = error_get_last();
            $this->error('File copy error: ' . $errors['type']);
            $this->error($errors['message']);
            dump($errors);
            exit();
        } else {
            $this->info('Complete!');
        }

        return true;
    }

    protected function unzipFile($path)
    {
        $destination = substr($path, 0, -4);
        $archive = new \ZipArchive();
        $archive->open($path);
        $archive->extractTo($destination);

        return $destination;
    }

    protected function importDirectory($directory)
    {
        $count = 0;
        $files = array_slice(scandir($directory), 2);

        foreach ($files as $file) {
            $filepath = $directory . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filepath)) {
                $count += $this->importDirectory($filepath);
            }

            if (is_file($filepath) && stristr($file, '.zip')) {
                $unzipped = $this->unzipFile($filepath);
                $count += $this->importDirectory($unzipped);
            }

            if (is_file($filepath) && stristr($file, '.prn')) {
                dispatch(new ImportPrnFileData($filepath));
                $count++;

            }
        }
        return $count;
    }
}

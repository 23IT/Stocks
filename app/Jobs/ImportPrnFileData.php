<?php

namespace App\Jobs;

use App\ClosingQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportPrnFileData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filepath;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filepath)
    {
        $this->filepath = $filepath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = file($this->filepath);

            foreach ($data as $row) {
                $line = explode(',', $row);

                $quote = new ClosingQuote([
                    'symbol' => $line[0],
                    'date_stamp' => $line[1],
                    'date_quote' => date_create_from_format('Ymd', $line[1]),
                    'open' => $line[2],
                    'high' => $line[3],
                    'low' => $line[4],
                    'close' => $line[5],
                    'volumes' => $line[6],
                ]);
                $quote->save();
            }

        } catch (\Exception $e) {
            dump($e);
        }
    }
}

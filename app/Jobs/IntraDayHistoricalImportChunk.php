<?php

namespace App\Jobs;

use App\IntraDayQuote;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mockery\Exception;

class IntraDayHistoricalImportChunk implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            foreach ($this->data as $row) {
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
            }
        } catch (Exception $e) {
            //TODO: log the problem
        }
    }
}

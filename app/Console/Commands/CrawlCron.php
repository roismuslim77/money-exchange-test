<?php

namespace App\Console\Commands;

use App\Http\Controllers\ScrapController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class CrawlCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:cron';

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
     * @return int
     */
    public function handle(Request $request)
    {
        $scrap = new ScrapController();
        $res = $scrap->scrapMoney($request);

        \Log::info($res);
    }
}

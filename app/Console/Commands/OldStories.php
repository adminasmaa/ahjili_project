<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class OldStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oldstories:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this cron job is used to delete uploaded stories older then 24hours';

    public function __construct()
    {
        parent::__construct();
    }
  

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {   

        info("Delete old stories cron Job running at ". now());

        $stories=Story::with('getStoryMedia')->where('created_at', '<',now()->subHours(24))->get();
    
        ini_set('memory_limit', '44M');

        foreach ($stories as $key => $story) {
            $storymedia=$story->getStoryMedia;
            if(count($storymedia)>0){
                foreach ($storymedia as $single) {
                    $opath= explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
                    $path= $opath[1]?? "";
                    if(Storage::disk('s3')->exists($path)) {
                        Storage::disk('s3')->delete($path);
                    }
                    $single->delete();
                }
            }
            $story->delete();
        }

        info("Delete old stories cron Job end ". now());

        return 0;
    }
}

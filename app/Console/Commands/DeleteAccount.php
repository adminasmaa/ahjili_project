<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteAccount extends Command
{
    /**
      * The name and signature of the console command.
      *
      * @var string
      */
    protected $signature = 'delete_account:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this cron job is used to delete accounts older then 30 days';

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
        info("Delete accounts cron Job running at ". now());

        $users=User::onlyTrashed()->with('posts')->where('deleted_at', '<', now()->subDays(30))->get();

        ini_set('memory_limit', '44M');

        foreach ($users as $key => $user) {
            // get profile image
            $image_profile= !$user->profile_image ? explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $user->profile_image) : null;
            // check found this image
            if ($image_profile) {
                $image_profile = $image_profile[1] ?? "";
                if (Storage::disk('s3')->exists($image_profile)) {
                    Storage::disk('s3')->delete($image_profile);
                }
            }
            // deleted posts
            $posts = $user->posts;
            foreach ($posts as $post) {
                $postmedia=$post->getPostImages;
                if (count($postmedia)>0) {
                    foreach ($postmedia as $single) {
                        $opath= explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path);
                        $thumb_path=explode('https://ahjili.s3.eu-central-1.amazonaws.com/', $single->path_thumbnail);
                        $path= $opath[1]?? "";
                        $thumb_path= $thumb_path[1]?? "";
                        if ($path) {
                            if (Storage::disk('s3')->exists($path)) {
                                Storage::disk('s3')->delete($path);
                            }
                        }
                        if ($thumb_path) {
                            if (Storage::disk('s3')->exists($thumb_path)) {
                                Storage::disk('s3')->delete($thumb_path);
                            }
                        }
                        $single->delete();
                    }
                }
                $post->delete();
            }
            // delete user
            $user->forceDelete();
        }

        info("Delete accounts cron Job end ". now());

        return 0;
    }
}

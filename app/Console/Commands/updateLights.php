<?php

namespace App\Console\Commands;

use App\LiveImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class updateLights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:lights';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update light status of each image';

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
        // Retrieve all images where lightsOn = null

        $imagesToUpdate = LiveImage::where('lightsOn', null)->get();
        foreach ($imagesToUpdate as $image) {
            try {
                $imageSize = Storage::size('/source/' . trim($image->imageName) . '.jpg');
                if ($imageSize < 400000) {
                    $image->lightsOn = false;
                } else {
                    $image->lightsOn = true;
                }
                $image->save();
            } catch (Exception $e) {
                print("The image " . $image->imageName . " cannot be found\n");
            }
        }

        print("A total of " . count($imagesToUpdate) . " images were updated with light status\n");

    }
}

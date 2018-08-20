<?php

namespace App\Console\Commands;

use App\Photo;
use Illuminate\Console\Command;

class medium extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medium {nb=5 : generate medium pictures if missing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create medium pictures if missing';

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
        set_time_limit(600);

        $argument = $this->argument('nb');
        $photos = Photo::where('medium','=',0)->limit($argument)->get();
        if(count($photos) == 0)
        {
            $this->line('No pictures requires medium.');
            return false;
        }

        foreach ($photos as $photo){
            if( $photo->createMedium() )
            {
                $photo->medium = 1;
                $photo->save();
                $this->line('medium for '.$photo->title.' created');
            }
            else
            {
                $this->line('Could not create medium for '.$photo->title.' created');
            }
        }
    }
}

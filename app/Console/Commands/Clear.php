<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Clear:All';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reoptimises class loader. Clear Cache facade value. Clear Route, view, config cache. Configs config cache';

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
        $this->call('optimize');
        $this->call('cache:clear');
        $this->call('route:cache');
        $this->call('view:clear');
        $this->call('config:cache');
        $this->call('config:clear');

    }
}

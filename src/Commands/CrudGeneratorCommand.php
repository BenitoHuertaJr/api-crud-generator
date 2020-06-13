<?php

namespace iamx\ApiCrudGenerator\Commands;

use iamx\ApiCrudGenerator\Traits\CrudTrait;
use Illuminate\Console\Command;

class CrudGeneratorCommand extends Command
{
    use CrudTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:crud {name : Class (singular) for example Test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new api crud template from model';

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
        $this->makeCrud($this->argument('name'));
    }
}

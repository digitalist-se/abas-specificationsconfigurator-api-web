<?php

namespace App\Console\Commands;

use App\Http\Resources\SpecificationDocument;
use App\Models\User;
use Illuminate\Console\Command;

class TestExport extends Command
{
    const EXPORT_PATH = 'app/export';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create demo excel from template';

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
        $outputDir = storage_path(self::EXPORT_PATH);
        $user = User::first();
        $answers = $user->answers()->get();
        $specificationDocument = new SpecificationDocument($outputDir, $user, $answers);
        $specificationDocument->save();

        return 0;
    }
}

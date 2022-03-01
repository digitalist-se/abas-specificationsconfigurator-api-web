<?php

namespace App\Console\Commands;

use App\Http\Controllers\DocumentController;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class ExportCleanCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exports:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete old exported files';

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
        $outputDir = storage_path(DocumentController::EXPORT_PATH);
        $files = File::files($outputDir);
        $deleteBeforeTime = Carbon::now()->subMinutes(30);
        collect($files)
            ->each(function ($file) use ($deleteBeforeTime) {
                /**
                 * @var \Symfony\Component\Finder\SplFileInfo
                 */
                $createTime = Carbon::createFromTimestamp($file->getCTime());
                if ($deleteBeforeTime->greaterThan($createTime)) {
                    File::delete($file->getPathname());
                }
            });
    }
}

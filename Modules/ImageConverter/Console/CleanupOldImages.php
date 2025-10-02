<?php

namespace Modules\ImageConverter\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Facades\Log;

class CleanupOldImages extends Command
{
    protected $signature = 'imageconverter:cleanup {--days=}';
    protected $description = 'Clean up old converted images from the storage directory.';

    public function handle()
    {
        try {
            $days = $this->option('days') ?? config('imageconverter.cleanup_days', 7);

            if ($days <= 0) {
                $this->info('Image cleanup is disabled. Set a positive number of days in config or via --days option.');
                return 0;
            }

            $this->info("Cleaning up images older than {$days} days...");

            $path = config('imageconverter.storage_path', 'public/converted');
            $files = Storage::files($path);
            $threshold = now()->subDays($days)->timestamp;
            $deletedCount = 0;

            foreach ($files as $file) {
                // Ignore placeholder files
                if (basename($file) === '.gitignore' || basename($file) === '.gitkeep') {
                    continue;
                }

                if (Storage::lastModified($file) < $threshold) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }

            if ($deletedCount > 0) {
                $this->info("Successfully deleted {$deletedCount} old image(s).");
            } else {
                $this->info('No old images found to delete.');
            }

            return 0;

        } catch (Exception $e) {
            Log::error('Image cleanup failed: ' . $e->getMessage());
            $this->error('An error occurred during cleanup. Check logs for details.');
            return 1;
        }
    }
}
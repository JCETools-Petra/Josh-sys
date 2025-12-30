<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use ZipArchive;

class BackupFiles extends Command
{
    protected $signature = 'backup:files {--cloud}';

    protected $description = 'Backup important files and uploads';

    public function handle()
    {
        $this->info('Starting files backup...');

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_files_{$timestamp}.zip";
        $backupPath = storage_path("app/backups/{$filename}");

        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Failed to create zip file');
            return 1;
        }

        $directoriesToBackup = [
            storage_path('app/public'),
            public_path('uploads'),
        ];

        $fileCount = 0;

        foreach ($directoriesToBackup as $directory) {
            if (!is_dir($directory)) {
                $this->warn("Directory not found: {$directory}");
                continue;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(base_path()) + 1);

                    $zip->addFile($filePath, $relativePath);
                    $fileCount++;
                }
            }
        }

        $zip->close();

        $this->info("Files backed up: {$filename} ({$fileCount} files)");

        if ($this->option('cloud')) {
            $this->uploadToCloud($backupPath);
        }

        $this->cleanOldBackups();

        $this->info('Files backup completed successfully!');
        return 0;
    }

    protected function uploadToCloud(string $path): void
    {
        try {
            if (config('filesystems.disks.s3')) {
                Storage::disk('s3')->put(
                    'backups/' . basename($path),
                    file_get_contents($path)
                );
                $this->info('Files backup uploaded to cloud storage');
            } else {
                $this->warn('Cloud storage not configured, skipping upload');
            }
        } catch (\Exception $e) {
            $this->error('Cloud upload failed: ' . $e->getMessage());
        }
    }

    protected function cleanOldBackups(): void
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir . '/backup_files_*');

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $keepCount = 15;

        if (count($files) > $keepCount) {
            $filesToDelete = array_slice($files, $keepCount);

            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->line("Deleted old backup: " . basename($file));
            }
        }
    }
}

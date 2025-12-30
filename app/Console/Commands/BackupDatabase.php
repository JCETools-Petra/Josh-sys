<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--compress} {--cloud}';

    protected $description = 'Backup database with optional compression and cloud upload';

    public function handle()
    {
        $this->info('Starting database backup...');

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_database_{$timestamp}.sql";
        $backupPath = storage_path("app/backups/{$filename}");

        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'C:\\xampp\\mysql\\bin\\mysqldump.exe --user=%s --password=%s --host=%s %s > %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('Backup failed!');
            $this->error(implode("\n", $output));
            return 1;
        }

        $this->info("Database backed up: {$filename}");

        if ($this->option('compress')) {
            $this->compressBackup($backupPath);
        }

        if ($this->option('cloud')) {
            $this->uploadToCloud($backupPath);
        }

        $this->cleanOldBackups();

        $this->info('Backup completed successfully!');
        return 0;
    }

    protected function compressBackup(string $path): void
    {
        $gzPath = $path . '.gz';

        if (function_exists('gzencode')) {
            $data = file_get_contents($path);
            $compressed = gzencode($data, 9);
            file_put_contents($gzPath, $compressed);

            unlink($path);

            $this->info("Backup compressed: " . basename($gzPath));
        } else {
            $this->warn('gzencode not available, skipping compression');
        }
    }

    protected function uploadToCloud(string $path): void
    {
        try {
            if (config('filesystems.disks.s3')) {
                Storage::disk('s3')->put(
                    'backups/' . basename($path),
                    file_get_contents($path)
                );
                $this->info('Backup uploaded to cloud storage');
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
        $files = glob($backupDir . '/backup_database_*');

        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $keepCount = 30;

        if (count($files) > $keepCount) {
            $filesToDelete = array_slice($files, $keepCount);

            foreach ($filesToDelete as $file) {
                unlink($file);
                $this->line("Deleted old backup: " . basename($file));
            }
        }
    }
}

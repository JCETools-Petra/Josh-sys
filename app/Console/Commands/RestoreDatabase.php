<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore-database {file}';

    protected $description = 'Restore database from backup file';

    public function handle()
    {
        $file = $this->argument('file');
        $backupPath = storage_path("app/backups/{$file}");

        if (!file_exists($backupPath)) {
            $this->error("Backup file not found: {$file}");
            return 1;
        }

        if (!$this->confirm('This will restore the database. Are you sure?')) {
            $this->info('Restore cancelled');
            return 0;
        }

        $this->warn('DANGER: Restoring database...');

        $isGzipped = str_ends_with($backupPath, '.gz');

        if ($isGzipped) {
            $tempPath = storage_path('app/backups/temp_restore.sql');
            $compressed = file_get_contents($backupPath);
            $data = gzdecode($compressed);
            file_put_contents($tempPath, $data);
            $backupPath = $tempPath;
        }

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $command = sprintf(
            'C:\\xampp\\mysql\\bin\\mysql.exe --user=%s --password=%s --host=%s %s < %s 2>&1',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($backupPath)
        );

        exec($command, $output, $returnVar);

        if ($isGzipped && isset($tempPath)) {
            unlink($tempPath);
        }

        if ($returnVar !== 0) {
            $this->error('Restore failed!');
            $this->error(implode("\n", $output));
            return 1;
        }

        $this->info('Database restored successfully!');
        $this->warn('Please clear cache and restart queue workers if needed');

        return 0;
    }
}

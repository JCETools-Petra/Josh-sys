<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListBackups extends Command
{
    protected $signature = 'backup:list';

    protected $description = 'List all available backups';

    public function handle()
    {
        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            $this->warn('No backups directory found');
            return 0;
        }

        $backups = [];

        $databaseBackups = glob($backupDir . '/backup_database_*');
        $fileBackups = glob($backupDir . '/backup_files_*');

        foreach ($databaseBackups as $file) {
            $backups[] = [
                'type' => 'Database',
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        foreach ($fileBackups as $file) {
            $backups[] = [
                'type' => 'Files',
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        usort($backups, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        if (empty($backups)) {
            $this->info('No backups found');
            return 0;
        }

        $this->table(
            ['Type', 'Filename', 'Size', 'Created'],
            array_map(function ($backup) {
                return [
                    $backup['type'],
                    $backup['filename'],
                    $backup['size'],
                    $backup['date'],
                ];
            }, $backups)
        );

        $this->info('Total backups: ' . count($backups));

        return 0;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

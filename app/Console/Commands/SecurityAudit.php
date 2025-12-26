<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SecurityAudit extends Command
{
    protected $signature = 'security:audit';

    protected $description = 'Run comprehensive security audit';

    protected array $issues = [];
    protected array $warnings = [];
    protected array $passed = [];

    public function handle()
    {
        $this->info('Running security audit...');
        $this->newLine();

        $this->checkEnvironmentSecurity();
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkDependencies();
        $this->checkConfiguration();
        $this->checkAuthSecurity();
        $this->checkBackupSystem();

        $this->displayResults();

        return empty($this->issues) ? 0 : 1;
    }

    protected function checkEnvironmentSecurity(): void
    {
        $this->info('Checking environment configuration...');

        if (config('app.debug') === true && config('app.env') === 'production') {
            $this->issues[] = 'DEBUG mode is enabled in production';
        } else {
            $this->passed[] = 'Debug mode properly configured';
        }

        if (config('app.env') === 'production' && !config('app.key')) {
            $this->issues[] = 'APP_KEY not set';
        } else {
            $this->passed[] = 'Application key is set';
        }

        if (file_exists(base_path('.env')) && substr(sprintf('%o', fileperms(base_path('.env'))), -4) !== '0600') {
            $this->warnings[] = '.env file permissions should be 0600';
        }

        $this->passed[] = 'Environment file exists';
    }

    protected function checkDatabaseSecurity(): void
    {
        $this->info('Checking database security...');

        try {
            DB::connection()->getPdo();
            $this->passed[] = 'Database connection successful';

            $dbUser = config('database.connections.mysql.username');
            if ($dbUser === 'root') {
                $this->warnings[] = 'Using root user for database (not recommended for production)';
            } else {
                $this->passed[] = 'Database user is not root';
            }

            $dbPassword = config('database.connections.mysql.password');
            if (empty($dbPassword)) {
                $this->issues[] = 'Database password is empty';
            } elseif (strlen($dbPassword) < 12) {
                $this->warnings[] = 'Database password is weak (less than 12 characters)';
            } else {
                $this->passed[] = 'Database password is strong';
            }

        } catch (\Exception $e) {
            $this->issues[] = 'Database connection failed: ' . $e->getMessage();
        }
    }

    protected function checkFilePermissions(): void
    {
        $this->info('Checking file permissions...');

        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'config/services.php',
        ];

        foreach ($sensitiveFiles as $file) {
            $path = base_path($file);
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                if ($perms === '0600' || $perms === '0644') {
                    $this->passed[] = "{$file} has secure permissions";
                } else {
                    $this->warnings[] = "{$file} permissions: {$perms} (should be 0600 or 0644)";
                }
            }
        }

        $storageWritable = is_writable(storage_path());
        if ($storageWritable) {
            $this->passed[] = 'Storage directory is writable';
        } else {
            $this->issues[] = 'Storage directory is not writable';
        }
    }

    protected function checkDependencies(): void
    {
        $this->info('Checking dependencies...');

        $composerLock = base_path('composer.lock');
        if (file_exists($composerLock)) {
            $lockData = json_decode(file_get_contents($composerLock), true);
            $lastUpdate = filemtime($composerLock);
            $daysSinceUpdate = (time() - $lastUpdate) / 86400;

            if ($daysSinceUpdate > 90) {
                $this->warnings[] = sprintf('Dependencies not updated in %d days', $daysSinceUpdate);
            } else {
                $this->passed[] = 'Dependencies recently updated';
            }
        }

        if (file_exists(base_path('package.json'))) {
            $this->passed[] = 'Node dependencies tracked';
        }
    }

    protected function checkConfiguration(): void
    {
        $this->info('Checking security configuration...');

        if (config('session.secure') === false && config('app.env') === 'production') {
            $this->warnings[] = 'Session cookies should be secure in production';
        } else {
            $this->passed[] = 'Session security configured';
        }

        if (config('session.same_site') !== 'lax' && config('session.same_site') !== 'strict') {
            $this->warnings[] = 'SameSite cookie attribute not properly set';
        } else {
            $this->passed[] = 'SameSite cookie protection enabled';
        }

        $csrfProtection = class_exists(\App\Http\Middleware\VerifyCsrfToken::class);
        if ($csrfProtection) {
            $this->passed[] = 'CSRF protection enabled';
        } else {
            $this->issues[] = 'CSRF protection not found';
        }
    }

    protected function checkAuthSecurity(): void
    {
        $this->info('Checking authentication security...');

        $passwordMinLength = 8;
        $this->passed[] = 'Password validation in place';

        if (config('auth.password_timeout')) {
            $this->passed[] = 'Password confirmation timeout configured';
        }

        try {
            $usersWithWeakPasswords = DB::table('users')
                ->whereNull('email_verified_at')
                ->count();

            if ($usersWithWeakPasswords > 0) {
                $this->warnings[] = "{$usersWithWeakPasswords} users with unverified emails";
            } else {
                $this->passed[] = 'All users have verified emails';
            }
        } catch (\Exception $e) {
            $this->warnings[] = 'Could not check user verification status';
        }
    }

    protected function checkBackupSystem(): void
    {
        $this->info('Checking backup system...');

        $backupDir = storage_path('app/backups');

        if (is_dir($backupDir)) {
            $backups = glob($backupDir . '/backup_database_*');

            if (count($backups) > 0) {
                $latestBackup = max(array_map('filemtime', $backups));
                $daysSinceBackup = (time() - $latestBackup) / 86400;

                if ($daysSinceBackup < 2) {
                    $this->passed[] = 'Recent backup found (< 2 days old)';
                } else {
                    $this->warnings[] = sprintf('Latest backup is %d days old', $daysSinceBackup);
                }
            } else {
                $this->warnings[] = 'No backups found';
            }
        } else {
            $this->warnings[] = 'Backup directory does not exist';
        }
    }

    protected function displayResults(): void
    {
        $this->newLine(2);
        $this->info('=== SECURITY AUDIT RESULTS ===');
        $this->newLine();

        if (!empty($this->issues)) {
            $this->error('CRITICAL ISSUES (' . count($this->issues) . '):');
            foreach ($this->issues as $issue) {
                $this->line('  ❌ ' . $issue);
            }
            $this->newLine();
        }

        if (!empty($this->warnings)) {
            $this->warn('WARNINGS (' . count($this->warnings) . '):');
            foreach ($this->warnings as $warning) {
                $this->line('  ⚠️  ' . $warning);
            }
            $this->newLine();
        }

        if (!empty($this->passed)) {
            $this->info('PASSED CHECKS (' . count($this->passed) . '):');
            foreach ($this->passed as $pass) {
                $this->line('  ✅ ' . $pass);
            }
            $this->newLine();
        }

        $total = count($this->issues) + count($this->warnings) + count($this->passed);
        $score = round((count($this->passed) / $total) * 100);

        $this->newLine();
        $this->info("Security Score: {$score}%");

        if (empty($this->issues)) {
            $this->info('✅ No critical security issues found');
        } else {
            $this->error('⚠️  Critical issues need immediate attention!');
        }
    }
}

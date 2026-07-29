<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--notify : Kirim notifikasi email setelah backup}';

    protected $description = 'Backup database MySQL ke storage/backups';

    public function handle(): int
    {
        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $tanggal = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$db}_{$tanggal}.sql";
        $path = storage_path("app/backups/{$filename}");

        // Buat folder backups jika belum ada
        if (! is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $optionFile = tempnam(storage_path('app'), 'mysql-backup-');
        file_put_contents($optionFile, implode("\n", [
            '[client]',
            'host='.$this->optionValue($host),
            'port='.$this->optionValue($port),
            'user='.$this->optionValue($user),
            'password='.$this->optionValue($password),
            '',
        ]));
        chmod($optionFile, 0600);

        try {
            $process = new Process([
                'mysqldump',
                "--defaults-extra-file={$optionFile}",
                '--single-transaction',
                '--quick',
                "--result-file={$path}",
                $db,
            ]);
            $process->setTimeout(600)->run();
            if (! $process->isSuccessful()) {
                @unlink($path);
                $this->error('Backup gagal: '.$process->getErrorOutput());

                return Command::FAILURE;
            }

            chmod($path, 0600);
        } finally {
            @unlink($optionFile);
        }

        $size = round(filesize($path) / 1024, 1);
        $this->info("Backup berhasil: {$filename} ({$size} KB)");

        // Hapus backup lebih dari 7 hari
        $this->hapusBackupLama();

        // Kirim email notifikasi jika diminta
        if ($this->option('notify') && config('mail.default')) {
            $this->kirimNotifikasi($filename, $size);
        }

        return Command::SUCCESS;
    }

    protected function hapusBackupLama(): void
    {
        $backupDir = storage_path('app/backups');
        $files = glob($backupDir.'/backup_*.sql');

        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-7 days')) {
                unlink($file);
                $this->line('Hapus backup lama: '.basename($file));
            }
        }
    }

    protected function kirimNotifikasi(string $filename, float $size): void
    {
        try {
            $adminEmail = config('mail.admin_email', config('mail.from.address'));
            Mail::raw(
                "Backup database berhasil.\n\nFile: {$filename}\nUkuran: {$size} KB\nWaktu: ".now()->isoFormat('D MMMM Y, HH:mm'),
                fn ($m) => $m->to($adminEmail)->subject('[Cuci Sepatu] Backup database berhasil')
            );
        } catch (\Exception $e) {
            $this->warn('Email notifikasi gagal: '.$e->getMessage());
        }
    }

    private function optionValue(?string $value): string
    {
        return '"'.addcslashes((string) $value, '\\"').'"';
    }
}

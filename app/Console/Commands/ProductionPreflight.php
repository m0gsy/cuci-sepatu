<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ProductionPreflight extends Command
{
    protected $signature = 'app:production-check';

    protected $description = 'Fail-fast verification for the production runtime';

    public function handle(): int
    {
        $checks = [
            ['Environment', app()->environment('production'), 'APP_ENV must be production'],
            ['Debug mode', config('app.debug') === false, 'APP_DEBUG must be false'],
            ['HTTPS URL', parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https', 'APP_URL must use HTTPS'],
            ['Application key', $this->validAppKey(), 'APP_KEY must be a non-test base64 key'],
            ['MySQL driver', DB::getDriverName() === 'mysql', 'DB_CONNECTION must be mysql'],
            ['Database', $this->databaseWorks(), 'Database query failed'],
            ['Cache', $this->cacheWorks(), 'Persistent cache round-trip failed'],
            ['Queue', ! in_array(config('queue.default'), ['sync', 'null'], true), 'QUEUE_CONNECTION must be asynchronous'],
            ['Session', ! in_array(config('session.driver'), ['array', 'file'], true), 'Use a shared session store'],
            ['Secure cookie', config('session.secure') === true, 'SESSION_SECURE_COOKIE must be true'],
            ['Encrypted session', config('session.encrypt') === true, 'SESSION_ENCRYPT must be true'],
            ['Twilio', $this->twilioConfigured(), 'Twilio SID, token, and WhatsApp sender are required'],
            ['Mail', $this->mailConfigured(), 'A real mailer, sender, and admin address are required'],
            ['Build assets', $this->buildAssetsExist(), 'Build manifest or an asset is missing'],
            ['Writable storage', is_writable(storage_path()) && is_writable(base_path('bootstrap/cache')), 'Storage paths are not writable'],
            ['mysqldump', $this->commandWorks(['mysqldump', '--version']), 'mysqldump is unavailable'],
        ];

        $this->table(
            ['Check', 'Status', 'Remediation'],
            array_map(fn (array $check) => [
                $check[0],
                $check[1] ? 'PASS' : 'FAIL',
                $check[1] ? '' : $check[2],
            ], $checks)
        );

        if (collect($checks)->contains(fn (array $check) => ! $check[1])) {
            $this->error('Production preflight failed.');

            return self::FAILURE;
        }

        $this->info('Production preflight passed.');

        return self::SUCCESS;
    }

    private function validAppKey(): bool
    {
        $key = (string) config('app.key');

        return str_starts_with($key, 'base64:')
            && strlen($key) >= 51
            && $key !== 'base64:T5+1EglVeoQkPKnp8T5SgtDAUWM2O99iiytCBdtVMU=';
    }

    private function databaseWorks(): bool
    {
        try {
            DB::select('SELECT 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function cacheWorks(): bool
    {
        if (in_array(config('cache.default'), ['array', 'null'], true)) {
            return false;
        }

        try {
            $key = 'production-check:'.Str::uuid();
            Cache::put($key, 'ok', 30);
            $works = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $works;
        } catch (\Throwable) {
            return false;
        }
    }

    private function twilioConfigured(): bool
    {
        return str_starts_with((string) config('services.twilio.sid'), 'AC')
            && strlen((string) config('services.twilio.token')) >= 20
            && str_starts_with((string) config('services.twilio.from'), '+');
    }

    private function mailConfigured(): bool
    {
        $mailer = config('mail.default');
        $configured = ! in_array($mailer, ['array', 'log'], true)
            && filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)
            && ! str_ends_with((string) config('mail.from.address'), '@example.com')
            && filter_var(config('mail.admin_address'), FILTER_VALIDATE_EMAIL);

        if ($mailer === 'smtp') {
            $smtp = config('mail.mailers.smtp');
            $configured = $configured
                && ! in_array($smtp['host'] ?? null, [null, '', '127.0.0.1', 'localhost'], true)
                && ! empty($smtp['username'])
                && ! empty($smtp['password']);
        }

        return $configured;
    }

    private function buildAssetsExist(): bool
    {
        $manifestPath = public_path('build/manifest.json');
        if (! is_file($manifestPath)) {
            return false;
        }

        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        if (! is_array($manifest) || $manifest === []) {
            return false;
        }

        foreach ($manifest as $entry) {
            if (empty($entry['file']) || ! is_file(public_path('build/'.$entry['file']))) {
                return false;
            }
        }

        return true;
    }

    private function commandWorks(array $command): bool
    {
        try {
            return (new Process($command))->setTimeout(10)->run() === 0;
        } catch (\Throwable) {
            return false;
        }
    }
}

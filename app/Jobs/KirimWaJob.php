<?php

namespace App\Jobs;

use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class KirimWaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly string $nomorHp,
        public readonly string $pesan,
    ) {}

    public function handle(WhatsappService $wa): void
    {
        $berhasil = $wa->kirim($this->nomorHp, $this->pesan);
        if (!$berhasil && config('queue.default') !== 'sync') {
            // Async mode: throw triggers retry (tries=3, backoff=60)
            throw new \RuntimeException("WA gagal terkirim ke {$this->nomorHp}");
        }
        // Sync mode: log sudah ada di WhatsappService::kirim(), cukup return
    }

    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::error("KirimWaJob gagal permanen ke {$this->nomorHp}: {$e->getMessage()}");
    }
}

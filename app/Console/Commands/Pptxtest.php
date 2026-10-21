<?php

namespace App\Console\Commands;

use App\Services\PptxReportService;
use Illuminate\Console\Command;

class PptxTest extends Command
{
    protected $signature = 'pptx:test {template=template-02} {--photo=}';

    protected $description = 'Uji pembuatan PDF dari template PPTX dan tampilkan error lengkap';

    public function handle(): int
    {
        $template = $this->argument('template');
        $photo = $this->option('photo') ?: null;

        if ($photo && !is_file($photo)) {
            $this->error("File foto tidak ditemukan: {$photo}");
            return self::FAILURE;
        }

        $data = [
            'hari' => 'Rabu',
            'tanggal' => '21 Oktober 2026',
            'tim' => 'Tes',
            'foto_petugas' => $photo,
            'lokasi' => collect(range(1, 5))->map(fn ($i) => [
                'nama' => "Lokasi $i",
                'status' => $i == 1 ? 'tidak_normal' : 'normal',
                'kondisi' => 'Bersih',
                'catatan' => 'Rapi',
                'masalah' => 'Sampah',
                'solusi' => 'Dibersihkan',
                'foto' => $photo ? [$photo] : [],
            ])->all(),
        ];

        $out = storage_path('app/public/tes.pdf');
        @mkdir(dirname($out), 0777, true);

        $this->info('Membuat PDF, mohon tunggu...');

        try {
            $svc = app(PptxReportService::class);
            $svc->generatePdf($data, $template, $out);

            if (is_file($out)) {
                $this->info("Berhasil: {$out}");
                return self::SUCCESS;
            }

            $this->error('GAGAL: generatePdf selesai tanpa error, tetapi file PDF tidak terbentuk.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('GAGAL: ' . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
            return self::FAILURE;
        }
    }
}
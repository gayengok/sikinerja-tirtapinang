<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $aspects = [
        1 => [
            'nama_kompetensi' => 'Orientasi Pelanggan',
            'definisi_perilaku' => 'Kemampuan memberikan pelayanan yang ramah dan memuaskan.',
        ],
        2 => [
            'nama_kompetensi' => 'Komunikasi',
            'definisi_perilaku' => 'Kemampuan memberikan informasi yang akurat dan jelas.',
        ],
        3 => [
            'nama_kompetensi' => 'Pemecahan Masalah',
            'definisi_perilaku' => 'Kemampuan menyelesaikan masalah pelanggan dengan cepat dan efektif.',
        ],
        4 => [
            'nama_kompetensi' => 'Promosi Perusahaan',
            'definisi_perilaku' => 'Kemampuan membangun citra positif perusahaan di mata pelanggan.',
        ],
        5 => [
            'nama_kompetensi' => 'Kedisiplinan',
            'definisi_perilaku' => 'Kepatuhan terhadap aturan jam kerja.',
        ],
    ];

    public function up(): void
    {
        foreach ($this->aspects as $urutan => $aspect) {
            DB::table('master_kompetensis')
                ->whereNull('jabatan_id')
                ->where('urutan', $urutan)
                ->update($aspect);
        }
    }

    public function down(): void
    {
        $oldAspects = [
            1 => ['Disiplin', 'Mematuhi jam kerja, aturan, dan prosedur kerja yang berlaku secara konsisten.'],
            2 => ['Kerja Sama', 'Mampu bekerja secara efektif dengan rekan kerja dan lintas bagian untuk mencapai tujuan bersama.'],
            3 => ['Tanggung Jawab', 'Menyelesaikan tugas yang diberikan dengan penuh komitmen dan siap menanggung konsekuensi pekerjaan.'],
            4 => ['Komunikasi', 'Menyampaikan informasi dengan jelas, sopan, dan mudah dipahami baik lisan maupun tulisan.'],
            5 => ['Integritas', 'Bersikap jujur, konsisten antara ucapan dan tindakan, serta menjunjung tinggi etika kerja.'],
        ];

        foreach ($oldAspects as $urutan => [$nama, $definisi]) {
            DB::table('master_kompetensis')
                ->whereNull('jabatan_id')
                ->where('urutan', $urutan)
                ->update([
                    'nama_kompetensi' => $nama,
                    'definisi_perilaku' => $definisi,
                ]);
        }
    }
};

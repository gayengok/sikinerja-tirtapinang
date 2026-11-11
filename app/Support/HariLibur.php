<?php

namespace App\Support;

use Carbon\Carbon;

class HariLibur
{
    public static function detail(string $tanggal): ?array
    {
        $date = Carbon::parse($tanggal);

        if ($date->isWeekend()) {
            $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            return [
                'label' => 'hari libur akhir pekan (' . $namaHari[$date->dayOfWeek] . ')',
                'source' => 'Jadwal libur posisi',
            ];
        }

        $tanggalMerah = [
            '01-01' => 'Tahun Baru Masehi',
            '01-05' => 'Hari Buruh Internasional',
            '01-06' => 'Hari Lahir Pancasila',
            '17-08' => 'Hari Kemerdekaan Republik Indonesia',
            '25-12' => 'Hari Natal',
        ];

        if ($date->year === 2026) {
            $tanggalMerah += [
                '16-01' => 'Isra Mikraj Nabi Muhammad SAW',
                '17-02' => 'Tahun Baru Imlek',
                '19-03' => 'Hari Suci Nyepi',
                '20-03' => 'Idulfitri',
                '21-03' => 'Idulfitri',
                '03-04' => 'Wafat Yesus Kristus',
                '14-05' => 'Kenaikan Yesus Kristus',
                '27-05' => 'Iduladha',
                '31-05' => 'Hari Waisak',
                '16-06' => 'Tahun Baru Islam',
                '25-08' => 'Maulid Nabi Muhammad SAW',
            ];
        }

        $key = $date->format('d-m');

        if (isset($tanggalMerah[$key])) {
            return [
                'label' => 'Hari libur: ' . $tanggalMerah[$key],
                'source' => 'Kalender libur nasional',
            ];
        }

        return null;
    }
}

<?php

namespace Tests\Unit;

use App\Models\MasterJamKerja;
use PHPUnit\Framework\TestCase;

class MasterJamKerjaAttendanceWindowTest extends TestCase
{
    public function test_it_uses_the_configured_attendance_window_values(): void
    {
        $jamKerja = new MasterJamKerja([
            'jam_masuk' => '07:30:00',
            'jam_pulang' => '16:30:00',
            'toleransi_menit' => 15,
            'jam_buka_absen_masuk' => '06:20:00',
            'batas_absen_masuk' => '07:55:00',
            'jam_buka_absen_pulang' => '16:15:00',
            'jam_tutup_absen_pulang' => '19:00:00',
        ]);

        [$bukaMasuk, $batasMasuk] = $jamKerja->rentangAbsenMasuk();
        [$bukaPulang, $tutupPulang] = $jamKerja->rentangAbsenPulang();

        $this->assertSame('06:20', $bukaMasuk->format('H:i'));
        $this->assertSame('07:55', $batasMasuk->format('H:i'));
        $this->assertSame('16:15', $bukaPulang->format('H:i'));
        $this->assertSame('19:00', $tutupPulang->format('H:i'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\Jabatan;
use App\Models\MasterKpi;
use App\Models\MasterTemplateLaporan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterTemplateLaporanTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_report_template_and_kpi_to_the_same_job(): void
    {
        $division = Divisi::create(['nama_divisi' => 'Seksi Teknik']);
        $technicianJob = Jabatan::create([
            'divisi_id' => $division->id,
            'nama_jabatan' => 'Teknisi',
            'grade' => 'Grade 1',
            'base_tukin' => 1000000,
        ]);
        $supervisorJob = Jabatan::create([
            'divisi_id' => $division->id,
            'nama_jabatan' => 'Pengawas Teknik',
            'grade' => 'Grade 2',
            'base_tukin' => 1500000,
        ]);
        $technicianKpi = MasterKpi::create([
            'jabatan_id' => $technicianJob->id,
            'indikator' => 'Perbaikan jaringan',
            'bobot' => 30,
        ]);
        $supervisorKpi = MasterKpi::create([
            'jabatan_id' => $supervisorJob->id,
            'indikator' => 'Pengawasan pekerjaan',
            'bobot' => 30,
        ]);
        $master = User::create([
            'name' => 'Admin Master',
            'email' => 'master.template@example.test',
            'password' => bcrypt('password'),
            'role' => 'master',
        ]);

        $this->actingAs($master)
            ->get(route('master.template-laporan.create'))
            ->assertOk()
            ->assertSee('Teknisi (Seksi Teknik)')
            ->assertSee('Pengawas Teknik (Seksi Teknik)')
            ->assertSee('data-jabatan-id="'.$technicianJob->id.'"', false)
            ->assertSee('data-jabatan-id="'.$supervisorJob->id.'"', false)
            ->assertSee('Perbaikan jaringan')
            ->assertSee('Pengawasan pekerjaan')
            ->assertSee('Belum ada KPI untuk jabatan ini.')
            ->assertSee('data-kpi-empty-state', false);

        $this->get(route('master.master-kpi.create', ['jabatan_id' => $technicianJob->id]))
            ->assertOk()
            ->assertSeeInOrder([
                'value="'.$technicianJob->id.'"',
                'selected>',
                'Teknisi (Seksi Teknik)',
            ], false);

        $this->post(route('master.template-laporan.store'), [
            'jabatan_id' => $technicianJob->id,
            'nama_field' => 'Jumlah perbaikan',
            'tipe_field' => 'angka',
            'satuan' => 'titik',
            'terhubung_ke_kpi_id' => $supervisorKpi->id,
        ])->assertSessionHasErrors('terhubung_ke_kpi_id');

        $this->post(route('master.template-laporan.store'), [
            'jabatan_id' => $technicianJob->id,
            'nama_field' => 'Jumlah perbaikan',
            'tipe_field' => 'angka',
            'satuan' => 'titik',
            'terhubung_ke_kpi_id' => $technicianKpi->id,
        ])->assertRedirect(route('master.template-laporan.index'));

        $this->assertDatabaseHas('master_template_laporans', [
            'nama_field' => 'Jumlah perbaikan',
            'divisi_id' => $division->id,
            'jabatan_id' => $technicianJob->id,
            'terhubung_ke_kpi_id' => $technicianKpi->id,
        ]);

        $template = MasterTemplateLaporan::firstOrFail();
        $this->get(route('master.template-laporan.edit', $template))
            ->assertOk()
            ->assertSee('Perbaikan jaringan')
            ->assertSee('Teknisi (Seksi Teknik)');

        $this->assertSame(1, MasterTemplateLaporan::count());
    }
}

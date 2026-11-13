<?php

use App\Http\Controllers\Atasan\AbsensiAtasanController;
use App\Http\Controllers\Atasan\ApprovalLaporanController;
use App\Http\Controllers\Atasan\DashboardAtasanController;
use App\Http\Controllers\Atasan\HasilPenilaianController;
use App\Http\Controllers\Atasan\KompetensiController;
use App\Http\Controllers\Atasan\PegawaiAtasanController;
use App\Http\Controllers\Atasan\PenilaianKpiController;
use App\Http\Controllers\Auth\AtasanLoginController;
use App\Http\Controllers\Auth\AtasanRegistrationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DirekturLoginController;
use App\Http\Controllers\Auth\KepalaBagianLoginController;
use App\Http\Controllers\Auth\MasterLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\KepalaBagian\DashboardKepalaBagianController;
use App\Http\Controllers\KepalaBagian\KompetensiAtasanController;
use App\Http\Controllers\KepalaBagian\PegawaiTimController;
use App\Http\Controllers\KepalaBagian\PenilaianAtasanController;
use App\Http\Controllers\KepalaBagian\RekapDataController;
use App\Http\Controllers\Direktur\DashboardDirekturController;
use App\Http\Controllers\Direktur\PenilaianKepalaBagianController;
use App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\Master\DivisiController;
use App\Http\Controllers\Master\JabatanController;
use App\Http\Controllers\Master\JamKerjaController;
use App\Http\Controllers\Master\JenisPotonganController;
use App\Http\Controllers\Master\MasterKompetensiController;
use App\Http\Controllers\Master\MasterKpiController;
use App\Http\Controllers\Master\PegawaiController;
use App\Http\Controllers\Master\PeriodePenilaianController;
use App\Http\Controllers\Master\TemplateLaporanController;
use App\Http\Controllers\Pegawai\AbsensiController;
use App\Http\Controllers\Pegawai\DashboardPegawaiController;
use App\Http\Controllers\Pegawai\LaporanHarianController;
use App\Http\Controllers\Pegawai\ProfileController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/', function () {
//     if (! auth()->check()) {
//         return redirect()->route('login');
//     }

//     return match (auth()->user()->role) {
//         'master' => redirect()->route('master.dashboard'),
//         'atasan' => redirect()->route('atasan.dashboard'),
//         'pegawai' => redirect()->route('pegawai.dashboard'),
//         default => redirect()->route('login'),
//     };
// });

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/atasan/login', [AtasanLoginController::class, 'create'])->name('atasan.login');
Route::post('/atasan/login', [AtasanLoginController::class, 'store'])->name('atasan.login.store');

Route::get('/kepala-bagian/login', [KepalaBagianLoginController::class, 'create'])->name('kepala_bagian.login');
Route::post('/kepala-bagian/login', [KepalaBagianLoginController::class, 'store'])->name('kepala_bagian.login.store');

Route::get('/direktur/login', [DirekturLoginController::class, 'create'])->name('direktur.login');
Route::post('/direktur/login', [DirekturLoginController::class, 'store'])->name('direktur.login.store');

Route::get('/master/login', [MasterLoginController::class, 'create'])->name('master.login');
Route::post('/master/login', [MasterLoginController::class, 'store'])->name('master.login.store');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

Route::get('/atasan/register', [AtasanRegistrationController::class, 'create'])->name('atasan.register');
Route::post('/atasan/register', [AtasanRegistrationController::class, 'store'])->name('atasan.register.store');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'role:master'])
    ->prefix('master')
    ->name('master.')
    ->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('divisi', DivisiController::class);
        Route::resource('jabatan', JabatanController::class);
        Route::resource('pegawai', PegawaiController::class);
        Route::resource('periode-penilaian', PeriodePenilaianController::class)->parameters(['periode-penilaian' => 'periodePenilaian']);
        Route::resource('master-kpi', MasterKpiController::class)->parameters(['master-kpi' => 'masterKpi']);
        Route::resource('master-kompetensi', MasterKompetensiController::class)->parameters(['master-kompetensi' => 'masterKompetensi']);
        Route::resource('template-laporan', TemplateLaporanController::class)->parameters(['template-laporan' => 'templateLaporan']);
        Route::resource('jam-kerja', JamKerjaController::class)->parameters(['jam-kerja' => 'jamKerja']);
        Route::resource('jenis-potongan', JenisPotonganController::class)->parameters(['jenis-potongan' => 'jenisPotongan']);
    });

Route::middleware(['auth', 'role:atasan'])
    ->prefix('atasan')
    ->name('atasan.')
    ->group(function () {
        Route::get('dashboard', [DashboardAtasanController::class, 'index'])->name('dashboard');
        Route::prefix('laporan-harian')->name('laporan-harian.')->group(function () {
            Route::get('/', [LaporanHarianController::class, 'index'])->name('index');
            Route::get('/create', [LaporanHarianController::class, 'create'])->name('create');
            Route::post('/', [LaporanHarianController::class, 'store'])->name('store');
            Route::get('/{laporanHarian}/image', [LaporanHarianController::class, 'image'])->name('image');
            Route::get('/{laporanHarian}', [LaporanHarianController::class, 'show'])->name('show');
            Route::get('/{laporanHarian}/edit', [LaporanHarianController::class, 'edit'])->name('edit');
            Route::put('/{laporanHarian}', [LaporanHarianController::class, 'update'])->name('update');
            Route::delete('/{laporanHarian}', [LaporanHarianController::class, 'destroy'])->name('destroy');
            Route::post('/{laporanHarian}/ajukan', [LaporanHarianController::class, 'ajukan'])->name('ajukan');
        });

        Route::get('pegawai', [PegawaiAtasanController::class, 'index'])->name('pegawai.index');
        Route::get('pegawai/{pegawai}', [PegawaiAtasanController::class, 'show'])->name('pegawai.show');

        Route::get('absensi', [AbsensiAtasanController::class, 'index'])->name('absensi.index');
        Route::get('absensi/{absensi}/foto', [AbsensiAtasanController::class, 'foto'])->name('absensi.foto');
        Route::post('absensi/{absensi}/verifikasi', [AbsensiAtasanController::class, 'verifikasi'])->name('absensi.verifikasi');
        Route::get('absensi/{absensi}', [AbsensiAtasanController::class, 'show'])->name('absensi.show');

        Route::prefix('absensi-saya')->name('absensi-saya.')->group(function () {
            Route::get('/', [AbsensiController::class, 'index'])->name('index');
            Route::post('/', [AbsensiController::class, 'store'])->name('store');
            Route::get('/{absensi}', [AbsensiController::class, 'show'])->name('show');
        });

        Route::prefix('kompetensi')->name('kompetensi.')->group(function () {
            Route::get('/', [KompetensiController::class, 'index'])->name('index');
            Route::get('/{pegawai}/edit', [KompetensiController::class, 'edit'])->name('edit');
            Route::put('/{pegawai}', [KompetensiController::class, 'update'])->name('update');
        });

        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [ApprovalLaporanController::class, 'index'])->name('index');
            Route::get('/{laporanHarian}/image', [ApprovalLaporanController::class, 'image'])->name('image');
            Route::get('/{laporanHarian}', [ApprovalLaporanController::class, 'show'])->name('show');
            Route::post('/{laporanHarian}/approve', [ApprovalLaporanController::class, 'approve'])->name('approve');
            Route::post('/{laporanHarian}/reject', [ApprovalLaporanController::class, 'reject'])->name('reject');
        });

        Route::prefix('penilaian-kpi')->name('penilaian-kpi.')->group(function () {
            Route::get('/', [PenilaianKpiController::class, 'index'])->name('index');
            Route::get('/{pegawai}', [PenilaianKpiController::class, 'show'])->name('show');
            Route::get('/{pegawai}/edit', [PenilaianKpiController::class, 'edit'])->name('edit');
            Route::get('/{pegawai}/nilai', [PenilaianKpiController::class, 'create'])->name('create');
            Route::post('/{pegawai}', [PenilaianKpiController::class, 'store'])->name('store');
            Route::put('/{pegawai}', [PenilaianKpiController::class, 'update'])->name('update');
        });

        Route::prefix('hasil-penilaian')->name('hasil-penilaian.')->group(function () {
            Route::get('/', [HasilPenilaianController::class, 'index'])->name('index');
            Route::get('/{pegawai}', [HasilPenilaianController::class, 'show'])->name('show');
        });
    });

Route::middleware(['auth', 'role:kepala_bagian'])
    ->prefix('kepala-bagian')
    ->name('kepala_bagian.')
    ->group(function () {
        Route::get('dashboard', [DashboardKepalaBagianController::class, 'index'])->name('dashboard');
        Route::get('pegawai', [PegawaiTimController::class, 'index'])->name('pegawai.index');
        Route::get('absensi', [AbsensiAtasanController::class, 'index'])->name('absensi.index');
        Route::get('rekap-data/pdf', [RekapDataController::class, 'downloadPdf'])->name('rekap-data.pdf');
        Route::get('rekap-data', [RekapDataController::class, 'index'])->name('rekap-data.index');
        Route::get('absensi/{absensi}/foto', [AbsensiAtasanController::class, 'foto'])->name('absensi.foto');
        Route::post('absensi/{absensi}/verifikasi', [AbsensiAtasanController::class, 'verifikasi'])->name('absensi.verifikasi');
        Route::get('absensi/{absensi}', [AbsensiAtasanController::class, 'show'])->name('absensi.show');

        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [ApprovalLaporanController::class, 'index'])->name('index');
            Route::get('/{laporanHarian}/image', [ApprovalLaporanController::class, 'image'])->name('image');
            Route::get('/{laporanHarian}', [ApprovalLaporanController::class, 'show'])->name('show');
            Route::post('/{laporanHarian}/approve', [ApprovalLaporanController::class, 'approve'])->name('approve');
            Route::post('/{laporanHarian}/reject', [ApprovalLaporanController::class, 'reject'])->name('reject');
        });

        Route::prefix('penilaian-atasan')->name('penilaian-atasan.')->group(function () {
            Route::get('/{pegawai}/kompetensi/edit', [KompetensiAtasanController::class, 'edit'])->name('kompetensi.edit');
            Route::put('/{pegawai}/kompetensi', [KompetensiAtasanController::class, 'update'])->name('kompetensi.update');
            Route::get('/', [PenilaianAtasanController::class, 'index'])->name('index');
            Route::get('/{pegawai}', [PenilaianAtasanController::class, 'show'])->name('show');
            Route::get('/{pegawai}/nilai', [PenilaianAtasanController::class, 'create'])->name('create');
            Route::post('/{pegawai}', [PenilaianAtasanController::class, 'store'])->name('store');
        });
    });

Route::middleware(['auth', 'role:direktur'])
    ->prefix('direktur')
    ->name('direktur.')
    ->group(function () {
        Route::get('dashboard', [DashboardDirekturController::class, 'index'])->name('dashboard');
        Route::get('rekap-data/pdf', [RekapDataController::class, 'downloadPdf'])->name('rekap-data.pdf');
        Route::get('rekap-data', [RekapDataController::class, 'index'])->name('rekap-data.index');

        Route::prefix('penilaian-kepala-bagian')->name('penilaian-kepala-bagian.')->group(function () {
            Route::get('/', [PenilaianKepalaBagianController::class, 'index'])->name('index');
            Route::get('/{pegawai}/kompetensi/edit', [PenilaianKepalaBagianController::class, 'editKompetensi'])->name('kompetensi.edit');
            Route::put('/{pegawai}/kompetensi', [PenilaianKepalaBagianController::class, 'updateKompetensi'])->name('kompetensi.update');
            Route::get('/{pegawai}', [PenilaianKepalaBagianController::class, 'show'])->name('show');
            Route::get('/{pegawai}/nilai', [PenilaianKepalaBagianController::class, 'create'])->name('create');
            Route::post('/{pegawai}', [PenilaianKepalaBagianController::class, 'store'])->name('store');
            Route::get('/{pegawai}/edit', [PenilaianKepalaBagianController::class, 'edit'])->name('edit');
        });
    });


Route::middleware(['auth', 'role:pegawai'])
    ->prefix('pegawai')
    ->name('pegawai.')
    ->group(function () {
        Route::get('dashboard', [DashboardPegawaiController::class, 'index'])->name('dashboard');

        Route::prefix('laporan-harian')->name('laporan-harian.')->group(function () {
            Route::get('/', [LaporanHarianController::class, 'index'])->name('index');
            Route::get('/create', [LaporanHarianController::class, 'create'])->name('create');
            Route::post('/', [LaporanHarianController::class, 'store'])->name('store');
            Route::get('/{laporanHarian}/image', [LaporanHarianController::class, 'image'])->name('image');
            Route::get('/{laporanHarian}', [LaporanHarianController::class, 'show'])->name('show');
            Route::get('/{laporanHarian}/edit', [LaporanHarianController::class, 'edit'])->name('edit');
            Route::put('/{laporanHarian}', [LaporanHarianController::class, 'update'])->name('update');
            Route::delete('/{laporanHarian}', [LaporanHarianController::class, 'destroy'])->name('destroy');
            Route::post('/{laporanHarian}/ajukan', [LaporanHarianController::class, 'ajukan'])->name('ajukan');
        });

        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/', [AbsensiController::class, 'index'])->name('index');
            Route::post('/', [AbsensiController::class, 'store'])->name('store');
            Route::get('/{absensi}', [AbsensiController::class, 'show'])->name('show');
        });

        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
        });
    });

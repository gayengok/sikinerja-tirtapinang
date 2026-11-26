@php
    $rentangFields = [
        [
            'name' => 'jam_buka_absen_masuk',
            'label' => 'Jam Buka Absen Masuk',
            'description' => 'Waktu mulai pegawai dapat melakukan absen masuk.',
        ],
        [
            'name' => 'batas_absen_masuk',
            'label' => 'Batas Absen Masuk',
            'description' => 'Waktu terakhir pada rentang absen masuk.',
        ],
        [
            'name' => 'jam_buka_absen_pulang',
            'label' => 'Jam Buka Absen Pulang',
            'description' => 'Waktu mulai pegawai dapat melakukan absen pulang.',
        ],
        [
            'name' => 'jam_tutup_absen_pulang',
            'label' => 'Jam Tutup Absen Pulang',
            'description' => 'Waktu terakhir pada rentang absen pulang.',
        ],
    ];
@endphp

<section class="master-jam-window-settings" aria-labelledby="masterJamWindowSettingsTitle">
    <header>
        <h2 id="masterJamWindowSettingsTitle">Rentang Absen</h2>
        <p>Atur jadwal jam buka dan batas absen secara terpisah untuk setiap hari dan jabatan.</p>
    </header>

    <div class="master-jam-window-settings-grid">
        @foreach ($rentangFields as $field)
            @php
                $storedValue = isset($jamKerja) && filled($jamKerja->{$field['name']})
                    ? \Carbon\Carbon::parse($jamKerja->{$field['name']})->format('H:i')
                    : '';
            @endphp
            <div class="master-jam-window-settings-field">
                <label for="{{ $field['name'] }}">{{ $field['label'] }}</label>
                <input
                    type="time"
                    id="{{ $field['name'] }}"
                    name="{{ $field['name'] }}"
                    value="{{ old($field['name'], $storedValue) }}"
                    class="@error($field['name']) is-invalid @enderror"
                    required
                >
                <small>{{ $field['description'] }}</small>
                @error($field['name'])
                    <span class="master-jam-window-settings-error">{{ $message }}</span>
                @enderror
            </div>
        @endforeach
    </div>
</section>

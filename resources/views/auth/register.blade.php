<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — {{ config('app.name', 'SIKINERJA') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Work+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        @include('auth._theme') * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: var(--foam);
            color: var(--ink);
            font-family: 'Work Sans', sans-serif;
        }

        ::selection {
            background: var(--sand);
            color: var(--deep);
        }

        .register-stage {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            min-height: 100vh;
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            padding: 48px clamp(30px, 5vw, 72px);
            background: #0875ad;
            color: var(--foam);
        }

        .brand-panel::before {
            position: absolute;
            inset: 0;
            content: "";
            background: radial-gradient(circle at 20% 15%, rgba(255, 255, 255, .08), transparent 38%),
                radial-gradient(circle at 85% 68%, rgba(18, 181, 170, .12), transparent 42%);
        }

        .brand-content,
        .brand-footer {
            position: relative;
            z-index: 1;
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            display: grid;
            width: 82px;
            height: 50px;
            place-items: center;
            border-radius: 10px;
            padding: 5px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 6px 18px rgba(0, 0, 0, .14);
        }

        .brand-mark svg {
            width: 26px;
            height: 26px;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 6px;
        }

        .brand-name .eyebrow {
            display: block;
            color: var(--sand);
            font: 700 10px 'JetBrains Mono', monospace;
            letter-spacing: .1em;
        }

        .brand-name strong {
            display: block;
            color: #fff;
            font: 600 18px 'Space Grotesk', sans-serif;
        }

        .brand-intro {
            max-width: 390px;
            margin: 92px 0 auto;
        }

        .brand-badge,
        .step-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border: 1px solid rgba(18, 181, 170, .42);
            border-radius: 999px;
            color: var(--tirta-lt);
            font-size: 11px;
        }

        .brand-intro h1 {
            margin-top: 22px;
            color: #fff;
            font: 700 clamp(30px, 3.3vw, 44px)/1.08 'Space Grotesk', sans-serif;
        }

        .brand-intro p {
            max-width: 39ch;
            margin-top: 18px;
            color: rgba(234, 246, 244, .72);
            font-size: 14px;
            line-height: 1.65;
        }

        .steps {
            display: grid;
            gap: 12px;
            margin-top: 42px;
        }

        .side-step {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 12px;
            border: 1px solid rgba(126, 149, 184, .13);
            border-radius: 10px;
            color: #93a1b8;
            font-size: 12px;
        }

        .side-step.active {
            border-color: rgba(18, 181, 170, .4);
            background: rgba(18, 181, 170, .09);
            color: #fff;
        }

        .side-step.done {
            border-color: rgba(18, 181, 170, .28);
            background: rgba(18, 181, 170, .05);
        }

        .step-number {
            display: grid;
            width: 25px;
            height: 25px;
            place-items: center;
            border-radius: 6px;
            background: rgba(126, 149, 184, .12);
            font: 12px 'JetBrains Mono', monospace;
        }

        .side-step.active .step-number,
        .side-step.done .step-number {
            background: var(--tirta);
            color: #062c35;
        }

        .side-step small {
            display: block;
            margin-bottom: 2px;
            color: var(--tirta-lt);
            font: 10px 'JetBrains Mono', monospace;
            text-transform: uppercase;
        }

        .brand-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding-top: 25px;
            border-top: 1px solid rgba(126, 149, 184, .16);
            color: rgba(234, 246, 244, .6);
            font-size: 11px;
        }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 42px clamp(24px, 6vw, 92px);
            background: #07141a;
        }

        .register-card {
            width: min(100%, 650px);
        }

        .register-head {
            position: relative;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line);
        }

        .register-head::after {
            position: absolute;
            bottom: -2px;
            left: 0;
            width: var(--progress, 33.33%);
            height: 3px;
            border-radius: 4px;
            background: var(--tirta);
            content: "";
            transition: width .2s ease;
        }

        .register-head h2 {
            margin-top: 4px;
            color: #f4f7fb;
            font: 600 23px 'Space Grotesk', sans-serif;
        }

        .kicker {
            color: var(--tirta);
            font: 10px 'JetBrains Mono', monospace;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .step-badge {
            position: absolute;
            top: 1px;
            right: 0;
            padding: 5px 10px;
            font-size: 10px;
        }

        .form-step {
            display: none;
            padding-top: 30px;
        }

        .form-step.active {
            display: block;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 5px;
            color: #f4f7fb;
            font: 600 16px 'Space Grotesk', sans-serif;
        }

        .section-heading i {
            color: var(--tirta);
            font-style: normal;
        }

        .section-copy {
            margin-bottom: 24px;
            color: var(--ink-soft);
            font-size: 11px;
        }

        .field {
            margin-bottom: 17px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            color: #d5dce7;
            font-size: 11px;
        }

        .field label em {
            color: var(--tirta);
            font-style: normal;
        }

        .input-wrap {
            position: relative;
        }

        input,
        select {
            width: 100%;
            min-height: 45px;
            padding: 12px 14px;
            border: 1px solid rgba(126, 149, 184, .28);
            border-radius: 10px;
            outline: none;
            background: rgba(3, 12, 29, .62);
            color: #f4f7fb;
            font: 13px 'Work Sans', sans-serif;
        }

        select option {
            background: #0c172d;
            color: #f4f7fb;
        }

        input:focus,
        select:focus {
            border-color: var(--tirta);
            box-shadow: 0 0 0 3px rgba(18, 181, 170, .12);
        }

        input::placeholder {
            color: #718098;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 14px;
            width: 16px;
            color: var(--ink-soft);
            transform: translateY(-50%);
        }

        .input-icon+input {
            padding-left: 42px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 12px;
            border: 0;
            background: transparent;
            color: var(--ink-soft);
            cursor: pointer;
            transform: translateY(-50%);
        }

        .password-toggle:hover {
            color: var(--tirta-lt);
        }

        .help {
            display: block;
            margin-top: 5px;
            color: var(--ink-soft);
            font-size: 10px;
        }

        .error {
            display: block;
            margin-top: 5px;
            color: #ef8b78;
            font-size: 11px;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 28px;
        }

        .actions button,
        .actions a {
            flex: 1;
            min-height: 43px;
            border-radius: 10px;
            font: 600 12px 'Space Grotesk', sans-serif;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        .actions button {
            border: 0;
            background: linear-gradient(135deg, var(--tirta-lt), var(--tirta));
            color: #062c35;
            box-shadow: 0 8px 20px rgba(18, 181, 170, .18);
        }

        .actions a {
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            color: #d5dce7;
            background: rgba(3, 12, 29, .45);
        }

        .actions .primary {
            flex: 1.35;
        }

        .check-card {
            margin-top: 20px;
            padding: 13px;
            border: 1px solid rgba(18, 181, 170, .25);
            border-radius: 10px;
            background: rgba(18, 181, 170, .06);
        }

        .check-card label {
            display: flex;
            gap: 9px;
            color: #b8c3d3;
            font-size: 11px;
            line-height: 1.5;
        }

        .check-card input {
            width: 14px;
            min-height: 14px;
            flex: 0 0 14px;
            accent-color: var(--tirta);
        }

        .login-link {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--line);
            text-align: center;
            color: var(--ink-soft);
            font-size: 11px;
        }

        .login-link a {
            color: var(--tirta-lt);
            text-decoration: none;
        }

        @media (max-width: 920px) {
            .register-stage {
                display: block;
            }

            .brand-panel {
                min-height: 430px;
                padding: 32px 24px 150px;
            }

            .brand-intro {
                margin-top: 48px;
            }

            .brand-intro h1 {
                font-size: 32px;
            }

            .brand-footer {
                position: absolute;
                right: 24px;
                bottom: 28px;
                left: 24px;
            }

            .form-panel {
                margin-top: -100px;
                position: relative;
                padding: 28px 18px 38px;
                border-radius: 24px 24px 0 0;
            }
        }

        @media (max-width: 520px) {
            .brand-panel {
                min-height: 390px;
            }

            .brand-intro p {
                font-size: 13px;
            }

            .steps {
                margin-top: 25px;
            }

            .form-panel {
                padding-inline: 14px;
            }

            .register-head h2 {
                font-size: 20px;
            }

            .step-badge {
                position: static;
                display: inline-flex;
                margin-top: 12px;
            }

            .actions {
                flex-direction: column-reverse;
            }

            .actions button,
            .actions a {
                flex: none;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    @php
        $initialStep = $errors->hasAny(['jabatan_id', 'atasan_id'])
            ? 2
            : ($errors->hasAny(['tanggal_masuk', 'status'])
                ? 3
                : 1);
    @endphp
    <div class="register-stage" data-initial-step="{{ $initialStep }}">
        <aside class="brand-panel">
            <div class="brand-content">
                <div class="brand-top">
                    <div class="brand-mark" aria-hidden="true">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo SIKINERJA">
                    </div>
                    <div class="brand-name"><span class="eyebrow">PERUMDAM TIRTA PINANG</span><strong>SIKINERJA</strong>
                    </div>
                </div>

                <div class="brand-intro">
                    <div class="steps">
                        <div class="side-step active" data-side-step="1"><span
                                class="step-number">1</span><span><small>Langkah 1</small>Data Akun &amp;
                                Kredensial</span></div>
                        <div class="side-step" data-side-step="2"><span class="step-number">2</span><span><small>Langkah
                                    2</small>Penempatan &amp; Jabatan</span></div>
                        <div class="side-step" data-side-step="3"><span class="step-number">3</span><span><small>Langkah
                                    3</small>Status &amp; Verifikasi</span></div>
                    </div>
                </div>
            </div>
        </aside>

        <main class="form-panel">
            <div class="register-card">
                <header class="register-head">
                    <span class="kicker">Pendaftaran Akun Pegawai</span>
                    <h2 id="step-title">Kredensial &amp; Akun</h2>
                    <span class="step-badge">Langkah <b id="step-number">1</b> dari 3</span>
                </header>
                <form method="POST" action="{{ route('register') }}" id="register-form">
                    @csrf
                    <section class="form-step active" data-step="1">
                        <h3 class="section-heading"><i>♙</i> Identitas Dasar</h3>
                        <p class="section-copy">Pastikan nama lengkap sesuai dengan SK pengangkatan pegawai aktif.</p>
                        <div class="field"><label for="nama">Nama Lengkap <em>*</em></label>
                            <div class="input-wrap"><span class="input-icon">◎</span><input id="nama"
                                    name="nama" type="text" value="{{ old('nama') }}"
                                    placeholder="Contoh: Budi Santoso, S.T." required autofocus></div>
                            @error('nama')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="field"><label for="email">Alamat Email (Resmi / Pribadi)</label>
                            <div class="input-wrap"><span class="input-icon">@</span><input id="email"
                                    name="email" type="email" value="{{ old('email') }}"
                                    placeholder="pegawai@tirtapinang.co.id" required autocomplete="email"></div>
                            @error('email')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="field"><label for="password">Password Awal <em>*</em></label>
                            <div class="input-wrap"><span class="input-icon">♙</span><input id="password"
                                    name="password" type="password" placeholder="Minimal 6 karakter" required
                                    autocomplete="new-password"><button type="button" class="password-toggle"
                                    data-target="password">◉</button></div><small class="help">ⓘ Minimal 6 karakter
                                kombinasi huruf dan angka.</small>
                            @error('password')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="field"><label for="password_confirmation">Konfirmasi Kata Sandi <em>*</em></label>
                            <div class="input-wrap"><span class="input-icon">♡</span><input id="password_confirmation"
                                    name="password_confirmation" type="password" required
                                    autocomplete="new-password"><button type="button" class="password-toggle"
                                    data-target="password_confirmation">◉</button></div>
                        </div>
                        <div class="actions"><span></span><button type="button" class="primary next-step">Lanjut ke
                                Penempatan&nbsp; →</button></div>
                    </section>
                    <section class="form-step" data-step="2">
                        <h3 class="section-heading"><i>▦</i> Penempatan Kerja</h3>
                        <p class="section-copy">Pilih divisi dan jabatan tugas sesuai surat keputusan pengangkatan
                            resmi.</p>
                        <div class="field"><label for="divisi_filter">Divisi<em>*</em></label><select
                                id="divisi_filter" required>
                                <option value="">Pilih Divisi...</option>
                                @foreach ($divisis as $divisi)
                                    <option value="{{ $divisi->id }}">{{ $divisi->nama_divisi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label for="jabatan_id">Jabatan<em>*</em></label>
                            <select name="jabatan_id" id="jabatan_id" required>
                                <option value="">Pilih Jabatan...</option>
                                @foreach ($jabatans as $jabatan)
                                    <option value="{{ $jabatan->id }}" data-divisi="{{ $jabatan->divisi_id }}"
                                        {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                        {{ $jabatan->nama_jabatan }} ({{ $jabatan->divisi->nama_divisi }})</option>
                                @endforeach
                            </select>
                            @error('jabatan_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="field"><label for="atasan_id">Atasan Langsung <em>*</em></label><select
                                name="atasan_id" id="atasan_id" required>
                                <option value="">Pilih Atasan...</option>
                                @foreach ($atasans as $atasan)
                                    <option value="{{ $atasan->id }}"
                                        data-divisi="{{ $atasan->jabatan->divisi_id ?? '' }}"
                                        {{ old('atasan_id') == $atasan->id ? 'selected' : '' }}>{{ $atasan->nama }}
                                        ({{ $atasan->jabatan->nama_jabatan ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="help">ⓘ Pilih atasan langsung aktif yang berasal dari divisi yang
                                sama.</small>
                            @error('atasan_id')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="actions"><button type="button" class="previous-step">←
                                &nbsp;Kembali</button><button type="button" class="primary next-step">Lanjut ke
                                Verifikasi &nbsp;→</button></div>
                    </section>
                    <section class="form-step" data-step="3">
                        <h3 class="section-heading"><i>▣</i> Status Kepegawaian &amp; Tanggal Masuk</h3>
                        <p class="section-copy">Lengkapi tanggal mulai bertugas dan konfirmasi status kepegawaian Anda.
                        </p>
                        <div class="field"><label for="tanggal_masuk">Tanggal Masuk <em>*</em></label><input
                                id="tanggal_masuk" name="tanggal_masuk" type="date"
                                value="{{ old('tanggal_masuk') }}" required>
                            @error('tanggal_masuk')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="field"><label for="status">Status Akun <em>*</em></label><select
                                name="status" id="status" required>
                                <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                            @error('status')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="check-card"><label><input type="checkbox" id="agreement" required><span><b
                                        style="color:var(--tirta)">♧ Pernyataan Keabsahan Data</b><br>Saya menyatakan
                                    bahwa data yang saya masukkan adalah valid dan sesuai dengan SK Pengangkatan Pegawai
                                    Perumdam Tirta Pinang.</span></label></div>
                        <div class="actions"><button type="button" class="previous-step">←
                                &nbsp;Kembali</button><button type="submit" class="primary">Daftar sebagai Pegawai
                                &nbsp;✓</button></div>
                    </section>
                </form>
                <p class="login-link">Sudah punya akun? <a href="{{ route('login') }}">Masuk ke SIKINERJA →</a></p>
            </div>
        </main>
    </div>
    <script>
        const stage = document.querySelector('.register-stage');
        const steps = [...document.querySelectorAll('.form-step')];
        const sideSteps = [...document.querySelectorAll('[data-side-step]')];
        const title = document.getElementById('step-title');
        const stepNumber = document.getElementById('step-number');
        const titles = ['Kredensial & Akun', 'Penempatan & Jabatan', 'Status & Verifikasi Akhir'];
        let currentStep = Number(stage.dataset.initialStep || 1);

        function filterOptions(select, division) {
            [...select.options].forEach(option => {
                option.hidden = option.value !== '' && option.dataset.divisi !== division;
            });
            if (select.selectedOptions[0]?.hidden) select.value = '';
        }

        function showStep(step) {
            currentStep = step;
            steps.forEach(item => item.classList.toggle('active', Number(item.dataset.step) === step));
            sideSteps.forEach(item => {
                const number = Number(item.dataset.sideStep);
                item.classList.toggle('active', number === step);
                item.classList.toggle('done', number < step);
                item.querySelector('.step-number').textContent = number < step ? '✓' : number;
            });
            title.textContent = titles[step - 1];
            stepNumber.textContent = step;
            document.querySelector('.register-head').style.setProperty('--progress', `${step * 33.33}%`);
            document.querySelector('.register-head').style.setProperty('border-bottom-color', 'var(--line)');
            document.querySelector('.register-head').classList.toggle('progress-wide', step === 3);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function validateStep() {
            const fields = [...steps[currentStep - 1].querySelectorAll('input, select')];
            return fields.every(field => field.reportValidity());
        }
        document.querySelectorAll('.next-step').forEach(button => button.addEventListener('click', () => {
            if (validateStep()) showStep(Math.min(3, currentStep + 1));
        }));
        document.querySelectorAll('.previous-step').forEach(button => {
            button.addEventListener('click', () => {
                showStep(Math.max(1, currentStep - 1));
            });
        });

        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.target);
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });

        const division = document.getElementById('divisi_filter');
        const position = document.getElementById('jabatan_id');
        const supervisor = document.getElementById('atasan_id');

        division.addEventListener('change', () => {
            filterOptions(position, division.value);
            filterOptions(supervisor, division.value);
        });

        position.addEventListener('change', () => {
            division.value = position.selectedOptions[0]?.dataset.divisi || '';
            filterOptions(supervisor, division.value);
        });

        if (position.value) {
            position.dispatchEvent(new Event('change'));
        }

        showStep(currentStep);
    </script>
</body>

</html>

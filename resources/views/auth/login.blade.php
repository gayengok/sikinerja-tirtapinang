@php
    $loginRoute = $loginRoute ?? 'login';
    $loginTitle = $loginTitle ?? 'Masuk ke Akun';
    $loginDescription = $loginDescription ?? 'Gunakan kredensial akun kepegawaian Anda.';
    $loginButton = $loginButton ?? 'Masuk ke SIKINERJA';
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $loginTitle }} — {{ config('app.name', 'SIKINERJA') }}</title>
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
            height: 100%;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            color: var(--ink);
            background: var(--foam);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        ::selection {
            background: var(--sand);
            color: var(--deep);
        }

        .stage {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            min-height: 100vh;
        }

        @media (max-width: 920px) {
            .stage {
                grid-template-columns: 1fr;
            }

            .brandside {
                min-height: 280px;
            }
        }

        /* ============ LEFT: BRAND / WAVE PANEL ============ */
        .brandside {
            position: relative;
            background: #0875ad;
            color: var(--foam);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(28px, 4vw, 64px);
        }

        .brandside::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 18% 20%, rgba(255, 255, 255, 0.06) 0, transparent 40%),
                radial-gradient(circle at 85% 75%, rgba(242, 166, 90, 0.10) 0, transparent 45%);
            pointer-events: none;
        }

        .brand-top {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 82px;
            height: 50px;
            flex: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, .92);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
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

        .brand-name {
            line-height: 1.2;
        }

        .brand-name .eyebrow {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--sand);
            opacity: .9;
        }

        .brand-name strong {
            display: block;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .brand-mid {
            position: relative;
            z-index: 2;
            max-width: 460px;
            margin: auto 0;
            padding: 40px 0;
        }

        .brand-mid h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: clamp(28px, 3.4vw, 42px);
            line-height: 1.12;
            letter-spacing: -0.01em;
        }

        .brand-mid h1 em {
            font-style: normal;
            color: var(--sand);
        }

        .brand-mid p {
            margin-top: 16px;
            font-size: 15px;
            line-height: 1.65;
            color: rgba(234, 246, 244, 0.78);
            max-width: 40ch;
        }

        .brand-stats {
            position: relative;
            z-index: 2;
            display: flex;
            gap: 28px;
            margin-top: 34px;
            flex-wrap: wrap;
        }

        .brand-stats div {
            border-left: 2px solid rgba(242, 166, 90, 0.55);
            padding-left: 12px;
        }

        .brand-stats .num {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
        }

        .brand-stats .lbl {
            font-size: 11.5px;
            color: rgba(234, 246, 244, 0.65);
            margin-top: 2px;
        }

        .brand-bottom {
            position: relative;
            z-index: 2;
            font-size: 12px;
            color: rgba(234, 246, 244, 0.55);
        }

        .waves {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 46%;
            z-index: 1;
            pointer-events: none;
        }

        .brand-photo {
            display: none;
        }

        .waves svg {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 100%;
        }

        .wave-a {
            opacity: .35;
            animation: drift 22s linear infinite;
        }

        .wave-b {
            opacity: .5;
            animation: drift 16s linear infinite reverse;
        }

        .wave-c {
            opacity: .85;
            animation: drift 26s linear infinite;
        }

        @keyframes drift {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .wave-a,
            .wave-b,
            .wave-c {
                animation: none;
            }
        }

        /* ============ RIGHT: FORM PANEL ============ */
        .formside {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(24px, 5vw, 64px);
            background:
                radial-gradient(circle at 100% 0%, rgba(29, 138, 130, 0.06), transparent 45%),
                var(--foam);
        }

        .card {
            width: 100%;
            max-width: 420px;
        }

        .card-head {
            margin-bottom: 28px;
        }

        .card-head .kicker {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--tirta);
        }

        .card-head h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 26px;
            font-weight: 700;
            margin-top: 6px;
            color: var(--deep);
        }

        .card-head p {
            margin-top: 8px;
            font-size: 13.5px;
            color: var(--ink-soft);
            line-height: 1.5;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--deep);
            letter-spacing: .01em;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px;
            width: 17px;
            height: 17px;
            stroke: var(--ink-soft);
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            font-family: 'Work Sans', sans-serif;
            font-size: 14.5px;
            padding: 12px 14px 12px 42px;
            border-radius: 10px;
            border: 1.5px solid var(--line);
            background: #fff;
            color: var(--ink);
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .input-wrap input::placeholder {
            color: #9FB4B6;
        }

        .input-wrap input:focus {
            border-color: var(--tirta);
            box-shadow: 0 0 0 4px rgba(29, 138, 130, 0.12);
        }

        .input-wrap.err input {
            border-color: var(--danger);
        }

        .input-wrap.err input:focus {
            box-shadow: 0 0 0 4px rgba(196, 67, 43, 0.12);
        }

        .toggle-eye {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-soft);
            border-radius: 6px;
        }

        .toggle-eye:hover {
            color: var(--tirta);
            background: rgba(29, 138, 130, 0.08);
        }

        .toggle-eye svg {
            width: 18px;
            height: 18px;
        }

        .field-msg {
            font-size: 12px;
            color: var(--danger);
            margin-top: 2px;
        }

        .row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--ink-soft);
        }

        .remember input {
            accent-color: var(--tirta);
            width: 15px;
            height: 15px;
        }

        .forgot {
            font-size: 13px;
            color: var(--tirta);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot:hover {
            text-decoration: underline;
        }

        .btn-submit {
            margin-top: 6px;
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #0875ad;
            color: #fff;
            font-family: 'Space Grotesk', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: .01em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: transform .12s ease, box-shadow .12s ease, opacity .12s ease;
            box-shadow: 0 8px 20px rgba(10, 61, 69, 0.28);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(10, 61, 69, 0.34);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit svg {
            width: 16px;
            height: 16px;
        }

        .register-prompt {
            margin-top: 18px;
            text-align: center;
            color: var(--ink-soft);
            font-size: 13px;
        }

        .register-prompt a {
            color: var(--tirta);
            font-weight: 600;
            text-decoration: none;
        }

        .register-prompt a:hover {
            text-decoration: underline;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 13px;
            border-radius: 10px;
            background: rgba(196, 67, 43, 0.08);
            border: 1px solid rgba(196, 67, 43, 0.25);
            color: #8C3521;
            font-size: 12.5px;
            line-height: 1.5;
            margin-bottom: 18px;
        }

        .alert svg {
            width: 16px;
            height: 16px;
            flex: none;
            margin-top: 1px;
        }

        .alert.success {
            background: rgba(29, 138, 130, 0.08);
            border-color: rgba(29, 138, 130, 0.28);
            color: var(--deep);
        }

        .alert .close-btn {
            margin-left: auto;
            background: none;
            border: none;
            cursor: pointer;
            color: inherit;
            opacity: .6;
            font-size: 15px;
            line-height: 1;
        }

        .alert .close-btn:hover {
            opacity: 1;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0 16px;
            color: #9FB4B6;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--line);
        }

        .footnote {
            text-align: center;
            font-size: 12.5px;
            color: var(--ink-soft);
            line-height: 1.6;
        }

        .footnote a {
            color: var(--tirta);
            font-weight: 600;
            text-decoration: none;
        }

        .footnote a:hover {
            text-decoration: underline;
        }

        .meta-line {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            color: #9FB4B6;
            letter-spacing: .02em;
        }

        @media (max-width: 920px) {

            html,
            body {
                min-height: 100%;
                height: auto;
            }

            body {
                display: block;
                background: #f4f8fb;
            }

            .stage {
                display: block;
                min-height: 100vh;
            }

            .brandside {
                min-height: 620px;
                padding: 44px 28px 210px;
                border-radius: 0 0 34px 34px;
            }

            .brand-top {
                align-items: flex-start;
                gap: 14px;
            }

            .brand-mark {
                width: 78px;
                height: 50px;
                border-radius: 10px;
            }

            .brand-mark svg {
                width: 30px;
                height: 30px;
            }

            .brand-mark img {
                width: 100%;
                height: 100%;
            }

            .brand-name .eyebrow {
                display: block;
                max-width: 260px;
                font-size: 10px;
                line-height: 1.4;
            }

            .brand-name strong {
                margin-top: 4px;
                font-size: 20px;
                line-height: 1.25;
            }

            .brand-mid {
                max-width: 350px;
                margin: 54px 0 0;
                padding: 0;
            }

            .brand-mid h1 {
                font-size: 36px;
                line-height: 1.12;
            }

            .brand-mid p {
                margin-top: 18px;
                font-size: 16px;
                line-height: 1.55;
            }

            .brand-stats,
            .brand-bottom {
                display: none;
            }

            .waves {
                height: 34%;
            }

            .formside {
                position: relative;
                z-index: 3;
                display: block;
                margin-top: -196px;
                padding: 0 18px 28px;
                background: transparent;
            }

            .card {
                max-width: 560px;
                margin: 0 auto;
                padding: 34px 22px 26px;
                background: #fff;
                border-radius: 28px;
                box-shadow: 0 14px 35px rgba(10, 61, 69, .13);
            }

            .card-head {
                margin-bottom: 28px;
            }

            .card-head .kicker {
                display: inline-block;
                padding: 8px 14px;
                background: var(--foam);
                border-radius: 999px;
                font-family: 'Work Sans', sans-serif;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .1em;
            }

            .card-head h2 {
                margin-top: 18px;
                font-size: 30px;
                line-height: 1.15;
            }

            .card-head p {
                margin-top: 12px;
                font-size: 16px;
                line-height: 1.55;
            }

            form {
                gap: 20px;
            }

            .field {
                gap: 8px;
            }

            .field label {
                font-size: 15px;
            }

            .input-wrap input {
                min-height: 62px;
                padding: 16px 14px 16px 48px;
                border-radius: 17px;
                background: #f5f8fb;
                font-size: 16px;
            }

            .input-wrap svg {
                left: 18px;
                width: 20px;
                height: 20px;
            }

            .toggle-eye {
                right: 14px;
                width: 34px;
                height: 34px;
            }

            .row-between {
                margin-top: 0;
            }

            .remember,
            .forgot {
                font-size: 15px;
            }

            .remember input {
                width: 20px;
                height: 20px;
            }

            .btn-submit {
                min-height: 62px;
                margin-top: 2px;
                border-radius: 17px;
                font-size: 18px;
            }

            .divider {
                margin: 30px 0 20px;
                font-size: 12px;
                font-weight: 600;
            }

            .footnote {
                font-size: 15px;
            }

            .meta-line {
                margin-top: 28px;
                padding-top: 18px;
                border-top: 1px solid var(--line);
                font-size: 11px;
            }
        }

        @media (max-width: 380px) {
            .brandside {
                min-height: 570px;
                padding: 28px 20px 190px;
            }

            .brand-mid {
                margin-top: 42px;
            }

            .brand-mid h1 {
                font-size: 30px;
            }

            .brand-mid p {
                font-size: 14px;
            }

            .formside {
                margin-top: -155px;
                padding-inline: 12px;
            }

            .card {
                padding: 28px 18px 22px;
                border-radius: 24px;
            }

            .card-head h2 {
                font-size: 26px;
            }
        }

        /* Reference layout: dark split-screen login */
        @media (min-width: 921px) {
            body {
                background: #0c172d;
            }

            .stage {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .brandside {
                min-height: 100vh;
                padding: 68px clamp(42px, 4.4vw, 72px);

            }

            .brand-top {
                align-items: center;
                gap: 16px;
            }

            .brand-mark {
                width: 86px;
                height: 52px;
                border: 0;
                border-radius: 10px;
                background: rgba(255, 255, 255, .92);
                box-shadow: 0 6px 18px rgba(0, 0, 0, .14);
            }

            .brand-mark svg {
                width: 28px;
                height: 28px;
            }

            .brand-mark img {
                width: 100%;
                height: 100%;
            }

            .brand-mark svg path {
                fill: var(--sand);
            }

            .brand-name .eyebrow {
                font-family: 'Space Grotesk', sans-serif;
                font-size: 14px;
                font-weight: 700;
                letter-spacing: .03em;
                color: var(--sand);
            }

            .brand-name strong {
                margin-top: 6px;
                font-size: 23px;
                letter-spacing: -.02em;
            }

            .brand-mid,
            .brand-bottom {
                display: none;
            }

            .waves {
                height: 22%;
            }

            .brand-photo {
                display: block;
                position: absolute;
                z-index: 1;
                right: 2%;
                bottom: 0;
                /* width: min(92%, 700px); */
                height: 77%;
                overflow: hidden;
                pointer-events: none;
            }

            .brand-photo::after {
                display: none;
            }

            .brand-photo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: 55% top;
                opacity: 1;
                filter: none;
            }

            .formside {
                padding: 42px clamp(42px, 7.4vw, 116px);
                background:
                    radial-gradient(circle at 90% 15%, rgba(27, 75, 92, .16), transparent 40%),
                    #0c172d;
            }

            .card {
                max-width: 460px;
            }

            .card-head {
                margin-bottom: 36px;
            }

            .card-head .kicker {
                display: none;
            }

            .card-head h2 {
                margin-top: 0;
                color: #f7f9fc;
                font-size: 31px;
                letter-spacing: -.035em;
            }

            .card-head p {
                margin-top: 8px;
                color: #8d9bb3;
                font-size: 16px;
            }

            form {
                gap: 19px;
            }

            .field {
                gap: 8px;
            }

            .field label,
            .remember {
                color: #c7d0de;
                font-size: 14px;
            }

            .input-wrap input {
                min-height: 55px;
                border: 1px solid rgba(126, 149, 184, .28);
                border-radius: 14px;
                background: rgba(3, 12, 29, .62);
                color: #f4f7fb;
                font-size: 15px;
            }

            .input-wrap input::placeholder {
                color: #c9d1dd;
            }

            .input-wrap svg {
                stroke: #8fa0b9;
            }

            .input-wrap input:focus {
                border-color: var(--tirta);
                box-shadow: 0 0 0 4px rgba(18, 181, 170, .14);
            }

            .toggle-eye {
                color: #8fa0b9;
            }

            .toggle-eye:hover {
                color: var(--tirta-lt);
                background: rgba(18, 181, 170, .1);
            }

            .forgot,
            .footnote a {
                color: var(--tirta-lt);
            }

            .remember input {
                width: 19px;
                height: 19px;
            }

            .btn-submit {
                min-height: 53px;
                margin-top: 11px;
                border-radius: 13px;
                background: #0875ad;
                box-shadow: 0 8px 22px rgba(0, 0, 0, .2);
                font-size: 16px;
            }

            .divider {
                margin: 35px 0 27px;
                color: #687891;
            }

            .divider::before,
            .divider::after {
                background: rgba(126, 149, 184, .16);
            }

            .footnote {
                color: #8795ad;
                font-size: 14px;
            }

            .meta-line {
                display: none;
            }

            .alert {
                color: #ffb7aa;
                background: rgba(196, 67, 43, .12);
                border-color: rgba(255, 130, 110, .3);
            }

            .alert.success {
                color: #9ee5dd;
                background: rgba(18, 181, 170, .12);
                border-color: rgba(18, 181, 170, .3);
            }
        }

        @media (max-width: 920px) {
            body {
                background: #0c172d;
            }

            .brandside {
                min-height: 190px;
                padding: 30px 24px 72px;
                border-radius: 0;
            }

            .brand-mid,
            .brand-bottom {
                display: none;
            }

            .brand-top {
                gap: 12px;
            }

            .brand-mark {
                width: 46px;
                height: 46px;
                border-radius: 13px;
            }

            .brand-mark svg {
                width: 25px;
                height: 25px;
            }

            .brand-name .eyebrow {
                font-size: 11px;
            }

            .brand-name strong {
                margin-top: 3px;
                font-size: 19px;
            }

            .formside {
                margin-top: -28px;
                padding: 0 16px 24px;
                background: transparent;
            }

            .card {
                max-width: 560px;
                padding: 30px 22px 26px;
                border-radius: 24px;
                background: #101d34;
                color: #f4f7fb;
                box-shadow: 0 14px 35px rgba(0, 0, 0, .2);
            }

            .card-head .kicker,
            .card-head h2,
            .field label {
                color: #f4f7fb;
            }

            .card-head .kicker,
            .divider,
            .footnote,
            .meta-line {
                display: none;
            }

            .card-head {
                margin-bottom: 24px;
            }

            .card-head h2 {
                margin-top: 0;
                font-size: 27px;
            }

            .card-head p {
                margin-top: 7px;
                font-size: 14px;
            }

            .remember {
                color: #9aa8bd;
            }

            .input-wrap input {
                min-height: 55px;
                padding-top: 13px;
                padding-bottom: 13px;
                border-radius: 14px;
                background: #081329;
                border-color: rgba(126, 149, 184, .28);
                color: #f4f7fb;
            }

            .btn-submit {
                min-height: 55px;
                border-radius: 14px;
                font-size: 16px;
            }

            .waves {
                display: none;
            }

            .brand-photo {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="stage">

        <!-- ============ BRAND / KIRI ============ -->
        <aside class="brandside">
            <div class="brand-top">
                <div class="brand-mark" aria-hidden="true">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo SIKINERJA">
                </div>
                <div class="brand-name">
                    <span class="eyebrow">PERUMDAM TIRTA PINANG</span>
                    <strong>SIKINERJA</strong>
                </div>
            </div>

            <div class="brand-photo" aria-hidden="true">
                <img src="{{ asset('img/pegawai.png') }}" alt="">
            </div>

        </aside>

        <!-- ============ FORM / KANAN ============ -->
        <main class="formside">
            <div class="card">

                <div class="card-head">
                    <span class="kicker">Masuk ke akun</span>
                    <h2>{{ $loginTitle }}</h2>
                    <p>{{ $loginDescription }}</p>
                </div>

                @if (session('success'))
                    <div class="alert success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route($loginRoute) }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="email">Masukan Email</label>
                        <div class="input-wrap {{ $errors->has('email') ? 'err' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path d="m3 7 9 6 9-6" />
                            </svg>
                            <input type="email" id="email" name="email" placeholder="nama@instansi.id"
                                value="{{ old('email') }}" autofocus autocomplete="username">
                        </div>
                        @error('email')
                            <span class="field-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Kata Sandi</label>
                        <div class="input-wrap {{ $errors->has('password') ? 'err' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="11" width="18" height="10" rx="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi"
                                autocomplete="current-password" style="padding-right:44px;">
                            <button type="button" class="toggle-eye" id="toggleEye" aria-label="Tampilkan kata sandi">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8">
                                    <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-msg">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row-between">
                        <label class="remember">
                            <input type="checkbox" name="remember" id="remember">
                            Ingat perangkat ini
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot">Lupa kata sandi?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" y1="12" x2="3" y2="12" />
                        </svg>
                        <span>{{ $loginButton }}</span>
                    </button>
                </form>

                @if (Route::has('register'))
                    <p class="register-prompt">
                        Belum punya akun?
                        <a href="{{ route('register') }}">Daftar sekarang</a>
                    </p>
                @endif

                <p class="register-prompt">
                    Masuk sesuai peran:
                    <a href="{{ route('atasan.login') }}">Atasan</a> ·
                    <a href="{{ route('kepala_bagian.login') }}">Kepala Bagian</a> ·
                    <a href="{{ route('master.login') }}">Admin</a>
                </p>

                <div class="divider">Kanal bantuan</div>
                <p class="footnote">
                    Kendala akses? Hubungi <a href="mailto:it.support@tirtapinang.id">IT Support</a> atau bagian
                    Kepegawaian.
                </p>

                <div class="meta-line">
                    <span>ENV: {{ strtoupper(app()->environment()) }}</span>
                    <span id="clock">--:--:--</span>
                </div>

            </div>
        </main>

    </div>

    <script>
        // jam realtime kecil di footer (murni kosmetik)
        function tickClock() {
            const el = document.getElementById('clock');
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            el.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
        }
        tickClock();
        setInterval(tickClock, 1000);

        // toggle show/hide password
        const toggleEye = document.getElementById('toggleEye');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        toggleEye.addEventListener('click', () => {
            const isPw = passwordInput.type === 'password';
            passwordInput.type = isPw ? 'text' : 'password';
            eyeIcon.innerHTML = isPw ?
                '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 7 11 7a21.6 21.6 0 0 1-2.61 3.68M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>' :
                '<path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/>';
            toggleEye.setAttribute('aria-label', isPw ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    </script>

</body>

</html>

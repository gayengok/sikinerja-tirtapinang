@extends('layouts.app')

@section('title', $attendanceRoute === 'atasan.absensi-saya' ? 'Presensi Atasan' : 'Presensi Pegawai')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        /* ------------------------------------------------------------------
                                                                                                   Design tokens — shared family with the pegawai dashboard
                                                                                                ------------------------------------------------------------------ */
        :root {
            --pr-bg: #f4f7fc;
            --pr-surface: #ffffff;
            --pr-surface-tint: #eef4ff;
            --pr-border: #e4ebf7;
            --pr-ink: #17213b;
            --pr-ink-soft: #55617a;
            --pr-ink-faint: #8792a3;

            --pr-primary: #0875ad;
            --pr-primary-dark: #065f8c;
            --pr-primary-tint: #dceaff;

            --pr-success: #0a8f60;
            --pr-success-tint: #d9f8ea;
            --pr-warning: #a2700a;
            --pr-warning-tint: #fff6dd;
            --pr-warning-border: #f0d999;

            --pr-radius-lg: 1.15rem;
            --pr-radius-md: 0.85rem;
            --pr-radius-sm: 0.65rem;
            --pr-shadow-card: 0 3px 12px rgba(23, 33, 59, 0.06);
        }

        body {
            background: var(--pr-bg);
        }

        .manual-page {
            max-width: 560px;
            margin: 0 auto;
            padding: 0 0 2.2rem;
            color: var(--pr-ink);
        }

        /* ------------------------------------------------------------------
                                                                                                   Header
                                                                                                ------------------------------------------------------------------ */
        .manual-breadcrumb {
            padding: 0.75rem 1rem 0.6rem;
            background: #eef2f9;
            color: var(--pr-ink-soft);
            font-size: 0.7rem;
        }

        .manual-breadcrumb strong {
            font-weight: 700;
            color: var(--pr-ink);
        }

        .manual-header {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.9rem 1rem 0.75rem;
        }

        .manual-back {
            display: grid;
            place-items: center;
            width: 2.15rem;
            height: 2.15rem;
            flex: none;
            border: 1px solid var(--pr-border);
            border-radius: 0.7rem;
            background: var(--pr-surface);
            color: var(--pr-ink);
            font-size: 1rem;
            box-shadow: var(--pr-shadow-card);
        }

        .manual-header h1 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .manual-header p {
            margin: 0.1rem 0 0;
            font-size: 0.72rem;
            color: var(--pr-ink-soft);
        }

        /* ------------------------------------------------------------------
                                                                                                   Shared card + banner primitives
                                                                                                ------------------------------------------------------------------ */
        .manual-body {
            padding: 0 1rem;
        }

        .manual-card {
            margin-bottom: 0.9rem;
            padding: 1rem;
            background: var(--pr-surface);
            border-radius: var(--pr-radius-lg);
            box-shadow: var(--pr-shadow-card);
        }

        .manual-banner {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            font-size: 0.75rem;
            line-height: 1.5;
        }

        .manual-banner i {
            display: grid;
            place-items: center;
            flex: none;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            font-size: 0.95rem;
        }

        .manual-banner-icon {
            width: 1.05rem;
            height: 1.05rem;
            margin: 0.475rem;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .manual-banner strong {
            display: block;
            margin-bottom: 0.15rem;
            font-size: 0.7rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .manual-banner-info {
            background: var(--pr-surface-tint);
            color: var(--pr-ink-soft);
        }

        .manual-banner-info i {
            color: var(--pr-primary);
            background: var(--pr-primary-tint);
        }

        .manual-banner-info strong {
            color: var(--pr-primary-dark);
        }

        .manual-banner-warning {
            background: var(--pr-warning-tint);
            color: #7a5a10;
            border: 1px solid var(--pr-warning-border);
        }

        .manual-banner-warning i {
            color: var(--pr-warning);
            background: #fbe8b0;
        }

        .manual-banner-warning strong {
            color: var(--pr-warning);
        }

        /* ------------------------------------------------------------------
                                                                                                   Identity card
                                                                                                ------------------------------------------------------------------ */
        .manual-identity {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .manual-avatar {
            display: grid;
            place-items: center;
            width: 3.6rem;
            height: 3.6rem;
            flex: none;
            border: 3px solid var(--pr-success-tint);
            border-radius: 50%;
            background: linear-gradient(145deg, #d9b184, #6b4c34);
            color: #fff;
            font-size: 1.25rem;
            font-weight: 800;
        }

        .manual-identity-copy {
            min-width: 0;
        }

        .manual-identity-copy h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .manual-identity-copy p {
            margin: 0.2rem 0;
            font-size: 0.75rem;
            color: var(--pr-ink-soft);
        }

        .manual-identity-copy a {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--pr-primary);
            font-size: 0.72rem;
            font-weight: 600;
            text-decoration: none;
        }

        .manual-active {
            margin-left: auto;
            align-self: flex-start;
            padding: 0.3rem 0.6rem;
            border-radius: 999px;
            background: var(--pr-success-tint);
            color: var(--pr-success);
            font-size: 0.62rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .manual-photo-status {
            background: var(--pr-warning-tint);
            color: var(--pr-warning);
        }

        .manual-photo-status.is-ready {
            background: var(--pr-success-tint);
            color: var(--pr-success);
        }

        /* ------------------------------------------------------------------
                                                                                                   Section heading pattern
                                                                                                ------------------------------------------------------------------ */
        .manual-section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }

        .manual-section-title span:first-child {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .manual-section-title i {
            color: var(--pr-primary);
        }

        .manual-sync {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: var(--pr-success);
            font-size: 0.65rem;
            font-weight: 700;
        }

        .manual-sync i {
            font-size: 0.5rem;
        }

        .manual-chip {
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            background: var(--pr-success-tint);
            color: var(--pr-success);
            font-size: 0.62rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ------------------------------------------------------------------
                                                                                                   Jenis presensi
                                                                                                ------------------------------------------------------------------ */
        .manual-types {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.4rem;
            padding: 0.35rem;
            border-radius: var(--pr-radius-md);
            background: var(--pr-surface-tint);
        }

        .manual-types button {
            border: 0;
            background: transparent;
            cursor: pointer;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.75rem 0.4rem;
            border-radius: var(--pr-radius-sm);
            color: var(--pr-ink-soft);
            text-align: center;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .manual-types button small {
            flex-basis: 100%;
            font-size: 0.56rem;
            font-weight: 500;
        }

        .manual-types button.selected {
            background: var(--pr-primary);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(8, 117, 173, 0.25);
        }

        .manual-clock {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.85rem;
            padding: 0.85rem 1rem;
            border-radius: var(--pr-radius-md);
            background: var(--pr-surface-tint);
        }

        .manual-clock-icon {
            display: grid;
            place-items: center;
            width: 2.6rem;
            height: 2.6rem;
            flex: none;
            border-radius: 0.8rem;
            background: var(--pr-primary-tint);
            color: var(--pr-primary);
            font-size: 1.1rem;
        }

        .manual-clock-time {
            flex: 1;
            min-width: 9rem;
        }

        .manual-clock-time small {
            display: block;
            color: var(--pr-ink-soft);
            font-size: 0.7rem;
        }

        .manual-clock-time strong {
            display: block;
            color: var(--pr-primary);
            font-size: 1.5rem;
            line-height: 1.2;
            font-weight: 800;
        }

        .manual-clock-time strong em {
            color: var(--pr-ink-soft);
            font-size: 0.7rem;
            font-style: normal;
            font-weight: 600;
        }

        .manual-clock-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.3rem;
        }

        .manual-lock {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.5rem;
            border-radius: 999px;
            background: var(--pr-success-tint);
            color: var(--pr-success);
            font-size: 0.6rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .manual-server {
            color: var(--pr-ink-faint);
            font-size: 0.62rem;
        }

        /* ------------------------------------------------------------------
                                                                                                   Form fields
                                                                                                ------------------------------------------------------------------ */
        .manual-label {
            display: block;
            margin: 0 0 0.4rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--pr-ink-soft);
            letter-spacing: 0.02em;
        }

        .manual-field+.manual-label {
            margin-top: 1rem;
        }

        .manual-select,
        .manual-textarea {
            width: 100%;
            padding: 0.8rem 0.9rem;
            border: 1px solid var(--pr-border);
            border-radius: var(--pr-radius-sm);
            background: var(--pr-surface-tint);
            color: var(--pr-ink);
            font-size: 0.78rem;
            font-family: inherit;
        }

        .manual-textarea {
            resize: vertical;
        }

        /* ------------------------------------------------------------------
                                                                                                   Geolocation status
                                                                                                ------------------------------------------------------------------ */
        .manual-map {
            position: relative;
            height: 13rem;
            overflow: hidden;
            border-radius: var(--pr-radius-md);
            border: 1px solid var(--pr-border);
            background: #dce8ef;
            z-index: 0;
        }

        .manual-map-canvas {
            width: 100%;
            height: 100%;
        }

        .manual-map-overlay {
            position: absolute;
            left: 0.75rem;
            right: 0.75rem;
            bottom: 0.75rem;
            z-index: 400;
            padding: 0.55rem 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: var(--pr-radius-sm);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 2px 8px rgba(23, 33, 59, 0.14);
            color: var(--pr-ink-soft);
            font-size: 0.64rem;
            line-height: 1.35;
            pointer-events: none;
        }

        .manual-map-overlay strong {
            display: block;
            color: var(--pr-ink);
            font-size: 0.68rem;
        }

        .manual-map .leaflet-control-attribution {
            padding: 1px 4px;
            background: rgba(255, 255, 255, 0.72);
            color: #6b7280;
            font-size: 0.52rem;
        }

        .manual-map .leaflet-control-attribution a:first-child {
            display: none;
        }

        .manual-gps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.75rem;
            padding: 0.7rem 0.85rem;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-surface-tint);
        }

        .manual-gps strong {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .manual-gps small {
            color: var(--pr-ink-faint);
            font-size: 0.64rem;
        }

        .manual-gps-status {
            display: block;
            margin-top: 0.2rem;
            color: var(--pr-ink-faint);
            font-size: 0.64rem;
        }

        .manual-refresh {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            flex: none;
            padding: 0.5rem 0.7rem;
            border: 0;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-primary-tint);
            color: var(--pr-primary);
            font-size: 0.66rem;
            font-weight: 700;
        }

        /* ------------------------------------------------------------------
                                                                                                   Manual photo input (redesigned)
                                                                                                ------------------------------------------------------------------ */
        .manual-photo {
            position: relative;
            height: 16rem;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid #223b55;
            background: radial-gradient(circle at 50% 42%, #294a68 0%, #172b41 42%, #0f1d2d 100%);
            color: #fff;
            text-align: center;
        }

        .manual-photo-preview {
            display: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .manual-photo.has-preview .manual-photo-preview {
            display: block;
        }

        .manual-photo.has-preview {
            border-style: solid;
            border-color: #55b7e8;
            background: #102437;
        }

        .manual-photo.has-preview .manual-photo-body {
            display: none;
        }

        .manual-photo-guide {
            position: absolute;
            inset: 0;
        }

        .manual-photo.has-preview .manual-photo-guide,
        .manual-photo.has-preview .manual-photo-label {
            display: none;
        }

        .manual-photo-frame {
            position: absolute;
            top: 3.05rem;
            left: 50%;
            transform: translateX(-50%);
            width: 7.2rem;
            height: 8.2rem;
            border-radius: 50%;
            border: 1px solid rgba(167, 220, 247, 0.65);
            background: rgba(255, 255, 255, 0.04);
            box-shadow: 0 0 0 999px rgba(5, 15, 26, 0.16);
        }

        .manual-photo-scan {
            position: absolute;
            left: 12%;
            right: 12%;
            top: 3.05rem;
            height: 2px;
            background: linear-gradient(90deg, transparent, #82d4f6, transparent);
            opacity: 0.2;
            animation: manual-scan 2.6s ease-in-out infinite;
        }

        @keyframes manual-scan {

            0%,
            100% {
                transform: translateY(0);
                opacity: 0.2;
            }

            50% {
                transform: translateY(8.2rem);
                opacity: 0.9;
            }
        }

        .manual-photo-body {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1.2rem;
        }

        .manual-photo-body-icon {
            display: grid;
            place-items: center;
            width: 3.35rem;
            height: 3.35rem;
            margin-bottom: 0.85rem;
            border-radius: 50%;
            border: 1px solid rgba(157, 215, 242, 0.5);
            background: rgba(71, 164, 210, 0.2);
            color: #a9e3ff;
            flex: none;
        }

        .manual-photo-body strong {
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .manual-photo-icon {
            display: block;
            width: 1.35rem;
            height: 1.35rem;
            overflow: visible;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }

        .manual-photo-body .manual-photo-icon {
            width: 1.45rem;
            height: 1.45rem;
            color: #a9e3ff;
            stroke: #a9e3ff;
        }

        .manual-photo-hint {
            display: block;
            margin-top: 0.35rem;
            color: rgba(232, 244, 252, 0.72);
            font-size: 0.72rem;
            font-weight: 500;
        }

        .manual-photo-label {
            position: absolute;
            left: 0.9rem;
            right: 0.9rem;
            bottom: 0.7rem;
            color: rgba(223, 240, 250, 0.68);
            font-size: 0.6rem;
            text-align: center;
            z-index: 1;
        }

        .manual-photo-label i {
            margin-right: 0.25rem;
            color: #82d4f6;
        }

        .manual-photo-corner {
            position: absolute;
            width: 1.25rem;
            height: 1.25rem;
            border-color: rgba(145, 216, 245, 0.9);
            border-style: solid;
            z-index: 1;
        }

        .manual-photo-corner-tl {
            top: 1rem;
            left: 1rem;
            border-width: 2px 0 0 2px;
            border-radius: 0.35rem 0 0 0;
        }

        .manual-photo-corner-tr {
            top: 1rem;
            right: 1rem;
            border-width: 2px 2px 0 0;
            border-radius: 0 0.35rem 0 0;
        }

        .manual-photo-corner-bl {
            bottom: 1rem;
            left: 1rem;
            border-width: 0 0 2px 2px;
            border-radius: 0 0 0 0.35rem;
        }

        .manual-photo-corner-br {
            right: 1rem;
            bottom: 1rem;
            border-width: 0 2px 2px 0;
            border-radius: 0 0 0.35rem 0;
        }

        .manual-photo-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            display: none;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background: var(--pr-success);
            color: #fff;
            box-shadow: 0 3px 10px rgba(10, 143, 96, 0.4);
            z-index: 2;
        }

        .manual-photo.has-preview .manual-photo-badge {
            display: flex;
        }

        .manual-photo-badge-icon {
            width: 1.1rem;
            height: 1.1rem;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .manual-photo-floating-retake {
            position: absolute;
            left: 0.75rem;
            bottom: 0.75rem;
            display: none;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 50%;
            background: rgba(15, 29, 45, 0.55);
            backdrop-filter: blur(4px);
            color: #fff;
            z-index: 2;
            cursor: pointer;
        }

        .manual-photo.has-preview .manual-photo-floating-retake {
            display: flex;
        }

        .manual-photo-floating-retake svg {
            width: 1.1rem;
            height: 1.1rem;
        }

        .manual-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .manual-action {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.65rem 0.4rem;
            border: 0;
            border-radius: var(--pr-radius-sm);
            background: var(--pr-primary-tint);
            color: var(--pr-primary-dark);
            font-size: 0.7rem;
            font-weight: 600;
        }

        .manual-action-primary {
            background: var(--pr-primary);
            color: #fff;
        }

        .manual-action:disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .manual-action svg {
            flex: none;
        }

        .manual-photo-input {
            display: none;
        }

        /* ------------------------------------------------------------------
                                                                                                   Submit
                                                                                                ------------------------------------------------------------------ */
        .manual-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            height: 3.25rem;
            border: 0;
            border-radius: var(--pr-radius-md);
            background: #0875ad;
            color: #fff;
            box-shadow: 0 6px 16px rgba(6, 95, 140, 0.28);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .manual-note {
            display: flex;
            gap: 0.55rem;
            margin: 0.85rem 0.35rem 0;
            color: var(--pr-ink-faint);
            font-size: 0.65rem;
            line-height: 1.55;
        }

        .manual-note i {
            display: grid;
            place-items: center;
            flex: none;
            margin-top: 0.1rem;
            width: 1.15rem;
            height: 1.15rem;
            color: var(--pr-primary);
        }

        .manual-note-icon {
            display: block;
            width: 1rem;
            height: 1rem;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 1.8;
        }

        .manual-disabled {
            opacity: 0.6;
            pointer-events: none;
            filter: grayscale(0.25);
        }

        fieldset.manual-disabled {
            min-width: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }

        .manual-disabled .manual-submit {
            background: #9aa6b5;
            box-shadow: none;
        }

        .manual-holiday-dialog {
            width: min(22rem, calc(100% - 2rem));
            padding: 1.35rem;
            color: var(--pr-ink);
            border: 0;
            border-radius: var(--pr-radius-lg);
            box-shadow: 0 18px 55px rgba(23, 33, 59, 0.25);
        }

        .manual-holiday-dialog::backdrop {
            background: rgba(14, 27, 48, 0.58);
            backdrop-filter: blur(3px);
        }

        .manual-holiday-dialog-content {
            text-align: center;
        }

        .manual-holiday-dialog-icon {
            display: grid;
            place-items: center;
            width: 3rem;
            height: 3rem;
            margin: 0 auto .75rem;
            color: var(--pr-warning);
            background: var(--pr-warning-tint);
            border-radius: 50%;
            font-size: 1.25rem;
        }

        .manual-holiday-dialog h2 {
            margin: 0 0 .4rem;
            font-size: 1rem;
        }

        .manual-holiday-dialog p {
            margin: 0;
            color: var(--pr-ink-soft);
            font-size: .78rem;
            line-height: 1.55;
        }

        .manual-holiday-dialog button {
            width: 100%;
            margin-top: 1rem;
            padding: .7rem;
            color: #fff;
            background: var(--pr-primary);
            border: 0;
            border-radius: var(--pr-radius-sm);
            font: inherit;
            font-weight: 700;
        }

        @media (max-width: 380px) {
            .manual-clock-time strong {
                font-size: 1.3rem;
            }

            .manual-photo {
                height: 15rem;
            }
        }

        @media (min-width: 768px) {
            body.desktop-atasan-attendance.role-atasan .mobile-app-shell {
                width: min(1440px, 100%);
                overflow-x: visible;
            }

            body.desktop-atasan-attendance.role-atasan .navbar-sikinerja {
                left: 50%;
                width: min(1440px, 100%);
            }

            body.desktop-atasan-attendance.role-atasan .mobile-bottom-nav {
                left: 50%;
                width: min(1440px, 100%);
                max-width: 1440px;
            }

            .manual-atasan-page {
                width: min(1600px, 100%);
                max-width: none;
                margin: 0 auto;
                padding: 0.75rem 1rem 2rem;
            }

            .manual-atasan-page .manual-header,
            .manual-atasan-page .manual-identity {
                display: none;
            }

            .manual-atasan-page .manual-body {
                padding: 0;
            }

            .manual-atasan-page fieldset {
                min-width: 0;
                margin: 0;
                padding: 0;
                border: 0;
            }

            .manual-atasan-page #attendanceForm {
                width: 100%;
                margin: 0;
            }

            .manual-atasan-page .manual-system-notice {
                margin-bottom: 0.9rem;
                padding: 0.65rem 0.8rem;
                border: 1px solid #cfe7f8;
                border-radius: 0.7rem;
                background: #f2faff;
                font-size: 0.72rem;
            }

            .manual-atasan-page .manual-system-notice i {
                width: 1.65rem;
                height: 1.65rem;
            }

            .manual-atasan-page .manual-attendance-layout {
                display: grid;
                grid-template-columns: minmax(285px, 0.8fr) minmax(0, 1.2fr);
                align-items: start;
                gap: 0.9rem;
            }

            .manual-atasan-page .manual-attendance-left,
            .manual-atasan-page .manual-attendance-right {
                display: grid;
                align-content: start;
                gap: 0.9rem;
                min-width: 0;
            }

            .manual-atasan-page .manual-attendance-layout .manual-card {
                margin: 0;
                padding: 0.9rem;
                border: 1px solid #e4ebf3;
                border-radius: 0.85rem;
                box-shadow: 0 2px 8px rgba(23, 33, 59, 0.04);
            }

            .manual-atasan-page .manual-clock-card {
                background: linear-gradient(135deg, #fff 55%, #f1faff);
            }

            .manual-atasan-page .manual-clock-card .manual-section-title {
                margin-bottom: 0.55rem;
                color: #64748b;
                font-size: 0.58rem;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .manual-atasan-page .manual-clock {
                min-height: 0;
                padding: 0;
                border: 0;
                background: transparent;
                box-shadow: none;
            }

            .manual-atasan-page .manual-clock-icon {
                display: none;
            }

            .manual-atasan-page .manual-clock-time strong {
                color: #0f172a;
                font-size: clamp(1.65rem, 3vw, 2.2rem);
                letter-spacing: 0.04em;
            }

            .manual-atasan-page .manual-clock-time small {
                color: #64748b;
                font-size: 0.62rem;
                text-transform: uppercase;
            }

            .manual-atasan-page .manual-clock-meta {
                align-self: flex-start;
                margin-left: auto;
            }

            .manual-atasan-page .manual-lock {
                padding: 0.28rem 0.45rem;
                border: 1px solid #c7f2df;
                border-radius: 0.4rem;
                background: #ecfdf5;
                color: #059669;
                font-size: 0.55rem;
            }

            .manual-atasan-page .manual-presence-card .manual-section-title {
                margin-bottom: 0.45rem;
                color: #64748b;
                font-size: 0.58rem;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .manual-atasan-page .manual-types {
                margin-bottom: 0.85rem;
                padding: 0.25rem;
                border-radius: 0.55rem;
                background: #f1f5f9;
            }

            .manual-atasan-page .manual-types button {
                padding: 0.6rem 0.4rem;
                border-radius: 0.4rem;
                font-size: 0.68rem;
            }

            .manual-atasan-page .manual-label {
                margin-bottom: 0.35rem;
                font-size: 0.58rem;
                text-transform: uppercase;
            }

            .manual-atasan-page .manual-select {
                padding: 0.65rem 0.75rem;
                border-radius: 0.55rem;
                background-color: #fff;
                font-size: 0.68rem;
            }

            .manual-atasan-page .manual-submit {
                height: 2.8rem;
                margin-top: 0.85rem;
                border-radius: 0.6rem;
                font-size: 0.7rem;
            }

            .manual-atasan-page .manual-note {
                margin: 0.1rem 0 0;
                font-size: 0.58rem;
            }

            .manual-atasan-page .manual-gps-card .manual-section-title,
            .manual-atasan-page .manual-photo-card .manual-section-title {
                min-height: 1.65rem;
                margin-bottom: 0.55rem;
                font-size: 0.68rem;
            }

            .manual-atasan-page .manual-gps-status {
                margin: 0 0 0.55rem;
                font-size: 0.58rem;
            }

            .manual-atasan-page .manual-map {
                height: clamp(7rem, 13vw, 10rem);
                border-radius: 0.65rem;
            }

            .manual-atasan-page .manual-map-overlay {
                left: 0.5rem;
                right: 0.5rem;
                bottom: 0.45rem;
                padding: 0.4rem 0.5rem;
                font-size: 0.52rem;
            }

            .manual-atasan-page .manual-map-overlay strong {
                font-size: 0.56rem;
            }

            .manual-atasan-page .manual-gps {
                margin-top: 0.55rem;
                padding: 0;
                background: transparent;
            }

            .manual-atasan-page .manual-gps strong,
            .manual-atasan-page .manual-gps small {
                font-size: 0.58rem;
            }

            .manual-atasan-page .manual-refresh {
                padding: 0.4rem 0.55rem;
                border: 1px solid #dce6f1;
                background: #fff;
                font-size: 0.58rem;
            }

            .manual-atasan-page .manual-photo {
                height: clamp(10rem, 22vw, 13rem);
                border-radius: 0.65rem;
            }

            .manual-atasan-page .manual-photo-frame {
                top: 1.6rem;
                height: 6rem;
            }

            .manual-atasan-page .manual-photo-scan {
                top: 1.6rem;
            }

            .manual-atasan-page .manual-photo-body {
                margin-top: 0;
            }

            .manual-atasan-page .manual-photo-body-icon {
                width: 2.5rem;
                height: 2.5rem;
                margin-bottom: 0.45rem;
            }

            .manual-atasan-page .manual-photo-body strong {
                font-size: 0.68rem;
            }

            .manual-atasan-page .manual-photo-hint,
            .manual-atasan-page .manual-photo-label {
                font-size: 0.55rem;
            }

            .manual-atasan-page .manual-photo-corner {
                width: 0.9rem;
                height: 0.9rem;
            }

            .manual-atasan-page .manual-photo-corner-tl,
            .manual-atasan-page .manual-photo-corner-tr {
                top: 0.6rem;
            }

            .manual-atasan-page .manual-photo-corner-bl,
            .manual-atasan-page .manual-photo-corner-br {
                bottom: 0.6rem;
            }

            .manual-atasan-page .manual-actions {
                gap: 0.45rem;
                margin-top: 0.55rem;
            }

            .manual-atasan-page .manual-action {
                padding: 0.55rem 0.4rem;
                font-size: 0.6rem;
            }
        }

        @media (min-width: 768px) and (max-width: 900px) {
            .manual-atasan-page .manual-attendance-layout {
                grid-template-columns: minmax(250px, 0.85fr) minmax(0, 1.15fr);
                gap: 0.65rem;
            }

            .manual-atasan-page .manual-attendance-left,
            .manual-atasan-page .manual-attendance-right {
                gap: 0.65rem;
            }
        }

        @media (max-width: 767.98px) {
            .manual-atasan-page .manual-attendance-layout,
            .manual-atasan-page .manual-attendance-left,
            .manual-atasan-page .manual-attendance-right {
                display: contents;
            }
        }
    </style>
@endpush

@section('content')
    <div class="manual-page {{ $attendanceRoute === 'atasan.absensi-saya' ? 'manual-atasan-page' : '' }}">
        @php($attendanceLocked = $cutiHariIni || $hariLiburHariIni !== null)
        @php($jenisPresensiAwal = old('jenis_presensi', $absensiMasukHariIni && !$absensiPulangHariIni ? 'pulang' : 'masuk'))

        <header class="manual-header">
            <button class="manual-back" type="button" onclick="history.back()" aria-label="Kembali">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            <div>
                <h1>{{ $attendanceRoute === 'atasan.absensi-saya' ? 'Presensi Atasan' : 'Presensi Pegawai' }}</h1>
                <p>Rekam kehadiran Anda dengan verifikasi lokasi &amp; foto</p>
            </div>
        </header>

        @if ($hariLiburHariIni)
            <dialog class="manual-holiday-dialog" id="holidayDialog" aria-labelledby="holidayDialogTitle">
                <div class="manual-holiday-dialog-content">
                    <span class="manual-holiday-dialog-icon"><i class="fa-solid fa-calendar-xmark"></i></span>
                    <h2 id="holidayDialogTitle">Hari Libur</h2>
                    <p>Keterangan: {{ $hariLiburHariIni['label'] }}. Anda tidak perlu melakukan presensi. Fitur presensi
                        dikunci otomatis dan akan terbuka kembali pada hari kerja.</p>
                    <button type="button" id="closeHolidayDialog">Mengerti</button>
                </div>
            </dialog>
        @endif
        <dialog class="manual-holiday-dialog" id="attendanceWarningDialog"
            data-show-on-load="{{ session('attendance_warning') ? 'true' : 'false' }}"
            aria-labelledby="attendanceWarningDialogTitle">
            <div class="manual-holiday-dialog-content">
                <span class="manual-holiday-dialog-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                <h2 id="attendanceWarningDialogTitle">Presensi Sudah Tercatat</h2>
                <p>{{ session('attendance_warning', 'Setiap jenis presensi hanya dapat dilakukan satu kali dalam sehari.') }}</p>
                <button type="button" class="close-attendance-warning">Mengerti</button>
            </div>
        </dialog>

        <div class="manual-body">
            <div class="manual-card manual-banner manual-banner-info manual-system-notice">
                <i aria-hidden="true">
                    <svg class="manual-banner-icon" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M12 11v5"></path>
                        <path d="M12 8h.01"></path>
                    </svg>
                </i>
                <div>
                    <strong>Perhatian Sistem</strong>
                    Pengajuan presensi memerlukan verifikasi foto selfie langsung di lokasi kerja serta koordinat
                    GPS akurat bertempel waktu. Pastikan izin browser untuk Kamera dan Lokasi aktif.
                </div>
            </div>

            <div class="manual-card manual-identity">
                <div class="manual-avatar">{{ strtoupper(substr($pegawai->nama, 0, 1)) }}</div>
                <div class="manual-identity-copy">
                    <h2>{{ $pegawai->nama }}</h2>
                    <a href="#"><i class="fa-regular fa-id-badge"></i>
                        {{ $pegawai->jabatan->nama_jabatan ?? 'Pegawai' }}</a>
                </div>
                <span class="manual-active">Aktif</span>
            </div>

            @if ($cutiHariIni)
                <div class="manual-card manual-banner manual-banner-warning" style="margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-umbrella-beach"></i>
                    <div>
                        <strong>Anda sedang cuti hari ini</strong>
                        Presensi dinonaktifkan karena status kehadiran Anda tercatat sebagai cuti. Presensi dapat
                        dilakukan kembali pada hari kerja berikutnya.
                    </div>
                </div>
            @endif

            @if ($hariLiburHariIni)
                <div class="manual-card manual-banner manual-banner-warning" style="margin-bottom: 0.9rem;">
                    <i class="fa-solid fa-calendar-xmark"></i>
                    <div>
                        <strong>Hari ini hari libur</strong>
                        {{ $hariLiburHariIni['label'] }}. Presensi tidak diperlukan dan dinonaktifkan otomatis.
                    </div>
                </div>
            @endif

            <fieldset class="{{ $attendanceLocked ? 'manual-disabled' : '' }}" @disabled($attendanceLocked)>
                <form id="attendanceForm" action="{{ route($attendanceRoute . '.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="jenisPresensi" name="jenis_presensi"
                        value="{{ $jenisPresensiAwal }}">
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                    <input type="hidden" id="accuracy" name="accuracy">

                    @if ($errors->any())
                        <div class="manual-card manual-banner manual-banner-warning" role="alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    @if (session('success') || session('error'))
                        <div class="manual-card manual-banner manual-banner-info" role="status">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>{{ session('success') ?: session('error') }}</div>
                        </div>
                    @endif

                    <div class="manual-attendance-layout">
                        <div class="manual-attendance-left">
                            <section class="manual-card manual-clock-card">
                                <div class="manual-section-title">
                                    <span>Waktu Sistem Server</span>
                                </div>
                                <div class="manual-clock" id="attendanceClock" data-server-timestamp="{{ now()->timestamp }}">
                                    <div class="manual-clock-icon"><i class="fa-regular fa-clock"></i></div>
                                    <div class="manual-clock-time">
                                        <small id="attendanceClockDate">{{ now()->translatedFormat('l, d F Y') }}</small>
                                        <strong><span id="attendanceClockTime">{{ now()->format('H:i:s') }}</span> <em>WIB</em></strong>
                                    </div>
                                    <div class="manual-clock-meta">
                                        <span class="manual-lock"><i class="fa-solid fa-lock"></i> Waktu Terkunci</span>
                                    </div>
                                </div>
                            </section>

                            <section class="manual-card manual-presence-card">
                                <div class="manual-section-title">
                                    <span>Jenis Presensi</span>
                                    <span class="manual-sync"><i class="fa-solid fa-circle"></i> Sinkron Server</span>
                                </div>

                                <div class="manual-types" role="group" aria-label="Jenis presensi">
                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'masuk' ? 'selected' : '' }}"
                                        data-presensi="masuk" data-completed="{{ $absensiMasukHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk
                                        @if ($absensiMasukHariIni)<small>Sudah tercatat</small>@endif
                                    </button>
                                    <button type="button"
                                        class="{{ $jenisPresensiAwal === 'pulang' ? 'selected' : '' }}"
                                        data-presensi="pulang" data-completed="{{ $absensiPulangHariIni ? 'true' : 'false' }}">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Pulang
                                        @if ($absensiPulangHariIni)<small>Sudah tercatat</small>@endif
                                    </button>
                                </div>

                                <label class="manual-label" for="status">Status Absensi</label>
                                <select class="manual-select manual-field" id="status" name="status" required>
                                    <option value="hadir" @selected(old('status', 'hadir') === 'hadir')>Hadir (Bekerja Normal)</option>
                                    <option value="izin" @selected(old('status') === 'izin')>Izin</option>
                                    <option value="cuti" @selected(old('status') === 'cuti')>Cuti</option>
                                </select>
                                <button type="submit" form="attendanceForm" class="manual-submit">
                                    <i class="fa-solid fa-user-check"></i> Kirim Presensi
                                </button>
                                <div class="manual-note">
                                    <i aria-hidden="true">
                                        <svg class="manual-note-icon" viewBox="0 0 24 24">
                                            <path d="M12 3 20 6v5c0 5-3.4 8.6-8 10-4.6-1.4-8-5-8-10V6l8-3Z"></path>
                                            <path d="m9 12 2 2 4-4"></path>
                                        </svg>
                                    </i>
                                    <span>Data presensi, titik koordinat GPS, dan foto akan diverifikasi oleh atasan langsung dan Subbagian Kepegawaian sesuai Peraturan Disiplin.</span>
                                </div>
                            </section>
                        </div>

                        <div class="manual-attendance-right">
                            <section class="manual-card manual-gps-card">
                                <div class="manual-section-title">
                                    <span><i class="fa-solid fa-location-crosshairs"></i> Validasi Geolokasi GPS</span>
                                    <span class="manual-chip">GPS presisi</span>
                                </div>
                                <p class="manual-gps-status">
                                    Presensi tetap dapat dikirim dari luar area kantor. Lokasi dan jaraknya akan ditandai untuk
                                    verifikasi atasan.
                                </p>

                                <div class="manual-map">
                                    <div class="manual-map-canvas" id="attendanceMap"></div>
                                    <div class="manual-map-overlay">
                                        <strong id="gpsLocationTitle">LOKASI BELUM DIVERIFIKASI</strong>
                                        <span id="gpsCoordinates">Tekan Perbarui untuk membaca beberapa pembacaan GPS terbaik</span>
                                    </div>
                                </div>

                                <div class="manual-gps">
                                    <div>
                                        <strong>Wajib berada di area kantor</strong>
                                        <small class="manual-gps-status" id="gpsStatus">GPS belum dibaca</small>
                                    </div>
                                    <button type="button" class="manual-refresh" id="refreshGpsButton">
                                        <i class="fa-solid fa-rotate"></i> Perbarui
                                    </button>
                                </div>
                            </section>

                            <section class="manual-card manual-photo-card">
                                <div class="manual-section-title">
                                    <span>
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        Foto Absensi
                                    </span>
                                    <span class="manual-chip manual-photo-status" id="manualPhotoStatus">Belum diambil</span>
                                </div>

                                <div class="manual-photo" id="manualPhotoPreview">
                                    <img class="manual-photo-preview" id="manualPhotoImage" alt="Pratinjau foto absensi">

                                    <div class="manual-photo-guide" aria-hidden="true">
                                        <span class="manual-photo-corner manual-photo-corner-tl"></span>
                                        <span class="manual-photo-corner manual-photo-corner-tr"></span>
                                        <span class="manual-photo-corner manual-photo-corner-bl"></span>
                                        <span class="manual-photo-corner manual-photo-corner-br"></span>
                                        <span class="manual-photo-frame"></span>
                                        <span class="manual-photo-scan"></span>
                                    </div>

                                    <div class="manual-photo-body">
                                        <span class="manual-photo-body-icon">
                                            <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                                <path
                                                    d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                                <circle cx="12" cy="13" r="3.5" />
                                            </svg>
                                        </span>
                                        <strong>Siap mengambil foto</strong>
                                        <span class="manual-photo-hint">Pastikan wajah terlihat jelas dan pencahayaan cukup</span>
                                    </div>

                                    <span class="manual-photo-label"><i class="fa-solid fa-shield-halved"></i> Foto digunakan
                                        untuk verifikasi absensi</span>

                                    <span class="manual-photo-badge" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" class="manual-photo-badge-icon">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                    </span>

                                    <button type="button" class="manual-photo-floating-retake" id="manualFloatingRetake"
                                        aria-label="Ambil ulang foto">
                                        <svg viewBox="0 0 24 24" class="manual-photo-icon">
                                            <path d="M20 11a8 8 0 0 0-14.8-4L3 9" />
                                            <path d="M3 4v5h5" />
                                            <path d="M4 13a8 8 0 0 0 14.8 4L21 15" />
                                            <path d="M21 20v-5h-5" />
                                        </svg>
                                    </button>
                                </div>

                                <input class="manual-photo-input" id="manualPhotoInput" name="foto" type="file" accept="image/*"
                                    capture="user">

                                <div class="manual-actions">
                                    <button type="button" class="manual-action manual-action-primary" id="manualPhotoButton">
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                                            <circle cx="12" cy="13" r="3.5" />
                                        </svg>
                                        Ambil Foto
                                    </button>
                                    <button type="button" class="manual-action" id="manualRetakeButton" disabled>
                                        <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M20 11a8 8 0 0 0-14.8-4L3 9" />
                                            <path d="M3 4v5h5" />
                                            <path d="M4 13a8 8 0 0 0 14.8 4L21 15" />
                                            <path d="M21 20v-5h-5" />
                                        </svg>
                                        Ambil Ulang
                                    </button>
                                </div>
                            </section>
                        </div>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (() => {
            const clock = document.getElementById('attendanceClock');
            const dateElement = document.getElementById('attendanceClockDate');
            const timeElement = document.getElementById('attendanceClockTime');
            const serverTimestamp = Number(clock?.dataset.serverTimestamp);

            if (!clock || !dateElement || !timeElement || !Number.isFinite(serverTimestamp)) {
                return;
            }

            const serverTime = serverTimestamp * 1000;
            const pageOpenedAt = performance.now();
            const dateFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric',
            });
            const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                timeZone: 'Asia/Jakarta',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hourCycle: 'h23',
            });

            const updateClock = () => {
                const currentTime = new Date(serverTime + performance.now() - pageOpenedAt);
                dateElement.textContent = dateFormatter.format(currentTime);
                timeElement.textContent = timeFormatter.format(currentTime);
            };

            updateClock();
            window.setInterval(updateClock, 250);
        })();

        (() => {
            const openDialog = (dialog) => {
                if (!dialog) {
                    return;
                }

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                } else {
                    dialog.setAttribute('open', '');
                }
            };
            const closeDialog = (dialog) => {
                if (typeof dialog?.close === 'function') {
                    dialog.close();
                } else {
                    dialog?.removeAttribute('open');
                }
            };
            const holidayDialog = document.getElementById('holidayDialog');
            const attendanceWarningDialog = document.getElementById('attendanceWarningDialog');

            openDialog(holidayDialog);
            if (attendanceWarningDialog?.dataset.showOnLoad === 'true') {
                openDialog(attendanceWarningDialog);
            }

            document.getElementById('closeHolidayDialog')?.addEventListener('click', () => {
                closeDialog(holidayDialog);
            });
            document.querySelectorAll('.close-attendance-warning').forEach((button) => {
                button.addEventListener('click', () => closeDialog(attendanceWarningDialog));
            });
        })();

        (() => {
            const typeInput = document.getElementById('jenisPresensi');
            const typeButtons = document.querySelectorAll('[data-presensi]');

            typeButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.dataset.completed === 'true') {
                        const warningDialog = document.getElementById('attendanceWarningDialog');
                        const warningText = document.querySelector('#attendanceWarningDialog p');
                        const label = button.dataset.presensi === 'masuk' ? 'masuk' : 'pulang';

                        if (warningText) {
                            warningText.textContent =
                                `Presensi ${label} hari ini sudah tercatat. Setiap jenis presensi hanya dapat dilakukan satu kali dalam sehari.`;
                        }
                        if (warningDialog) {
                            if (warningDialog.open) {
                                return;
                            }
                            if (typeof warningDialog.showModal === 'function') {
                                warningDialog.showModal();
                            } else {
                                warningDialog.setAttribute('open', '');
                            }
                        }
                        return;
                    }

                    typeButtons.forEach((item) => item.classList.remove('selected'));
                    button.classList.add('selected');
                    typeInput.value = button.dataset.presensi;
                });
            });
        })();

        (() => {
            const input = document.getElementById('manualPhotoInput');
            const preview = document.getElementById('manualPhotoPreview');
            const image = document.getElementById('manualPhotoImage');
            const takePhotoButton = document.getElementById('manualPhotoButton');
            const retakeButton = document.getElementById('manualRetakeButton');
            const floatingRetakeButton = document.getElementById('manualFloatingRetake');
            const photoStatus = document.getElementById('manualPhotoStatus');

            if (!input || !preview || !image || !takePhotoButton || !retakeButton || !photoStatus) {
                return;
            }

            const openCameraInput = () => input.click();

            takePhotoButton.addEventListener('click', openCameraInput);
            retakeButton.addEventListener('click', openCameraInput);
            floatingRetakeButton?.addEventListener('click', openCameraInput);

            input.addEventListener('change', () => {
                const [photo] = input.files;

                if (!photo) {
                    return;
                }

                if (!photo.type.startsWith('image/')) {
                    input.value = '';
                    return;
                }

                image.src = URL.createObjectURL(photo);
                preview.classList.add('has-preview');
                photoStatus.textContent = 'Sudah diambil';
                photoStatus.classList.add('is-ready');
                takePhotoButton.innerHTML = `
                    <svg class="manual-photo-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h3l1.5-2h5L15 7h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z" />
                        <circle cx="12" cy="13" r="3.5" />
                    </svg>
                    Ganti Foto`;
                retakeButton.disabled = false;
            });
        })();

        (() => {
            const form = document.getElementById('attendanceForm');
            const typeInput = document.getElementById('jenisPresensi');
            const refreshButton = document.getElementById('refreshGpsButton');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');
            const accuracyInput = document.getElementById('accuracy');
            const locationTitle = document.getElementById('gpsLocationTitle');
            const coordinates = document.getElementById('gpsCoordinates');
            const status = document.getElementById('gpsStatus');
            const mapElement = document.getElementById('attendanceMap');
            const officeLatitude = -2.146435;
            const officeLongitude = 106.101327;
            const maximumDistance = 50;

            if (!form || !typeInput || !refreshButton || !latitudeInput || !longitudeInput || !accuracyInput || !mapElement) {
                return;
            }

            const map = window.L ? L.map(mapElement).setView([officeLatitude, officeLongitude], 17) : null;
            if (map) {
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);
                L.marker([officeLatitude, officeLongitude])
                    .addTo(map)
                    .bindPopup('<strong>PDAM Kota Pangkal Pinang</strong><br>Jl. Bandes No. 200');
                L.circle([officeLatitude, officeLongitude], {
                    radius: maximumDistance,
                    color: '#0875ad',
                    fillColor: '#0875ad',
                    fillOpacity: 0.14,
                }).addTo(map);
            }
            let userMarker = null;

            const distanceInMeters = (latitude, longitude) => {
                const earthRadius = 6371000;
                const latitudeDifference = (latitude - officeLatitude) * Math.PI / 180;
                const longitudeDifference = (longitude - officeLongitude) * Math.PI / 180;
                const a = Math.sin(latitudeDifference / 2) ** 2 +
                    Math.cos(officeLatitude * Math.PI / 180) *
                    Math.cos(latitude * Math.PI / 180) *
                    Math.sin(longitudeDifference / 2) ** 2;

                return earthRadius * 2 * Math.asin(Math.sqrt(a));
            };

            const setGpsStatus = (message, isValid = false) => {
                status.textContent = message;
                status.style.color = isValid ? 'var(--pr-success)' : '';
            };

            const readBestLocation = (attempt = 1, bestPosition = null) => new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition((position) => {
                    const betterPosition = !bestPosition ||
                        position.coords.accuracy < bestPosition.coords.accuracy ?
                        position :
                        bestPosition;

                    if (attempt >= 3 || betterPosition.coords.accuracy <= 30) {
                        resolve(betterPosition);
                        return;
                    }

                    window.setTimeout(() => {
                        readBestLocation(attempt + 1, betterPosition).then(resolve, reject);
                    }, 700);
                }, reject, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
            });

            const updateLocation = () => {
                if (!navigator.geolocation) {
                    setGpsStatus('Perangkat tidak mendukung GPS.');
                    return;
                }

                refreshButton.disabled = true;
                setGpsStatus('Membaca lokasi perangkat...');

                readBestLocation().then((position) => {
                    const {
                        latitude,
                        longitude,
                        accuracy
                    } = position.coords;
                    const distance = distanceInMeters(latitude, longitude);
                    const gpsUncertainty = Math.min(accuracy, 50);
                    const effectiveDistance = Math.max(0, distance - gpsUncertainty);

                    latitudeInput.value = latitude;
                    longitudeInput.value = longitude;
                    accuracyInput.value = accuracy;
                    coordinates.textContent = `${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                    locationTitle.textContent = effectiveDistance <= maximumDistance ?
                        'LOKASI SESUAI AREA KANTOR' :
                        'LOKASI DI LUAR AREA KANTOR';
                    setGpsStatus(
                        `${Math.round(distance)} m dari titik kantor (akurasi ±${Math.round(accuracy)} m)`,
                        effectiveDistance <= maximumDistance
                    );
                    if (map && !userMarker) {
                        userMarker = L.marker([latitude, longitude])
                            .addTo(map)
                            .bindPopup('Lokasi perangkat Anda');
                    } else if (map) {
                        userMarker.setLatLng([latitude, longitude]);
                    }
                    if (map) {
                        map.setView([latitude, longitude], 17);
                    }
                    refreshButton.disabled = false;
                }).catch(() => {
                    latitudeInput.value = '';
                    longitudeInput.value = '';
                    accuracyInput.value = '';
                    setGpsStatus('Lokasi tidak dapat dibaca. Izinkan akses GPS lalu coba lagi.');
                    refreshButton.disabled = false;
                });
            };

            refreshButton.addEventListener('click', updateLocation);
            form.addEventListener('submit', (event) => {
                const selectedButton = document.querySelector(
                    `[data-presensi="${typeInput.value}"]`
                );
                if (selectedButton?.dataset.completed === 'true') {
                    event.preventDefault();
                    selectedButton.click();
                    return;
                }

                if (!latitudeInput.value || !longitudeInput.value || !accuracyInput.value) {
                    event.preventDefault();
                    setGpsStatus('Perbarui lokasi GPS sebelum mengirim presensi.');
                }
            });
        })();
    </script>
@endpush

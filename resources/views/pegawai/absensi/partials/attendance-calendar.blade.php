@php
    $calendarToday = now()->day;
    $calendarOffset = $attendanceCalendarMonth->dayOfWeekIso - 1;
    $calendarDaysInMonth = $attendanceCalendarMonth->daysInMonth;
@endphp

<section class="manual-card manual-calendar-card" aria-labelledby="attendance-calendar-title">
    <div class="manual-section-title">
        <span id="attendance-calendar-title">Tanggal Absensi</span>
        <span class="manual-calendar-today"><i class="fa-solid fa-circle"></i> Hari ini</span>
    </div>

    <h2 class="manual-calendar-month">{{ $attendanceCalendarMonth->locale('id')->translatedFormat('F Y') }}</h2>

    <div class="manual-calendar-grid" role="grid" aria-label="Kalender tanggal absensi">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
            <span class="manual-calendar-weekday" role="columnheader">{{ $weekday }}</span>
        @endforeach

        @for ($emptyDay = 0; $emptyDay < $calendarOffset; $emptyDay++)
            <span class="manual-calendar-empty" aria-hidden="true"></span>
        @endfor

        @for ($calendarDay = 1; $calendarDay <= $calendarDaysInMonth; $calendarDay++)
            @php
                $dayAttendance = $attendanceCalendar->get($calendarDay);
                $isToday = $calendarDay === $calendarToday;
                $isWeekend = $attendanceCalendarMonth->copy()->day($calendarDay)->isWeekend();
                $isComplete = $dayAttendance && filled($dayAttendance->jam_masuk) && filled($dayAttendance->jam_pulang);
            @endphp
            <span
                class="manual-calendar-day {{ $isWeekend ? 'is-weekend' : '' }} {{ $isToday ? 'is-today' : '' }} {{ $dayAttendance ? ($isComplete ? 'is-complete' : 'has-attendance') : '' }}"
                role="gridcell" @if ($isToday) aria-current="date" @endif>
                <span>{{ $calendarDay }}</span>
                @if ($dayAttendance)
                    <i class="{{ $isComplete ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle' }}" aria-hidden="true"></i>
                @elseif ($isToday)
                    <i class="fa-solid fa-circle" aria-hidden="true"></i>
                @endif
            </span>
        @endfor
    </div>

    <div class="manual-calendar-legend" aria-label="Keterangan kalender">
        <span><i class="fa-solid fa-circle-check"></i> Absensi lengkap</span>
        <span><i class="fa-solid fa-circle"></i> Hari ini</span>
    </div>
    <p class="manual-calendar-note">Tanggal presensi mengikuti waktu server dan tidak dapat diubah.</p>
</section>

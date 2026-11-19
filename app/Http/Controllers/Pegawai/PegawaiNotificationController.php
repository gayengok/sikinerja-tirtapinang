<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\PegawaiNotification;
use Illuminate\Http\Request;

class PegawaiNotificationController extends Controller
{
    public function open(Request $request, PegawaiNotification $notification)
    {
        $notification = $request->user()
            ->pegawaiNotifications()
            ->findOrFail($notification->getKey());

        if (! $notification->dibaca_pada) {
            $notification->forceFill(['dibaca_pada' => now()])->save();
        }

        abort_unless(
            str_starts_with($notification->url, '/') &&
            ! str_starts_with($notification->url, '//'),
            404,
            'Tujuan notifikasi tidak valid.'
        );

        return redirect()->to($notification->url);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\RadioProgram;

class RadioController extends Controller
{
    public function index(): void
    {
        $programas    = RadioProgram::allActive();
        $streamUrl    = app_setting('radio_stream_url');
        $stationName  = app_setting('radio_station_name');
        $nowPlaying   = app_setting('radio_now_playing');

        $this->view('public.radio.index', [
            'metaTitle'    => 'Radio En Vivo | ' . app_setting('site_name'),
            'metaDesc'     => 'Escucha la radio en vivo de ' . app_setting('site_name') . '. Programas, horarios y conducciones.',
            'programas'    => $programas,
            'stream_url'   => $streamUrl,
            'station_name' => $stationName,
            'now_playing'  => $nowPlaying,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

class LiveStreamController extends Controller
{
    public function index(): void
    {
        $enabled = (string)app_setting('live_tv_enabled', '1') === '1';
        $rawUrl  = (string)app_setting('live_tv_url', '');
        $title   = (string)app_setting('live_tv_title', 'AMIGOS TV EN VIVO — Las 24 horas');
        $siteName = (string)app_setting('site_name', 'AMIGOS POR EL MUNDO');

        $embedType = 'none';
        $embedSrc  = '';
        $ytId      = '';

        if ($enabled && $rawUrl !== '') {
            if (preg_match('#(?:youtube\.com/embed/|youtu\.be/|youtube\.com/watch\?v=)([A-Za-z0-9_-]{6,})#', $rawUrl, $m)) {
                $embedType = 'youtube';
                $ytId = $m[1];
                $embedSrc = 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1&mute=1&rel=0&modestbranding=1&playsinline=1';
            } elseif (stripos($rawUrl, '.m3u8') !== false) {
                $embedType = 'hls';
                $embedSrc = $rawUrl;
            } else {
                $embedType = 'iframe';
                $embedSrc = $rawUrl;
            }
        }

        $this->view('public.live.index', [
            'metaTitle'  => $title . ' | ' . $siteName,
            'metaDesc'   => 'Transmisión en vivo de ' . $siteName . ' TV. Las 24 horas, las noticias más importantes del Perú y el mundo.',
            'enabled'    => $enabled,
            'rawUrl'     => $rawUrl,
            'embedType'  => $embedType,
            'embedSrc'   => $embedSrc,
            'ytId'       => $ytId,
            'title'      => $title,
            'siteName'   => $siteName,
        ]);
    }
}

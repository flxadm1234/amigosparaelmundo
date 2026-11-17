<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\RadioProgram;
use App\Models\News;
use App\Models\Video;

class HomeController extends Controller
{
    public function index(): void
    {
        $metaTitle = trim(app_setting('seo_home_title'));
        $metaDesc = trim(app_setting('seo_home_desc'));

        $this->view('public.home_amigos', [
            'metaTitle'       => $metaTitle !== '' ? $metaTitle : 'Asociación Civil Amigos por el Mundo | Unidos por un futuro mejor',
            'metaDesc'        => $metaDesc !== '' ? $metaDesc : 'Promovemos solidaridad, educación y cuidado del medio ambiente para construir un mundo mejor.',
            'programas'       => RadioProgram::allActive(),
            'noticias'        => News::lastPublished(3),
            'videos'          => Video::latestPublished(6),
            'amwIsHome'       => true,
            'bodyClass'       => 'amw-body',
        ]);
    }
}

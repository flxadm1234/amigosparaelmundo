<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Page;
use App\Core\Response;

class PagesController extends Controller
{
    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        // Rutas conocidas de la semilla
        $aliases = [
            'nosotros' => 'nosotros',
            'servicios' => 'servicios',
            'equipo' => 'equipo',
            'clientes' => 'clientes',
            'contacto' => 'contacto',
            'privacidad' => 'politica-privacidad',
            'politica-privacidad' => 'politica-privacidad',
        ];
        $slug = $aliases[$slug] ?? $slug;
        $page = Page::findPublishedBySlug($slug);
        if (!$page) {
            Response::notFound('Página no encontrada.');
        }
        $layout = $page['layout'] === 'contact' ? 'contact' : 'default';
        $this->view('public.pages.' . $layout, [
            'metaTitle' => $page['meta_title'] ?? ($page['title'] . ' | ' . app_setting('site_name')),
            'metaDesc'  => $page['meta_desc'] ?? '',
            'page' => $page,
        ]);
    }
}

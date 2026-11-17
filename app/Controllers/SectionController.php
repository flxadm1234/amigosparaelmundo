<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Section;
use App\Models\News;
use App\Core\Response;

class SectionController extends Controller
{
    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $section = Section::findBySlug($slug);
        if (!$section || (int)($section['is_active'] ?? 0) !== 1) {
            Response::notFound('Sección no encontrada.');
        }

        $sectionType = (string)($section['section_type'] ?? 'news');

        if ($sectionType === 'radio') {
            redirect('/radio');
        }
        if ($sectionType === 'videos') {
            redirect('/videos');
        }
        if ($sectionType === 'podcast') {
            redirect('/podcast');
        }

        $perPage = config_val('pagination.per_page_public', 9);
        $page = $this->page();
        $results = News::searchPublic([
            'section_id' => (int)$section['id'],
        ], $page, $perPage);

        $seccionesMenu = Section::allFeatured();

        $this->view('public.section.show', [
            'metaTitle'     => ($section['name'] ?? 'Sección') . ' | ' . app_setting('site_name'),
            'metaDesc'      => $section['description'] ?? '',
            'section'       => $section,
            'noticias'      => $results,
            'seccionesMenu' => $seccionesMenu,
        ]);
    }
}

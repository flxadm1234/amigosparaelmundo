<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\News;
use App\Models\Section;
use App\Models\Tag;
use App\Models\MediaFile;
use App\Core\Response;

class NewsController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'           => trim((string)($_GET['q'] ?? '')),
            'year'        => (int)($_GET['year'] ?? 0),
            'section_id'  => (int)($_GET['section_id'] ?? 0),
            'tag_id'      => (int)($_GET['tag_id'] ?? 0),
            'order'       => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_public', 9);
        $page = $this->page();
        $results = News::searchPublic($filters, $page, $perPage);

        $years        = News::availableYears();
        $seccionesMenu = Section::allFeatured();
        $tags         = Tag::activeOrdered();

        $this->view('public.news.index', [
            'metaTitle'     => 'Noticias | ' . app_setting('site_name'),
            'metaDesc'      => 'Últimas noticias y novedades de ' . app_setting('site_name') . '.',
            'filters'       => $filters,
            'noticias'      => $results,
            'seccionesMenu' => $seccionesMenu,
            'years'         => $years,
            'tags'          => $tags,
            'bodyClass'     => 'amw-body',
            'amwIsHome'     => false,
        ]);
    }

    public function archivo(): void
    {
        $this->index();
    }

    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $noticia = News::findPublishedBySlug($slug);
        if (!$noticia) {
            Response::notFound('Noticia no encontrada o no disponible.');
        }
        News::incrementViews((int)$noticia['id']);
        $tags         = News::tagsFor((int)$noticia['id']);
        $adjuntos     = News::attachments((int)$noticia['id'], 'attachment');
        $galeria      = News::gallery((int)$noticia['id']);

        $related = News::searchPublic([
            'section_id' => (int)$noticia['section_id'],
        ], 1, 4);

        $seccionesMenu = Section::allFeatured();

        $this->view('public.news.show', [
            'metaTitle'     => ($noticia['title'] ?? 'Noticia') . ' | ' . app_setting('site_name'),
            'metaDesc'      => $noticia['excerpt'] ?? strip_tags($noticia['content_html'] ?? ''),
            'noticia'       => $noticia,
            'tags'          => $tags,
            'adjuntos'      => $adjuntos,
            'galeria'       => $galeria,
            'related'       => $related['items'] ?? [],
            'seccionesMenu' => $seccionesMenu,
        ]);
    }
}

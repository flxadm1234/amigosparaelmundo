<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Publication;
use App\Models\Category;
use App\Models\Region;
use App\Models\Tag;
use App\Models\MediaFile;
use App\Core\Response;

class PublicationsController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'           => trim((string)($_GET['q'] ?? '')),
            'year'        => (int)($_GET['year'] ?? 0),
            'category_id' => (int)($_GET['category_id'] ?? 0),
            'region_id'   => (int)($_GET['region_id'] ?? 0),
            'tag_id'      => (int)($_GET['tag_id'] ?? 0),
            'order'       => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_public', 9);
        $page = $this->page();
        $results = Publication::searchPublic($filters, $page, $perPage);

        $years = Publication::availableYears();
        $categories = Category::activeOrdered();
        $regions    = Region::activeOrdered();
        $tags       = Tag::activeOrdered();

        $this->view('public.publications.index', [
            'metaTitle' => 'Publicaciones | ' . app_setting('site_name'),
            'metaDesc'  => 'Explora las encuestas, estudios e informes publicados por AMIGOS POR EL MUNDO: electorales, sociales, educativos y de mercado.',
            'filters'    => $filters,
            'results'    => $results,
            'years'      => $years,
            'categories' => $categories,
            'regions'    => $regions,
            'tags'       => $tags,
        ]);
    }

    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $pub = Publication::findPublishedBySlug($slug);
        if (!$pub) {
            Response::notFound('Publicación no encontrada o no disponible.');
        }
        Publication::incrementViews((int)$pub['id']);
        $tags         = Publication::tagsFor((int)$pub['id']);
        $attachments  = Publication::attachments((int)$pub['id'], 'attachment');
        $gallery      = Publication::gallery((int)$pub['id']);

        $related = Publication::searchPublic([
            'category_id' => (int)$pub['category_id'],
        ], 1, 4);

        $this->view('public.publications.show', [
            'metaTitle' => ($pub['title'] ?? 'Publicación') . ' | ' . app_setting('site_name'),
            'metaDesc'  => $pub['excerpt'] ?? strip_tags($pub['content_html'] ?? ''),
            'pub'        => $pub,
            'tags'       => $tags,
            'attachments' => $attachments,
            'gallery'    => $gallery,
            'related'    => $related['items'] ?? [],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PodcastEpisode;
use App\Core\Response;

class PodcastController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'     => trim((string)($_GET['q'] ?? '')),
            'order' => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_public', 9);
        $page = $this->page();
        $results = PodcastEpisode::searchPublic($filters, $page, $perPage);

        $this->view('public.podcast.index', [
            'metaTitle' => 'Podcast | ' . app_setting('site_name'),
            'metaDesc'  => 'Episodios de podcast de ' . app_setting('site_name') . '. Escucha los programas completos.',
            'filters'   => $filters,
            'episodios' => $results,
        ]);
    }

    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $episodio = PodcastEpisode::findPublishedBySlug($slug);
        if (!$episodio) {
            Response::notFound('Episodio de podcast no encontrado o no disponible.');
        }
        PodcastEpisode::incrementPlays((int)$episodio['id']);

        $this->view('public.podcast.show', [
            'metaTitle' => ($episodio['title'] ?? 'Podcast') . ' | ' . app_setting('site_name'),
            'metaDesc'  => $episodio['description'] ?? '',
            'episodio'  => $episodio,
        ]);
    }
}

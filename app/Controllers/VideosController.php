<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Video;
use App\Core\Response;

class VideosController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q'          => trim((string)($_GET['q'] ?? '')),
            'section_id' => (int)($_GET['section_id'] ?? 0),
            'order'      => (string)($_GET['order'] ?? 'new'),
        ];
        $perPage = config_val('pagination.per_page_public', 9);
        $page = $this->page();
        $results = Video::searchPublic($filters, $page, $perPage);

        $this->view('public.videos.index', [
            'metaTitle' => 'Videos | ' . app_setting('site_name'),
            'metaDesc'  => 'Videos y contenido audiovisual de ' . app_setting('site_name') . '.',
            'filters'   => $filters,
            'videos'    => $results,
        ]);
    }

    public function show(array $params): void
    {
        $slug = (string)($params['slug'] ?? '');
        $video = Video::findPublishedBySlug($slug);
        if (!$video) {
            Response::notFound('Video no encontrado o no disponible.');
        }
        Video::incrementViews((int)$video['id']);

        $thumbImg = !empty($video['thumbnail_path'])
            ? img_fallback((string)$video['thumbnail_path'], '16x9', (string)($video['title'] ?? ''), 'video-cover-'.((int)($video['id'] ?? 0)))
            : img_placeholder('16x9', (string)($video['title'] ?? 'Video'), 'video-cover-'.((int)($video['id'] ?? 0)));

        $embedHtml = '';
        if (method_exists(\App\Models\Video::class, 'getEmbedHtml')) {
            $embedHtml = (string)\App\Models\Video::getEmbedHtml($video);
        }
        if ($embedHtml === '' && function_exists('video_render_embed_html')) {
            $embedHtml = (string)video_render_embed_html($video, $thumbImg);
        }

        $latestVideos = method_exists(\App\Models\Video::class, 'latestPublished')
            ? \App\Models\Video::latestPublished(8)
            : [];
        $videoId = (int)($video['id'] ?? 0);
        $otrosVideos = array_values(array_filter((array)$latestVideos, function ($item) use ($videoId) {
            return ((int)($item['id'] ?? 0)) !== $videoId;
        }));
        $relacionados = array_slice($otrosVideos, 0, 4);
        $masVideos   = array_slice($otrosVideos, 0, 6);

        $this->view('public.videos.show', [
            'metaTitle'  => ($video['meta_title'] ?? $video['title'] ?? 'Video') . ' | ' . app_setting('site_name'),
            'metaDesc'   => $video['meta_desc'] ?? ($video['description'] ?? ''),
            'video'      => $video,
            'embed_html' => $embedHtml,
            'thumb_img'  => $thumbImg,
            'relacionados' => $relacionados,
            'mas_videos'   => $masVideos,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class MediaFile extends Model
{
    protected static string $table = 'media_files';

    public static function findByStoredName(string $stored_name): ?array
    {
        return self::findBy('stored_name', $stored_name);
    }

    public static function forNews(int $newsId): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE news_id = ? ORDER BY sort_order ASC, id ASC',
            [$newsId]
        );
    }

    public static function forVideo(int $videoId): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE video_id = ? ORDER BY sort_order ASC, id ASC',
            [$videoId]
        );
    }

    public static function forPodcast(int $podcastId): array
    {
        return self::queryRows(
            'SELECT * FROM media_files WHERE podcast_id = ? ORDER BY sort_order ASC, id ASC',
            [$podcastId]
        );
    }

    public static function forPublication(int $publicationId): array
    {
        return self::forNews($publicationId);
    }
}

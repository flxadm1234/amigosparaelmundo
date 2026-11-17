<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ad;

class AdsController extends Controller
{
    public function incrementView(array $p): void
    {
        $id = (int)($p['id'] ?? 0);
        if ($id > 0) Ad::incrementViews($id);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
    }

    public function incrementClick(array $p): void
    {
        $id = (int)($p['id'] ?? 0);
        $ad = null;
        if ($id > 0) {
            $ad = Ad::findActiveById($id);
            if ($ad) Ad::incrementClicks($id);
        }
        $to = $_GET['to'] ?? '/';
        if ($ad && !empty($ad['link_url'])) $to = $ad['link_url'];
        if (!preg_match('#^https?://|^/#i', $to)) $to = '/';
        header('Location: ' . $to, true, 302);
        exit;
    }

    public function dismiss(array $p): void
    {
        $id = (int)($p['id'] ?? 0);
        $pos = $_GET['position'] ?? '';
        $ttl = (int)($_GET['ttl'] ?? 3600);
        $ttl = max(60, min(86400 * 30, $ttl));
        $key = 'dismissed_ads';
        $list = [];
        if (!empty($_COOKIE[$key])) {
            $json = json_decode($_COOKIE[$key], true);
            if (is_array($json)) $list = $json;
        }
        $now = time();
        $entry = null;
        if ($id > 0) $entry = 'id_' . $id;
        elseif ($pos !== '') $entry = 'pos_' . $pos;
        if ($entry) {
            $list[$entry] = $now + $ttl;
            foreach ($list as $k => $exp) if ($exp < $now) unset($list[$k]);
            setcookie($key, json_encode($list), [
                'expires'  => $now + 86400 * 30,
                'path'     => '/',
                'httponly' => false,
                'samesite' => 'Lax',
            ]);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'dismissed' => $entry ?: 'none']);
    }

    public static function isDismissed(int $adId, string $position): bool
    {
        if (empty($_COOKIE['dismissed_ads'])) return false;
        $list = json_decode($_COOKIE['dismissed_ads'], true);
        if (!is_array($list)) return false;
        $now = time();
        $byId  = !empty($list['id_' . $adId]) && (int)$list['id_' . $adId] > $now;
        $byPos = !empty($list['pos_' . $position]) && (int)$list['pos_' . $position] > $now;
        return $byId || $byPos;
    }
}

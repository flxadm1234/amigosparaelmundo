<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\SiteSetting;
use App\Models\AuditLog;
use App\Services\UploadService;

class AdminSettingsController extends Controller
{
    public function index(): void
    {
        Auth::requireRole('admin');

        $settings = SiteSetting::allAsMap();
        $this->view('admin.settings.index', [
            'metaTitle' => 'Configuración del sitio',
            'settings'  => $settings,
        ]);
    }

    public function update(): void
    {
        Auth::requireRole('admin');

        $keys = [
            'site_name','site_slogan','site_description',
            'seo_home_title','seo_home_desc','seo_default_keywords',
            'footer_address','footer_phone','footer_email','copyright_text',
            'home_hero_count','home_mas_noticias_count','news_per_page',
            'site_primary_color','site_secondary_color','site_accent_color',
            'site_blue_dark','site_green_dark',
            'site_header_bg','site_header_text',
            'site_breaking_bg','site_breaking_text',
            'site_footer_bg','site_footer_text',
            // Keys legacy (backward compat — se aceptan ambos nombres POST)
            'header_bg_color','header_text_color','footer_bg_color','footer_text_color',
            'contact_phones','contact_emails','contact_address','contact_hours',
            'social_facebook','social_twitter','social_linkedin','social_instagram','social_youtube',
            'social_tiktok','social_whatsapp','social_telegram','social_whatsapp_channel_url',
            'seo_title','seo_description','seo_keywords','seo_og_image','seo_twitter_handle',
            'footer_about','home_hero_title','home_hero_subtitle','analytics_code',
            'site_logo','site_favicon',
            'site_ruc','site_sunarp',
            'site_office_address',
            'site_logo_has_wordmark','home_hero_bg_image','home_hero_bg_position',
            'home_hero_overlay_opacity','site_color_navy','site_color_cta_teal',
            'site_logo_navbar','site_logo_hero_home','hero_resolucion_text',
            'site_logo_dark_bg',
            'site_brand_kicker',
            'header_logo_kicker_size','header_logo_name_size','header_logo_slogan_size',
            'hero_logo_size',
            'home_hero_eyebrow','home_hero_highlight','home_hero_button_text','home_hero_button_url',
            'home_programs_eyebrow','home_programs_title','home_programs_subtitle',
            'home_program_card_link','home_program_card_url','home_programs_empty',
            'home_nav_about_text','home_nav_about_url','home_nav_programs_text','home_nav_programs_url',
            'home_nav_projects_text','home_nav_projects_url','home_nav_news_text','home_nav_news_url',
            'home_nav_partners_text','home_nav_partners_url','home_nav_volunteer_text','home_nav_volunteer_url',
            'home_nav_contact_text','home_nav_contact_url',
            'home_mission_label','home_mission_title','home_mission_text',
            'home_vision_label','home_vision_title','home_vision_text','home_quote',
            'home_join_eyebrow','home_join_title','home_join_text','home_join_button_text','home_join_button_url',
            'home_contact_title','home_social_title',
            'radio_stream_url','radio_station_name','radio_now_playing',
            // Rediseño RPP 2026-09-28: TV en vivo, Banners, Promo, Menú móvil
            'live_tv_url','live_tv_title','live_tv_enabled',
            'banner_live_enabled','banner_live_url','banner_live_text','banner_live_bg_color',
            'banner_featured_enabled','banner_featured_url','banner_featured_text','banner_featured_color',
            'promo_block1_title','promo_block1_url',
            'menu_mobile_sections_enabled','header_topbar_bg',
            'breaking_enabled','breaking_custom_items',
            'site_title_animation_enabled','site_title_animated',
        ];
        // Seteo normal. También mapeo de nombres duplicados: $_POST[key] para mantener compatibilidad keys antiguas y nuevas.
        $aliasMap = [
            'header_bg_color'   => 'site_header_bg',
            'header_text_color'  => 'site_header_text',
            'footer_bg_color'   => 'site_footer_bg',
            'footer_text_color' => 'site_footer_text',
        ];
        foreach ($keys as $k) {
            if (!array_key_exists($k, $_POST)) {
                continue;
            }
            $val = trim((string)($_POST[$k] ?? ''));
            SiteSetting::set($k, $val);
            // Si la key antigua se setea y existe alias moderno, replicar valor a la moderna también
            if (isset($aliasMap[$k]) && $val !== '') {
                SiteSetting::set($aliasMap[$k], $val);
            }
        }
        // Reverso: si enviaron keys modernas y también setear keys legacy (posterior a legacy sobreescribe)
        $aliasMapRev = array_flip($aliasMap);
        foreach (['site_header_bg','site_header_text','site_footer_bg','site_footer_text'] as $modK) {
            if (isset($_POST[$modK]) && trim((string)$_POST[$modK]) !== '') {
                $val = trim((string)$_POST[$modK]);
                SiteSetting::set($modK, $val);
                if (isset($aliasMapRev[$modK])) {
                    SiteSetting::set($aliasMapRev[$modK], $val);
                }
            }
        }
        $uploader = new UploadService();
        $user = Auth::user();
        $userId = (int)$user['id'];

        if (isset($_FILES['site_logo_file']) && $_FILES['site_logo_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['site_logo_file'], 'settings', 'image', null, $userId, 'Logo Asociación');
            if ($res['ok']) {
                SiteSetting::set('site_logo', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['logo_file'], 'settings', 'other', null, $userId, 'Logo');
            if ($res['ok']) {
                SiteSetting::set('site_logo', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['logo_navbar_file']) && $_FILES['logo_navbar_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['logo_navbar_file'], 'settings', 'image', null, $userId, 'Logo Navbar');
            if ($res['ok']) {
                SiteSetting::set('site_logo_navbar', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['hero_logo_file']) && $_FILES['hero_logo_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['hero_logo_file'], 'settings', 'image', null, $userId, 'Logo Hero');
            if ($res['ok']) {
                SiteSetting::set('site_logo_hero_home', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['site_favicon_file']) && $_FILES['site_favicon_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['site_favicon_file'], 'settings', 'other', null, $userId, 'Favicon');
            if ($res['ok']) {
                SiteSetting::set('site_favicon', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['hero_bg_file']) && $_FILES['hero_bg_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['hero_bg_file'], 'site', 'image', null, $userId, 'Hero BG');
            if ($res['ok']) {
                SiteSetting::set('home_hero_bg_image', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['logo_dark_bg_file']) && $_FILES['logo_dark_bg_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['logo_dark_bg_file'], 'settings', 'image', null, $userId, 'Logo Dark BG');
            if ($res['ok']) {
                SiteSetting::set('site_logo_dark_bg', $res['data']['stored_path']);
            }
        }
        if (isset($_FILES['seo_og_image_file']) && $_FILES['seo_og_image_file']['error'] === UPLOAD_ERR_OK) {
            $res = $uploader->handleUpload($_FILES['seo_og_image_file'], 'settings', 'image', null, $userId, 'SEO OG Image');
            if ($res['ok']) {
                SiteSetting::set('seo_og_image', $res['data']['stored_path']);
            }
        }
        AuditLog::record('update', 'site_settings', 'general');
        $this->withSuccess('Configuración guardada correctamente.', '/admin/ajustes');
    }
}

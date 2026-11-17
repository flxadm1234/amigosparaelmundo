-- ==============================================================
-- ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO
-- Esquema de Base de Datos (sitio institucional + CMS)
-- Compatible: MySQL 8.x / MariaDB 10.5+  (InnoDB, utf8mb4)
-- ==============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------
-- roles
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(50)  NOT NULL,
  `slug`        VARCHAR(50)  NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- users
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role_id`       INT UNSIGNED NOT NULL,
  `status`        TINYINT      NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `created_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role_id`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- site_settings   (clave / valor)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(150) NOT NULL,
  `value_text`  TEXT         DEFAULT NULL,
  `updated_at`  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- sections  (secciones editoriales del menú)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `sections`;
CREATE TABLE `sections` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(100) NOT NULL,
  `slug`        VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `section_type` VARCHAR(40) NOT NULL DEFAULT 'news' COMMENT 'news|radio|videos|podcast',
  `icon`        VARCHAR(60)  DEFAULT NULL,
  `color`       VARCHAR(20)  DEFAULT NULL,
  `is_featured` TINYINT     NOT NULL DEFAULT 0 COMMENT '1=mostrar en menú principal',
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `is_active`   TINYINT     NOT NULL DEFAULT 1,
  `created_at`  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sections_slug` (`slug`),
  KEY `idx_sections_active` (`is_active`),
  KEY `idx_sections_type` (`section_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- tags  (etiquetas/tags para noticias)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(80) NOT NULL,
  `slug`       VARCHAR(100) NOT NULL,
  `is_active`  TINYINT     NOT NULL DEFAULT 1,
  `created_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- news  (publicaciones/noticias del portal)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`                VARCHAR(250) NOT NULL,
  `slug`                 VARCHAR(270) NOT NULL,
  `excerpt`              VARCHAR(500) DEFAULT NULL,
  `content_html`         MEDIUMTEXT   DEFAULT NULL,
  `section_id`           INT UNSIGNED DEFAULT NULL,
  `cover_image_path`     VARCHAR(255) DEFAULT NULL,
  `cover_caption`        VARCHAR(255) DEFAULT NULL,
  `is_featured`          TINYINT     NOT NULL DEFAULT 0 COMMENT '1=noticia destacada hero',
  `is_breaking`          TINYINT     NOT NULL DEFAULT 0 COMMENT '1=noticia de última hora',
  `publish_date`         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_published`         TINYINT     NOT NULL DEFAULT 0,
  `meta_title`           VARCHAR(180) DEFAULT NULL,
  `meta_desc`            VARCHAR(300) DEFAULT NULL,
  `meta_keywords`        VARCHAR(255) DEFAULT NULL,
  `views_count`          INT UNSIGNED NOT NULL DEFAULT 0,
  `source_name`          VARCHAR(150) DEFAULT NULL,
  `source_url`           VARCHAR(255) DEFAULT NULL,
  `author_name`          VARCHAR(150) DEFAULT NULL,
  `created_by`           INT UNSIGNED DEFAULT NULL,
  `updated_by`           INT UNSIGNED DEFAULT NULL,
  `created_at`           DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_news_slug` (`slug`),
  KEY `idx_news_published` (`is_published`),
  KEY `idx_news_publish_date` (`publish_date`),
  KEY `idx_news_section` (`section_id`),
  KEY `idx_news_featured` (`is_featured`),
  KEY `idx_news_breaking` (`is_breaking`),
  CONSTRAINT `fk_news_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- news_tags  (relación N:M noticias-etiquetas)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `news_tags`;
CREATE TABLE `news_tags` (
  `news_id` INT UNSIGNED NOT NULL,
  `tag_id`  INT UNSIGNED NOT NULL,
  PRIMARY KEY (`news_id`, `tag_id`),
  KEY `idx_nt_tag` (`tag_id`),
  CONSTRAINT `fk_nt_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_nt_tag`  FOREIGN KEY (`tag_id`)  REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- media_files  (adjuntos + galería por noticia / video / podcast)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `media_files`;
CREATE TABLE `media_files` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `news_id`        INT UNSIGNED DEFAULT NULL,
  `video_id`       INT UNSIGNED DEFAULT NULL,
  `podcast_id`     INT UNSIGNED DEFAULT NULL,
  `file_kind`      VARCHAR(40)  NOT NULL DEFAULT 'attachment' COMMENT 'attachment|gallery|cover|thumbnail|audio|video|other',
  `file_label`     VARCHAR(150) DEFAULT NULL,
  `original_name`  VARCHAR(255) NOT NULL,
  `stored_name`    VARCHAR(255) NOT NULL,
  `stored_path`    VARCHAR(255) NOT NULL,
  `mime_type`      VARCHAR(120) DEFAULT NULL,
  `size_bytes`     BIGINT UNSIGNED DEFAULT 0,
  `file_ext`       VARCHAR(20)  DEFAULT NULL,
  `sort_order`     INT          NOT NULL DEFAULT 0,
  `created_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_news` (`news_id`),
  KEY `idx_media_video` (`video_id`),
  KEY `idx_media_podcast` (`podcast_id`),
  KEY `idx_media_kind` (`file_kind`),
  CONSTRAINT `fk_media_news`    FOREIGN KEY (`news_id`)    REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- flyers  (banners destacados home - carrusel)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `flyers`;
CREATE TABLE `flyers` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(200) DEFAULT NULL,
  `subtitle`        VARCHAR(255) DEFAULT NULL,
  `image_path`      VARCHAR(255) NOT NULL,
  `link_url`        VARCHAR(255) DEFAULT NULL,
  `link_target`     VARCHAR(20)  NOT NULL DEFAULT '_self' COMMENT '_self|_blank',
  `position`        VARCHAR(50)  NOT NULL DEFAULT 'home-carousel' COMMENT 'home-carousel|home-grid|sidebar|section-top',
  `start_at`        DATETIME     DEFAULT NULL,
  `end_at`          DATETIME     DEFAULT NULL,
  `is_active`       TINYINT      NOT NULL DEFAULT 1,
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `clicks_count`    INT UNSIGNED NOT NULL DEFAULT 0,
  `views_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_flyers_active` (`is_active`),
  KEY `idx_flyers_position` (`position`),
  KEY `idx_flyers_dates` (`start_at`,`end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- videos  (modulo de videos - embed o archivo subido)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `videos`;
CREATE TABLE `videos` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(250) NOT NULL,
  `slug`            VARCHAR(270) NOT NULL,
  `description`     TEXT         DEFAULT NULL,
  `section_id`      INT UNSIGNED DEFAULT NULL,
  `video_type`      VARCHAR(30)  NOT NULL DEFAULT 'embed' COMMENT 'embed|upload|youtube|vimeo',
  `embed_url`       VARCHAR(500) DEFAULT NULL,
  `video_path`      VARCHAR(255) DEFAULT NULL,
  `thumbnail_path`  VARCHAR(255) DEFAULT NULL,
  `duration`        VARCHAR(20)  DEFAULT NULL,
  `publish_date`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_published`    TINYINT      NOT NULL DEFAULT 0,
  `views_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_videos_slug` (`slug`),
  KEY `idx_videos_published` (`is_published`),
  KEY `idx_videos_section` (`section_id`),
  KEY `idx_videos_date` (`publish_date`),
  CONSTRAINT `fk_videos_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- podcast_episodes  (modulo de podcast)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `podcast_episodes`;
CREATE TABLE `podcast_episodes` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(250) NOT NULL,
  `slug`            VARCHAR(270) NOT NULL,
  `description`     TEXT         DEFAULT NULL,
  `program_name`    VARCHAR(150) DEFAULT NULL,
  `host_name`       VARCHAR(150) DEFAULT NULL,
  `episode_number`  INT UNSIGNED DEFAULT NULL,
  `season_number`   INT UNSIGNED DEFAULT NULL,
  `audio_type`      VARCHAR(30)  NOT NULL DEFAULT 'upload' COMMENT 'upload|embed|spotify',
  `audio_path`      VARCHAR(255) DEFAULT NULL,
  `embed_url`       VARCHAR(500) DEFAULT NULL,
  `cover_image_path` VARCHAR(255) DEFAULT NULL,
  `duration`        VARCHAR(20)  DEFAULT NULL,
  `publish_date`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_published`    TINYINT      NOT NULL DEFAULT 0,
  `plays_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by`      INT UNSIGNED DEFAULT NULL,
  `updated_by`      INT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_podcast_slug` (`slug`),
  KEY `idx_podcast_published` (`is_published`),
  KEY `idx_podcast_date` (`publish_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- radio_programs  (programas de radio - opcional)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `radio_programs`;
CREATE TABLE `radio_programs` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(200) NOT NULL,
  `slug`            VARCHAR(220) NOT NULL,
  `description`     TEXT         DEFAULT NULL,
  `hosts`           VARCHAR(255) DEFAULT NULL,
  `schedule_text`   VARCHAR(255) DEFAULT NULL,
  `cover_image_path` VARCHAR(255) DEFAULT NULL,
  `icon_key`        VARCHAR(40)  NOT NULL DEFAULT 'leaf',
  `sort_order`      INT          NOT NULL DEFAULT 0,
  `is_active`       TINYINT      NOT NULL DEFAULT 1,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_radio_programs_slug` (`slug`),
  KEY `idx_rp_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- ads  (publicidad)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`         VARCHAR(200) NOT NULL,
  `position`      VARCHAR(50)  NOT NULL COMMENT 'top-banner|bottom-sticky|sidebar-right|sidebar-left|news-inline|interstitial|section-top',
  `ad_kind`       VARCHAR(30)  NOT NULL DEFAULT 'image' COMMENT 'image|html|code',
  `image_path`    VARCHAR(255) DEFAULT NULL,
  `link_url`      VARCHAR(500) DEFAULT NULL,
  `link_target`   VARCHAR(20)  NOT NULL DEFAULT '_blank',
  `alt_text`      VARCHAR(255) DEFAULT NULL,
  `html_code`     MEDIUMTEXT   DEFAULT NULL,
  `start_at`      DATETIME     DEFAULT NULL,
  `end_at`        DATETIME     DEFAULT NULL,
  `max_views`     INT UNSIGNED DEFAULT NULL,
  `max_clicks`    INT UNSIGNED DEFAULT NULL,
  `views_count`   INT UNSIGNED NOT NULL DEFAULT 0,
  `clicks_count`  INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`     TINYINT      NOT NULL DEFAULT 1,
  `sort_order`    INT          NOT NULL DEFAULT 0,
  `created_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ads_position` (`position`),
  KEY `idx_ads_active`   (`is_active`),
  KEY `idx_ads_dates`    (`start_at`,`end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- pages  (paginas estáticas)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`         VARCHAR(160) NOT NULL,
  `title`        VARCHAR(200) NOT NULL,
  `content_html` MEDIUMTEXT   DEFAULT NULL,
  `meta_title`   VARCHAR(180) DEFAULT NULL,
  `meta_desc`    VARCHAR(300) DEFAULT NULL,
  `is_published` TINYINT     NOT NULL DEFAULT 1,
  `layout`       VARCHAR(60)  DEFAULT 'default',
  `created_by`   INT UNSIGNED DEFAULT NULL,
  `updated_by`   INT UNSIGNED DEFAULT NULL,
  `created_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pages_slug` (`slug`),
  KEY `idx_pages_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- audit_logs  (historial de acciones)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED DEFAULT NULL,
  `action`        VARCHAR(50)  NOT NULL COMMENT 'create|update|delete|publish|login|logout|upload',
  `entity`        VARCHAR(80)  DEFAULT NULL,
  `entity_id`     VARCHAR(60)  DEFAULT NULL,
  `ip_address`    VARCHAR(60)  DEFAULT NULL,
  `old_data`      MEDIUMTEXT   DEFAULT NULL COMMENT 'JSON before',
  `new_data`      MEDIUMTEXT   DEFAULT NULL COMMENT 'JSON after',
  `metadata_text` TEXT         DEFAULT NULL,
  `created_at`    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user`    (`user_id`),
  KEY `idx_audit_action`  (`action`),
  KEY `idx_audit_entity`  (`entity`, `entity_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------
-- login_attempts (rate limit login)
-- --------------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip_address`    VARCHAR(60)  NOT NULL,
  `email`         VARCHAR(180) DEFAULT NULL,
  `attempts`      INT UNSIGNED NOT NULL DEFAULT 1,
  `locked_until`  DATETIME     DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_login_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================
-- DATOS SEED (mínimos para arrancar el sitio)
-- ==============================================================

-- ----- ROLES -----
INSERT INTO `roles` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrador', 'admin', 'Acceso total al sistema'),
(2, 'Editor',        'editor', 'Gestión de contenido: noticias, flyers, multimedia');

-- ----- USUARIOS -----
-- Administrador: admin@amigosparaelmundo.org / Amigos2026
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role_id`, `status`) VALUES
(1, 'Administrador', 'admin@amigosparaelmundo.org', '$2y$10$pv36cs5neU4pRrLlnqAa/.SASEUlBIbtqJ8FAfeztzdwqtVDotW4.', 1, 1);

-- ----- SECCIONES PRINCIPALES (menú tipo RPP) -----
INSERT INTO `sections` (`id`, `name`, `slug`, `section_type`, `icon`, `is_featured`, `sort_order`, `is_active`) VALUES
(1,  'Últimas noticias', 'ultimas-noticias',   'news',  '📰', 1, 1, 1),
(2,  'Política',         'politica',           'news',  '🏛️', 1, 2, 1),
(3,  'Lima',             'lima',               'news',  '🏙️', 1, 3, 1),
(4,  'Perú',             'peru',               'news',  '🇵🇪', 1, 4, 1),
(5,  'Fútbol',           'futbol',             'news',  '⚽', 1, 5, 1),
(6,  'Deportes',         'deportes',           'news',  '🏆', 1, 6, 1),
(7,  'Mundo',            'mundo',              'news',  '🌍', 1, 7, 1),
(8,  'Economía',         'economia',           'news',  '💰', 1, 8, 1),
(9,  'Entretenimiento',  'entretenimiento',    'news',  '🎬', 1, 9, 1),
(10, 'Cultura',          'cultura',            'news',  '🎭', 1, 10, 1),
(11, 'Tecnología',       'tecnologia',         'news',  '💻', 1, 11, 1),
(12, 'Radio',            'radio',              'radio', '📻', 1, 12, 1),
(13, 'Videos',           'videos',             'videos','🎥', 1, 13, 1),
(14, 'Podcast',          'podcast',            'podcast','🎙️', 1, 14, 1);

-- ----- TAGS iniciales -----
INSERT INTO `tags` (`id`, `name`, `slug`, `is_active`) VALUES
(1, 'Última hora', 'ultima-hora', 1),
(2, 'Destacado',   'destacado',   1),
(3, 'Nacional',    'nacional',    1),
(4, 'Internacional','internacional',1);

-- ----- SETTINGS GLOBALES (Asociación Amigos por el Mundo) -----
INSERT INTO `site_settings` (`setting_key`, `value_text`) VALUES
('site_name',             'ASOCIACIÓN CIVIL AMIGOS POR EL MUNDO'),
('site_slogan',           'Juntos podemos transformar vidas y proteger nuestro planeta'),
('site_description',      'Promovemos solidaridad, educación y cuidado del medio ambiente para construir un mundo mejor.'),
('site_logo',             ''),
('site_logo_dark',        ''),
('site_favicon',          ''),
('site_primary_color',    '#087B43'),
('site_secondary_color',  '#85C440'),
('site_accent_color',     '#F3BF24'),
('site_blue_dark',        '#123B2B'),
('site_green_dark',       '#075B38'),
('site_color_navy',       '#075B38'),
('site_color_cta_teal',   '#85C440'),
('site_header_bg',        '#087B43'),
('site_header_text',      '#FFFFFF'),
('site_breaking_bg',      '#E11D48'),
('site_breaking_text',    '#FFFFFF'),
('site_footer_bg',        '#092C21'),
('site_footer_text',      '#FFFFFF'),
('home_hero_count',       '6'),
('home_mas_noticias_count', '12'),
('home_flyers_count',      '6'),
('news_per_page',         '12'),
('radio_stream_url',      ''),
('radio_station_name',    'Amigos por el Mundo'),
('radio_now_playing',     'Enlace 24 horas'),
('social_facebook',       ''),
('social_twitter',        ''),
('social_instagram',      ''),
('social_youtube',        ''),
('social_tiktok',         ''),
('social_whatsapp',       ''),
('seo_home_title',        'Asociación Civil Amigos por el Mundo | Unidos por un futuro mejor'),
('seo_home_desc',         'Promovemos solidaridad, educación y cuidado del medio ambiente para construir un mundo mejor.'),
('seo_default_keywords',  'asociación civil, solidaridad, educación, medio ambiente, voluntariado, proyectos sociales'),
('footer_about',          'Una asociación comprometida con las personas, la educación y el cuidado de nuestro planeta.'),
('footer_address',        'Iquitos, Perú'),
('footer_phone',          ''),
('footer_email',          ''),
('copyright_text',        '© 2026 Asociación Civil Amigos por el Mundo. Todos los derechos reservados.'),
('site_brand_kicker',     'ASOCIACIÓN CIVIL'),
('home_hero_eyebrow',     'Una comunidad que transforma'),
('home_hero_title',       'Construyendo un'),
('home_hero_highlight',   'mundo mejor'),
('home_hero_subtitle',    'Con solidaridad, educación y cuidado del medio ambiente. Juntos podemos transformar vidas y proteger nuestro planeta.'),
('home_hero_button_text', 'Conoce nuestros programas'),
('home_hero_button_url',  '#programas'),
('home_hero_bg_image',    '/uploads/site/20260908222819_c33134b7_Gemini_Generated_Image_3j2tor3j2tor3j2t.png'),
('home_programs_title',   'Nuestros Programas'),
('home_programs_subtitle','Acciones que generan un impacto positivo'),
('home_mission_title',    'Nuestra Misión'),
('home_mission_text',     'Trabajar por el bienestar social, la educación y la protección del medio ambiente.'),
('home_vision_title',     'Nuestra Visión'),
('home_vision_text',      'Ser una organización reconocida por impulsar comunidades solidarias, saludables y sostenibles.'),
('home_quote',            'Juntos podemos hacer la diferencia'),
('home_join_title',       'Súmate al cambio'),
('home_join_text',        'Cada gesto cuenta. Participa como voluntario, apoya nuestros proyectos o comparte nuestra causa.');

-- ----- PROGRAMAS DE IMPACTO DE EJEMPLO -----
INSERT INTO `radio_programs` (`id`, `name`, `slug`, `description`, `hosts`, `schedule_text`, `icon_key`, `sort_order`, `is_active`) VALUES
(1, 'Premio', 'premio', 'Reconocemos iniciativas y personas que generan cambios positivos en su comunidad.', 'Reconocimiento comunitario', 'Convocatorias abiertas', 'sun', 1, 1),
(2, 'Sembrando Vida', 'sembrando-vida', 'Promovemos la educación ambiental, la reforestación y el cuidado de nuestros ecosistemas.', 'Medio ambiente', 'Acciones permanentes', 'leaf', 2, 1),
(3, 'Manos que Ayudan', 'manos-que-ayudan', 'Organizamos acciones solidarias para acompañar a familias y personas que necesitan apoyo.', 'Solidaridad y apoyo', 'Acciones comunitarias', 'hands', 3, 1),
(4, 'Generación con Futuro', 'generacion-con-futuro', 'Impulsamos educación y oportunidades para niñas, niños y jóvenes.', 'Educación', 'Nuevas oportunidades', 'book', 4, 1),
(5, 'Embajadores de la Solidaridad', 'embajadores-de-la-solidaridad', 'Sumamos voluntarios, aliados y organizaciones en favor del bien común.', 'Participación ciudadana', 'Red de voluntariado', 'heart', 5, 1),
(6, 'Vida Animal', 'vida-animal', 'Trabajamos por la protección y el bienestar de los animales de nuestra comunidad.', 'Protección y bienestar', 'Cuidado responsable', 'paw', 6, 1);

SET FOREIGN_KEY_CHECKS = 1;
-- Fin del esquema - Asociación Civil Amigos por el Mundo

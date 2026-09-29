-- Genere le 2026-09-29 07:49 par `php artisan ubdf:prod:comparer-structures`
-- Ancienne base : ub2020 (29 tables) — nouvelle base : 2026_ubdf (39 tables)

-- A executer sur une COPIE de la base ancienne, jamais sur ub2020.
-- Les donnees se reprennent ensuite par `php artisan ubdf:migrate-legacy --tous`.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `accueil_blocs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accueil_blocs_cle_unique` (`cle`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint(20) unsigned DEFAULT NULL,
  `admin_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_activities_admin_id_foreign` (`admin_id`),
  KEY `admin_activities_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `admin_activities_created_at_index` (`created_at`),
  CONSTRAINT `admin_activities_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `billing_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `siret` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `siren` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_form` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `naf_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FR',
  `admin_state` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `established_on` date DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billing_profiles_user_id_unique` (`user_id`),
  KEY `billing_profiles_siret_index` (`siret`),
  KEY `billing_profiles_siren_index` (`siren`),
  CONSTRAINT `billing_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_articles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `legacy_source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `book_section_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `body_blocks` json DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_articles_legacy_source_legacy_id_unique` (`legacy_source`,`legacy_id`),
  KEY `book_articles_book_section_id_foreign` (`book_section_id`),
  KEY `book_articles_user_id_status_index` (`user_id`,`status`),
  KEY `book_articles_status_index` (`status`),
  CONSTRAINT `book_articles_book_section_id_foreign` FOREIGN KEY (`book_section_id`) REFERENCES `book_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_articles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2288 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `legacy_source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `kind` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'news',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `is_private` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `page_order` json DEFAULT NULL,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_sections_legacy_source_legacy_id_unique` (`legacy_source`,`legacy_id`),
  KEY `book_sections_parent_id_foreign` (`parent_id`),
  KEY `book_sections_user_id_is_published_position_index` (`user_id`,`is_published`,`position`),
  KEY `book_sections_user_id_kind_position_index` (`user_id`,`kind`,`position`),
  CONSTRAINT `book_sections_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `book_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `book_sections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=418 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description_mobile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience` text COLLATE utf8mb4_unicode_ci,
  `footer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mdl_default',
  `theme_settings` json DEFAULT NULL,
  `theme_texts` json DEFAULT NULL,
  `theme_home_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_color` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `background_mode` tinyint(3) unsigned DEFAULT NULL,
  `is_centered` tinyint(1) NOT NULL DEFAULT '0',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` text COLLATE utf8mb4_unicode_ci,
  `custom_js` text COLLATE utf8mb4_unicode_ci,
  `analytics_id` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diffuse_ub` tinyint(1) NOT NULL DEFAULT '0',
  `diffuse_web` tinyint(1) NOT NULL DEFAULT '0',
  `diffuse_newsletter` tinyint(1) NOT NULL DEFAULT '0',
  `diffuse_availability` tinyint(1) NOT NULL DEFAULT '0',
  `legacy_payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `book_settings_user_id_unique` (`user_id`),
  UNIQUE KEY `book_settings_legacy_id_unique` (`legacy_id`),
  KEY `book_settings_theme_index` (`theme`),
  CONSTRAINT `book_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `brand` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'newsletter',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaigns_legacy_id_unique` (`legacy_id`),
  KEY `campaigns_brand_index` (`brand`),
  KEY `campaigns_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `campaign_sends` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `payload` json DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_sends_user_id_foreign` (`user_id`),
  KEY `campaign_sends_campaign_id_status_index` (`campaign_id`,`status`),
  KEY `campaign_sends_email_index` (`email`),
  KEY `campaign_sends_status_index` (`status`),
  CONSTRAINT `campaign_sends_campaign_id_foreign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaign_sends_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_plural` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cms_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `translation_group` int(10) unsigned DEFAULT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `parent_slug` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_pages_slug_locale_unique` (`slug`,`locale`),
  UNIQUE KEY `cms_pages_legacy_id_unique` (`legacy_id`),
  KEY `cms_pages_translation_group_index` (`translation_group`),
  KEY `cms_pages_parent_slug_index` (`parent_slug`),
  KEY `cms_pages_published_at_index` (`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cms_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `slug` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cms_posts_slug_locale_unique` (`slug`,`locale`),
  UNIQUE KEY `cms_posts_legacy_id_unique` (`legacy_id`),
  KEY `cms_posts_published_at_index` (`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'contact',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_detail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selector` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `book_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_spam` tinyint(1) NOT NULL DEFAULT '0',
  `spam_ia` tinyint(1) DEFAULT NULL,
  `spam_ia_probabilite` double DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversations_legacy_id_unique` (`legacy_id`),
  KEY `conversations_user_id_channel_index` (`user_id`,`channel`),
  KEY `conversations_channel_index` (`channel`),
  KEY `conversations_token_index` (`legacy_token`),
  KEY `conversations_selector_index` (`selector`),
  KEY `conversations_last_message_at_index` (`last_message_at`),
  KEY `conversations_is_spam_index` (`is_spam`),
  CONSTRAINT `conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `data_exports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `fichier` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `taille` bigint(20) unsigned DEFAULT NULL,
  `erreur` text COLLATE utf8mb4_unicode_ci,
  `termine_at` timestamp NULL DEFAULT NULL,
  `expire_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `data_exports_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `data_exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `galleries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `color` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci,
  `media_order` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `galleries_legacy_id_unique` (`legacy_id`),
  KEY `galleries_parent_id_foreign` (`parent_id`),
  KEY `galleries_user_id_status_position_index` (`user_id`,`status`,`position`),
  KEY `galleries_status_index` (`status`),
  CONSTRAINT `galleries_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `galleries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `galleries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=905 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `legacy_source` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `brand` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vat` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `gateway` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_payload` json DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_number_unique` (`number`),
  UNIQUE KEY `invoices_legacy_source_legacy_id_unique` (`legacy_source`,`legacy_id`),
  KEY `invoices_user_id_issued_at_index` (`user_id`,`issued_at`),
  KEY `invoices_brand_index` (`brand`),
  KEY `invoices_status_index` (`status`),
  KEY `invoices_issued_at_index` (`issued_at`),
  CONSTRAINT `invoices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `marketing_offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `offered_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketing_offers_legacy_id_unique` (`legacy_id`),
  KEY `marketing_offers_user_id_type_offered_at_index` (`user_id`,`type`,`offered_at`),
  CONSTRAINT `marketing_offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `gallery_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `mime` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `status` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `position` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_legacy_id_unique` (`legacy_id`),
  KEY `media_user_id_status_index` (`user_id`,`status`),
  KEY `media_gallery_id_position_index` (`gallery_id`,`position`),
  KEY `media_status_index` (`status`),
  CONSTRAINT `media_gallery_id_foreign` FOREIGN KEY (`gallery_id`) REFERENCES `galleries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `media_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13726 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `from_owner` tinyint(1) NOT NULL DEFAULT '0',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `messages_legacy_id_unique` (`legacy_id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_mails` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_mails_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `page_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_images_user_id_index` (`user_id`),
  CONSTRAINT `page_images_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `code` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'amount',
  `max_uses` int(10) unsigned DEFAULT NULL,
  `uses` int(10) unsigned NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promo_codes_code_unique` (`code`),
  UNIQUE KEY `promo_codes_legacy_id_unique` (`legacy_id`)
) ENGINE=InnoDB AUTO_INCREMENT=211 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `referrals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `sponsor_id` bigint(20) unsigned NOT NULL,
  `referred_id` bigint(20) unsigned DEFAULT NULL,
  `referred_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `referrals_legacy_id_unique` (`legacy_id`),
  KEY `referrals_sponsor_id_foreign` (`sponsor_id`),
  KEY `referrals_referred_id_foreign` (`referred_id`),
  KEY `referrals_status_index` (`status`),
  CONSTRAINT `referrals_referred_id_foreign` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `referrals_sponsor_id_foreign` FOREIGN KEY (`sponsor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_queries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `q` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `search_queries_brand_created_at_index` (`brand`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_terms` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `hits` int(10) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `search_terms_term_brand_unique` (`term`,`brand`),
  KEY `search_terms_term_index` (`term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `selections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `brand` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `category_slug` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selections_legacy_id_unique` (`legacy_id`),
  KEY `selections_brand_index` (`brand`),
  KEY `selections_category_slug_index` (`category_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `selection_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `selection_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `sent_twitter` tinyint(1) NOT NULL DEFAULT '0',
  `sent_instagram` tinyint(1) NOT NULL DEFAULT '0',
  `sent_mail` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `selection_user_selection_id_user_id_unique` (`selection_id`,`user_id`),
  KEY `selection_user_user_id_foreign` (`user_id`),
  CONSTRAINT `selection_user_selection_id_foreign` FOREIGN KEY (`selection_id`) REFERENCES `selections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `selection_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscription_reminders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `expires_on` date NOT NULL,
  `days_before` tinyint(3) unsigned NOT NULL,
  `sent_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_reminders_user_id_expires_on_days_before_unique` (`user_id`,`expires_on`,`days_before`),
  CONSTRAINT `subscription_reminders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `legacy_id` int(10) unsigned DEFAULT NULL,
  `login` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `brand` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ub',
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fr',
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `civility` tinyint(3) unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zipcode` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_domain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `in_home_selection` tinyint(1) NOT NULL DEFAULT '0',
  `home_selection_at` timestamp NULL DEFAULT NULL,
  `in_directory` tinyint(1) NOT NULL DEFAULT '0',
  `is_selected` tinyint(1) NOT NULL DEFAULT '0',
  `is_available` tinyint(1) NOT NULL DEFAULT '0',
  `accepts_sms` tinyint(1) NOT NULL DEFAULT '0',
  `shares_link` tinyint(1) NOT NULL DEFAULT '0',
  `plan` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `plan_started_at` timestamp NULL DEFAULT NULL,
  `plan_months` smallint(5) unsigned DEFAULT NULL,
  `plan_expires_at` timestamp NULL DEFAULT NULL,
  `storage_used` bigint(20) unsigned NOT NULL DEFAULT '0',
  `media_count` int(10) unsigned NOT NULL DEFAULT '0',
  `signup_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signup_referer` text COLLATE utf8mb4_unicode_ci,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_login_unique` (`login`),
  UNIQUE KEY `users_legacy_id_unique` (`legacy_id`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_category_id_foreign` (`category_id`),
  KEY `users_email_index` (`email`),
  KEY `users_brand_index` (`brand`),
  KEY `users_custom_domain_index` (`custom_domain`),
  KEY `users_is_published_index` (`in_home_selection`),
  KEY `users_in_directory_index` (`in_directory`),
  KEY `users_is_selected_index` (`is_selected`),
  KEY `users_is_available_index` (`is_available`),
  KEY `users_plan_index` (`plan`),
  KEY `users_plan_expires_at_index` (`plan_expires_at`),
  KEY `users_home_selection_at_index` (`home_selection_at`),
  CONSTRAINT `users_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_password_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `request_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_password_resets_token_hash_unique` (`token_hash`),
  KEY `user_password_resets_user_id_foreign` (`user_id`),
  KEY `user_password_resets_expires_at_index` (`expires_at`),
  CONSTRAINT `user_password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `visit_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `surface` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'book',
  `public_views` int(10) unsigned NOT NULL DEFAULT '0',
  `admin_views` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `visit_stats_user_id_date_surface_unique` (`user_id`,`date`,`surface`),
  CONSTRAINT `visit_stats_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tables abandonnees : a supprimer une fois la reprise validee.
-- DROP TABLE `bn_ultrabook_art_portefolio`;
-- DROP TABLE `bn_ultranews_art`;
-- DROP TABLE `bn_ultranews_art_cmsfront`;
-- DROP TABLE `bn_ultranews_art_tab_cmsfront`;
-- DROP TABLE `bn_ultranews_art_tab_default`;
-- DROP TABLE `bn_ultranews_rub`;
-- DROP TABLE `bn_ultranews_rub_cmsfront`;
-- DROP TABLE `df2_fac`;
-- DROP TABLE `df2_mail_relance`;
-- DROP TABLE `inc_auto_selection`;
-- DROP TABLE `inc_auto_selection_user`;
-- DROP TABLE `inc_comt`;
-- DROP TABLE `inc_marketing`;
-- DROP TABLE `inc_stats`;
-- DROP TABLE `inc_user`;
-- DROP TABLE `inc_user_pref`;
-- DROP TABLE `inc_user_token`;
-- DROP TABLE `nl_newsletter`;
-- DROP TABLE `ub2_codepromo`;
-- DROP TABLE `ub2_contact_form`;
-- DROP TABLE `ub2_dispo`;
-- DROP TABLE `ub2_edit_txt`;
-- DROP TABLE `ub2_fac`;
-- DROP TABLE `ub2_gal_img`;
-- DROP TABLE `ub2_gal_rub`;
-- DROP TABLE `ub2_intermediate_form`;
-- DROP TABLE `ub2_parrainage`;
-- DROP TABLE `ub2_stats_mcles`;
-- DROP TABLE `wp_import`;

SET FOREIGN_KEY_CHECKS = 1;

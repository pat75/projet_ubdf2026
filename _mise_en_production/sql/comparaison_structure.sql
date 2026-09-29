-- Genere le 2026-09-29 07:49 par `php artisan ubdf:prod:comparer-structures`
-- Ancienne base : ub2020 (29 tables) — nouvelle base : 2026_ubdf (39 tables)

-- ==================================================================
-- 1. Tables reprises : ancienne -> nouvelle(s)
-- ==================================================================

-- inc_user -> users
--   ancienne : us_id, us_login, us_pass, us_pass_old, us_type, us_vipcode, us_societe, us_titre, us_nom, us_prenom, us_mail, us_adresse, us_cp, us_ville, us_telephone, us_portable, us_fax, us_photo, us_ip, us_host, us_nav, us_referer, us_commentaire, us_date, us_http, us_affhome, us_licence, us_confirm_mail, us_dir, us_key, us_delete, us_lng, us_lat, us_anu, us_ultraselection, us_pays, us_statut, us_map, us_formule, us_formule_date, us_formule_nbmois, us_img_size, us_img_nb, us_facebook_url, us_twitter_url, us_instagram_url, us_partage_lien, us_facebook_id, us_google_id, us_linkedin_id, us_domaine, us_lang, us_view, us_pf_sms_contact, us_dispo
--   users : id, legacy_id, login, email, email_verified_at, password, google_id, remember_token, category_id, brand, locale, firstname, lastname, company, civility, status, address, zipcode, city, country, phone, mobile, latitude, longitude, website, facebook_url, twitter_url, instagram_url, custom_domain, in_home_selection, home_selection_at, in_directory, is_selected, is_available, accepts_sms, shares_link, plan, plan_started_at, plan_months, plan_expires_at, storage_used, media_count, signup_ip, signup_referer, admin_note, created_at, updated_at, deleted_at

-- inc_user_pref -> book_settings
--   ancienne : us_pref_id, us_id, us_pf_nom, us_pf_descp, us_pf_lienpopup, us_pf_img1, us_pf_img2, us_pf_img3, us_pf_img4, us_pf_img5, us_pf_type_compte, us_pf_nb_img, us_pf_css, us_pf_js, us_pf_analytic, us_pf_img_vignette, us_pf_bg, us_pf_center, us_pf_v2, us_pf_fdoption, us_pf_fdcoul, us_pf_piedpage, us_pf_version_web, us_pf_version_ipad, us_pf_version_iphone, us_pf_descp_mobile, us_pf_visuel2012, us_pf_conf2012, us_pf_conf2012_slide, us_pf_conf2013_pinter, us_pf_visuel2014, us_pf_conf2014_responsive, us_pf_diff_ub, us_pf_diff_web, us_pf_clas2015_visuel_top1, us_pf_clas2015_visuel_top2, us_pf_clas2015_visuel_top3, us_pf_clas2015_visuel_accueil, us_pf_conf2015_classique, us_pf_grid2015_visuel_accueil, us_pf_conf2015_grid, us_pf_conf2016_zoom, us_pf_zoom2016_visuel_accueil, us_pf_img_photo_bio, us_pf_diff_newsletter, us_pf_diff_ubdf, us_pf_diff_dispo, us_formule_ask_date, us_pf_experience, us_pf_conf2020_ultra_zen, us_pf_ultra_zen_2020_visuel_accueil
--   book_settings : id, legacy_id, user_id, title, description, description_mobile, experience, footer, theme, theme_settings, theme_texts, theme_home_image, background_image, background_color, background_mode, is_centered, thumbnail, bio_photo, keywords, custom_js, analytics_id, diffuse_ub, diffuse_web, diffuse_newsletter, diffuse_availability, legacy_payload, created_at, updated_at

-- ub2_gal_rub -> book_sections, galleries
--   ancienne : rub_id, rub_id_us, rub_id_categorie, rub_id_parent, rub_ordre_rub, rub_ordre_img, rub_publier, rub_nom, rub_link, rub_date_crea, rub_coul
--   book_sections : id, legacy_id, legacy_source, user_id, parent_id, kind, title, slug, is_published, is_private, position, page_order, color, icon, created_at, updated_at
--   galleries : id, legacy_id, user_id, parent_id, name, slug, status, position, color, password, media_order, created_at, updated_at, deleted_at

-- ub2_gal_img -> media, book_articles
--   ancienne : img_id, img_id_us, img_publier, img_titre, img_titre_alt, img_link, img_date_crea, img_fichier, img_poids, img_type, img_desc, img_html, fk_rub_id, img_offre_similaire, img_offre_vente
--   media : id, legacy_id, user_id, gallery_id, filename, title, alt, link, video_url, description, mime, size, width, height, status, position, created_at, updated_at, deleted_at
--   book_articles : id, legacy_id, legacy_source, user_id, book_section_id, title, slug, body, body_blocks, image, keywords, status, position, published_at, created_at, updated_at

-- bn_ultranews_rub -> book_sections
--   ancienne : id, id_site, id_util, id_parent, rub_titre, rub_pub, rub_type, rub_model, rub_model_masque, rub_color, rub_icon, rub_icon_over, rub_ord, rub_nb_niveau, rub_pre
--   book_sections : id, legacy_id, legacy_source, user_id, parent_id, kind, title, slug, is_published, is_private, position, page_order, color, icon, created_at, updated_at

-- bn_ultranews_art -> book_articles
--   ancienne : id, id_site, id_util, id_rub, id_art_tab, art_titre, art_date, art_ordre, art_pub
--   book_articles : id, legacy_id, legacy_source, user_id, book_section_id, title, slug, body, body_blocks, image, keywords, status, position, published_at, created_at, updated_at

-- bn_ultrabook_art_portefolio -> book_articles
--   ancienne : id, id_art, art_nom, art_texte, art_img, art_mot_clef
--   book_articles : id, legacy_id, legacy_source, user_id, book_section_id, title, slug, body, body_blocks, image, keywords, status, position, published_at, created_at, updated_at

-- ub2_edit_txt -> book_settings
--   ancienne : id, ed_us_login, ed_mdl, ed_dom_txt, ed_date
--   book_settings : id, legacy_id, user_id, title, description, description_mobile, experience, footer, theme, theme_settings, theme_texts, theme_home_image, background_image, background_color, background_mode, is_centered, thumbnail, bio_photo, keywords, custom_js, analytics_id, diffuse_ub, diffuse_web, diffuse_newsletter, diffuse_availability, legacy_payload, created_at, updated_at

-- ub2_contact_form -> conversations, messages
--   ancienne : cf_id, cf_us_id, cf_nom, cf_mail, cf_message, cf_date, cf_lu, cf_del
--   conversations : id, legacy_id, user_id, channel, subject, request_detail, sender_name, sender_company, sender_email, sender_phone, legacy_token, selector, owner_token, sender_token, book_image, is_spam, spam_ia, spam_ia_probabilite, last_message_at, created_at, updated_at, deleted_at
--   messages : id, legacy_id, conversation_id, from_owner, body, ip, read_at, created_at, updated_at, deleted_at

-- ub2_intermediate_form -> conversations, messages
--   ancienne : mf_id, mf_us_id, mf_id_parent, mf_is_my_msg, mf_action, mf_action_org, mf_nom, mf_societe, mf_mail, mf_tel, mf_message, mf_book_visuel, mf_request_detail, mf_ip, mf_date, mf_update, mf_lu, mf_del, mf_token, mf_selector
--   conversations : id, legacy_id, user_id, channel, subject, request_detail, sender_name, sender_company, sender_email, sender_phone, legacy_token, selector, owner_token, sender_token, book_image, is_spam, spam_ia, spam_ia_probabilite, last_message_at, created_at, updated_at, deleted_at
--   messages : id, legacy_id, conversation_id, from_owner, body, ip, read_at, created_at, updated_at, deleted_at

-- ub2_fac -> invoices
--   ancienne : fac_id, fac_us_id, fac_titre, fac_lien, fac_stats, fac_total, fac_tva, fac_designation, fac_date, fac_paypaldata
--   invoices : id, legacy_id, legacy_source, user_id, brand, number, label, designation, amount, vat, currency, status, gateway, gateway_payload, issued_at, paid_at, created_at, updated_at

-- df2_fac -> invoices
--   ancienne : fac_id, fac_us_id, fac_titre, fac_lien, fac_stats, fac_total, fac_tva, fac_designation, fac_date, fac_paypaldata
--   invoices : id, legacy_id, legacy_source, user_id, brand, number, label, designation, amount, vat, currency, status, gateway, gateway_payload, issued_at, paid_at, created_at, updated_at

-- inc_stats -> visit_stats
--   ancienne : st_id, st_id_user, st_public_nb, st_public_date, st_admin_nb, st_admin_date, st_admin_ip_0, st_admin_ip_1, st_admin_ip_2, st_selection_date
--   visit_stats : id, user_id, date, surface, public_views, admin_views, created_at, updated_at

-- ub2_parrainage -> referrals
--   ancienne : par_id, par_us_id, par_us_login, par_us_send, par_us_send_login, par_date, par_nb_mois, par_nb_mois_send
--   referrals : id, legacy_id, sponsor_id, referred_id, referred_email, status, confirmed_at, created_at, updated_at

-- ub2_codepromo -> promo_codes
--   ancienne : pro_id, pro_code, pro_nbmois, pro_date_crea, pro_date_use, pro_etat, pro_us_id, pro_provenance, pro_us_login
--   promo_codes : id, legacy_id, code, label, discount, discount_type, max_uses, uses, starts_at, ends_at, is_active, created_at, updated_at

-- inc_marketing -> marketing_offers
--   ancienne : id, us_id, us_login, us_json, us_date, us_type
--   marketing_offers : id, legacy_id, user_id, type, offered_at, created_at, updated_at

-- ==================================================================
-- 2. Tables de meme nom dans les deux bases
-- ==================================================================

-- ==================================================================
-- 3. Tables anciennes sans reprise (13)
-- ==================================================================
--   bn_ultranews_art_cmsfront (9 colonnes)
--   bn_ultranews_art_tab_cmsfront (8 colonnes)
--   bn_ultranews_art_tab_default (7 colonnes)
--   bn_ultranews_rub_cmsfront (15 colonnes)
--   df2_mail_relance (11 colonnes)
--   inc_auto_selection (5 colonnes)
--   inc_auto_selection_user (7 colonnes)
--   inc_comt (9 colonnes)
--   inc_user_token (4 colonnes)
--   nl_newsletter (9 colonnes)
--   ub2_dispo (6 colonnes)
--   ub2_stats_mcles (5 colonnes)
--   wp_import (16 colonnes)

-- ==================================================================
-- 4. Tables nouvelles (39)
-- ==================================================================
--   accueil_blocs (vide au depart)
--   admins (vide au depart)
--   admin_activities (vide au depart)
--   billing_profiles (vide au depart)
--   book_articles (alimentee par la reprise)
--   book_sections (alimentee par la reprise)
--   book_settings (alimentee par la reprise)
--   cache (vide au depart)
--   cache_locks (vide au depart)
--   campaigns (vide au depart)
--   campaign_sends (vide au depart)
--   categories (vide au depart)
--   cms_pages (vide au depart)
--   cms_posts (vide au depart)
--   conversations (alimentee par la reprise)
--   data_exports (vide au depart)
--   failed_jobs (vide au depart)
--   galleries (alimentee par la reprise)
--   invoices (alimentee par la reprise)
--   jobs (vide au depart)
--   job_batches (vide au depart)
--   marketing_offers (alimentee par la reprise)
--   media (alimentee par la reprise)
--   messages (alimentee par la reprise)
--   migrations (vide au depart)
--   newsletter_mails (vide au depart)
--   page_images (vide au depart)
--   password_reset_tokens (vide au depart)
--   promo_codes (alimentee par la reprise)
--   referrals (alimentee par la reprise)
--   search_queries (vide au depart)
--   search_terms (vide au depart)
--   selections (vide au depart)
--   selection_user (vide au depart)
--   sessions (vide au depart)
--   subscription_reminders (vide au depart)
--   users (alimentee par la reprise)
--   user_password_resets (vide au depart)
--   visit_stats (alimentee par la reprise)

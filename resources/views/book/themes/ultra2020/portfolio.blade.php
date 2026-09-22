{{-- Porte depuis 2011_html_pages_v2/ultra2020/portfolio.tlp.php (_outils/porter_gabarits.py) --}}
<?php

/* ------------------------------------------------
	Ultra-Book GRID | Modèle page : book
------------------------------------------------ */

// toutes les data du book en dur
//require_once('include/ub-data-global.php');

// toutes les fonctions utiles (type affiche_...)
//require_once('include/ub-grid-template.php');



// mansory CSS -v2
// https://w3bits.com/labs/css-masonry/
// https://w3bits.com/labs/css-grid-masonry-js/
//
// Maj mai - 2023



// test dev
//$b->us_formule_img_nb = 12; //0;


// pour la class de la balise home + titre page
$typePage = "book";


// bouclage rub
$array_rub = $b->gal_cont['gal'];
//print_r($b->gal_cont['gal']);


// gal content
$content = 			'';




$key_total = 0;

if (is_array($array_rub) )
	foreach ( $array_rub as $k => $rub ) {


		//if ($k > $b->us_formule_img_rub_nb) break;

		if ( is_array( $array_rub['img'][ $rub['rub_id'] ] ) ) {   // selection rub suivant le lien /*&& $rub['rub_id'] == $b->rub_id */

			//print_r($rub);

			// page title
			$titrePage = $b->prenom . ' ' . $b->nom . ' | ' . ucfirst( $typePage ) . ' : ' . $titre;

			if ( $array_rub['img'][ $rub['rub_id'] ] ) {
				foreach ( $array_rub['img'][ $rub['rub_id'] ] as $key => $img ) {

					$key_total++;
					if ( $key_total >= $b->us_formule_img_nb ) {
						break;
					}
					//print_r($img);


					$img['img_titre'] = strip_tags( $img['img_titre'] );
					$img['img_desc']  = nl2br( strip_tags( $img['img_desc'] ) );

					if ( $img['img_fichier'] != '' ) {

						if ( preg_match( '/\.swf$/', $img['img_fichier'] ) ) {
							$tmp_swf = true;
						} else {
							$tmp_swf = false;
						} /* aff des swf */

						$rub_name_url = strtolower( preg_replace( "/(\s|\/|&|\(|\)|\"|\'|\.|\+|\||@|;|,|#|!)+/", "-", $rub['rub_nom'] ) );
						$rub_name_url = filter_var( $rub_name_url, FILTER_SANITIZE_URL );

                        // '.$tmp_display_none.'

						// Visuels par défaut (aucune image uploadée) : le fichier vit dans /img_default/, pas dans le dossier user
						$tmp_is_default_img = preg_match('/^(ultra-book_default_|visuel_default_)/', $img['img_fichier']);
						$tmp_rep_img900 = $tmp_is_default_img ? '/img_default/' : $b->rep_img900;
						$tmp_rep_img550 = $tmp_is_default_img ? '/img_default/' : $b->rep_img550;

						$tmp_image_ = ( ( ! $tmp_swf ) ? $b->url_abs_site . $tmp_rep_img900 . $img['img_fichier'] : $b->url_abs_site . '/2010_images/icone_flash.gif');
						$tmp_image_src = $tmp_image_;

						// Préparation des URLs responsive et dimensions pour lazyload/CLS
						$src_900 = (!$tmp_swf) ? ($b->url_abs_site . $tmp_rep_img900 . $img['img_fichier']) : '';
						$src_550 = (!$tmp_swf) ? ($b->url_abs_site . $tmp_rep_img550 . $img['img_fichier']) : '';
						$attr_dimensions = '';
						if (!$tmp_swf) {
							// Tentative de récupération des dimensions côté serveur (version 900w)
							$abs_path = @(public_path() . $tmp_rep_img900 . $img['img_fichier']);
							if (is_string($abs_path) && @@is_file($abs_path)) {
								$dim = @@getimagesize($abs_path);
								if ($dim && isset($dim[0], $dim[1])) {
									$attr_dimensions = 'width="' . (int)$dim[0] . '" height="' . (int)$dim[1] . '"';
								}
							}
						}

						// Prioriser le chargement des 12 premières images
						$priority_limit = 6; // nombre d'images à charger en priorité
						$is_priority = ($key_total <= $priority_limit);
						$loading_attr = $is_priority ? 'eager' : 'lazy';
						$fetchpriority_attr = $is_priority ? 'high' : 'low';
						$src_main = (!$tmp_swf ? ($is_priority ? $src_900 : $src_550) : $tmp_image_src);

                $content .= '
					<div class="masonry-item  bg_white  transition  
					  '.$b->obj_pref->data->visuel_size.' 
					  filter ' . $k . '__' . $rub_name_url . ' all cursor_effect hidden_  "
									data-mfp-src="' . ( ( ! $tmp_swf ) ? $b->url_abs_site . $tmp_rep_img900 . $img['img_fichier'] : $b->url_abs_site . '/2010_images/icone_flash.gif' ) . '"
									data-title="' . $img['img_titre'] . '"																	
						            >
						            										
									<img 
									id = "img_'.$key.'"
									src="' . ( !$tmp_swf ? ( $is_priority ? $src_main : 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==' ) : $src_main ) . '"
									' . ( !$tmp_swf 
										? ( $is_priority 
											? ('srcset="' . $src_550 . ' 550w, ' . $src_900 . ' 900w" sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw"')
											: ('data-src="' . $src_main . '" data-srcset="' . $src_550 . ' 550w, ' . $src_900 . ' 900w" data-sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw" data-defer="1"')
										)
										: ''
									) . '
									class="masonry-content masonry-img' . ( $is_priority ? '' : ' blur-up' ) . '" 
									alt="' . $img['img_titre'] . '"	
									data-desc="' . $img['img_desc'] . '"
									data-priority="' . ( $is_priority ? '1' : '0' ) . '"
									loading="' . $loading_attr . '" decoding="async" fetchpriority="' . $fetchpriority_attr . '" ' . $attr_dimensions . '>
									 
						            <div class="masonry-content+">
						            <div class="masonry-title">
						                <div class="masonry-title_">' . $img['img_titre'] . '</div>
						                <div class="masonry-title_sub">' . $rub['rub_nom'] . '</div>
						            </div>						            
																	
									</div>
							</div>
							';

					}
				}
			}

		}

	}


//print_r($content);
?>




<article class="twelve wide column column-portfolio column_ultrafrais">

	<?php if (is_array($array_rub) && \App\Services\Book\Php7::count($array_rub) > 2) { ?>
        <div class="menu_filtre">
            <div class="ui text menu">

                <div class="ui  dropdown item" id="dropdown_portfolio">
                    <span class="cursor_effect"><?=__('les projets')?></span>
                    <div class="icon_plus"></div>
                    <div class="menu">

                        <a class="item filter_select btn cursor_effect" id="all">
							<?=__('Tous')?>
                        </a>
						<?php
						// liste des ptf
						$tmp = '';
						$array_rub = $b->gal_cont['gal'];
						if (is_array($array_rub) ) {
							foreach ($array_rub as $k=>$rub) {
								if (is_array($array_rub['img'][$rub['rub_id']]) )	{

									$rub_name_url = strtolower (preg_replace("/(\s|\/|&|\(|\)|\"|\'|\.|\+|\||@|;|,|#|!)+/", "-", $rub['rub_nom']) );
									$rub_name_url = filter_var ($rub_name_url, FILTER_SANITIZE_URL);

									$tmp .= '<a class="item filter_select btn cursor_effect" ' . $tmp_style_hide . ' id="' . $k . '__' . $rub_name_url . '">' . $rub['rub_nom'] . '</a>';

								}
							}
						}
						echo $tmp;


						?>

                    </div>
                </div>
            </div>

        </div>
	<?php } ?>



    <!-- =loader -->
    <div id="loader">
        <div class="ui active centered inline loader"></div>
    </div>

    <div class="wide column column_ultrafrais">
    <div class="masonry_h">

		<?=$content?>

    </div>
    </div>
</article>

<style>
/* Effet blur-up pour chargement progressif */
.masonry-img.blur-up {
  filter: blur(12px);
  transform: scale(1.03);
  transition: filter .35s ease, transform .35s ease, opacity .2s ease;
  background-color: #f2f2f2;
}
.masonry-img.blur-up.is-loaded {
  filter: blur(0);
  transform: none;
}
</style>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.1/imagesloaded.pkgd.min.js"></script>
<script>
    /**
     * Set appropriate spanning to any masonry item
     *
     * Get different properties we already set for the masonry, calculate
     * height or spanning for any cell of the masonry grid based on its
     * content-wrapper's height, the (row) gap of the grid, and the size
     * of the implicit row tracks.
     *
     * @@param item Object A brick/tile/cell inside the masonry
     */
    /* Get the grid object, its row-gap, and the size of its implicit rows */

    var grid = document.getElementsByClassName('masonry_h')[0],
        rowGap = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-row-gap')),
        rowHeight = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-auto-rows'));


    function resizeMasonryItem(item){
        /*
		 * Spanning for any brick = S
		 * Grid's row-gap = G
		 * Size of grid's implicitly create row-track = R
		 * Height of item content = H
		 * Net height of the item = H1 = H + G
		 * Net height of the implicit row-track = T = G + R
		 * S = H1 / T
		 */
        var rowSpan = Math.ceil((item.querySelector('.masonry-content').getBoundingClientRect().height+rowGap)/(rowHeight+rowGap));

        /* Set the spanning as calculated above (S) */
        item.style.gridRowEnd = 'span '+rowSpan;

        /* Make the images take all the available space in the cell/item */
        item.querySelector('.masonry-content').style.height = rowSpan * 1 + "px";
    }

    /**
     * Apply spanning to all the masonry items
     *
     * Loop through all the items and apply the spanning to them using
     * `resizeMasonryItem()` function.
     *
     * @@uses resizeMasonryItem
     */
    function resizeAllMasonryItems(){
        // Get all item class objects in one list
        var allItems = document.getElementsByClassName('masonry-item');
        /*
		 * Loop through the above list and execute the spanning function to
		 * each list-item (i.e. each masonry item)
		 */
        //console.log('resize '+ allItems.length);
        for(var i=0;i < allItems.length;i++){
            resizeMasonryItem(allItems[i]);
        }
    }

    /**
     * Resize the items when all the images inside the masonry grid
     * finish loading. This will ensure that all the content inside our
     * masonry items is visible.
     *
     * @@uses ImagesLoaded
     * @@uses resizeMasonryItem
     */

    function waitForImages() {
        var allItems = document.getElementsByClassName('masonry-item');
        for (var i = 0; i < allItems.length; i++) {
            imagesLoaded(allItems[i])
                .on('progress', function(instance, image) {
                    // Afficher progressivement l'image chargée (blur-up -> net)
                    var item = instance.elements[0];
                    if (image && image.isLoaded && image.img) {
                        image.img.classList.add('is-loaded');
                    }
                    resizeMasonryItem(item);
                });
        }

        // Charger les images différées après que toutes les prioritaires soient prêtes
        setupDeferredLoading();
    }

    function setupDeferredLoading() {
        var priorityImgs = document.querySelectorAll('.masonry-img[data-priority="1"]');
        var deferredImgs = document.querySelectorAll('.masonry-img[data-defer="1"]');

        if (!deferredImgs.length) return; // rien à différer

        // Si pas d'images prioritaires, charger directement les différées
        if (!priorityImgs.length) {
            loadDeferredImages();
            return;
        }

        var totalPrior = priorityImgs.length;
        var loadedPrior = 0;

        function tryLoadDeferred() {
            if (loadedPrior >= totalPrior) {
                loadDeferredImages();
            }
        }

        // Méthode 1: via imagesLoaded sur la liste prioritaire (si supportée)
        try {
            imagesLoaded(priorityImgs).on('always', function() {
                loadedPrior = totalPrior;
                tryLoadDeferred();
            });
        } catch (e) {
            // ignore, on bascule sur la méthode 2
        }

        // Méthode 2: listeners individuels + état déjà chargé
        for (var i = 0; i < priorityImgs.length; i++) {
            var img = priorityImgs[i];
            if (img.complete && img.naturalWidth > 0) {
                loadedPrior++;
                tryLoadDeferred();
            } else {
                img.addEventListener('load', function() {
                    loadedPrior++;
                    tryLoadDeferred();
                }, { once: true });
                img.addEventListener('error', function() {
                    loadedPrior++;
                    tryLoadDeferred();
                }, { once: true });
            }
        }
    }

    function loadDeferredImages() {
        var deferredImgs = document.querySelectorAll('.masonry-img[data-defer="1"]');
        for (var i = 0; i < deferredImgs.length; i++) {
            var img = deferredImgs[i];
            // Promouvoir les data-* en vrais attributs pour déclencher les requêtes
            var dataSrc = img.getAttribute('data-src');
            var dataSrcset = img.getAttribute('data-srcset');
            var dataSizes = img.getAttribute('data-sizes');

            if (dataSrc) img.setAttribute('src', dataSrc);
            if (dataSrcset) img.setAttribute('srcset', dataSrcset);
            if (dataSizes) img.setAttribute('sizes', dataSizes);

            // Forcer le navigateur à planifier le chargement maintenant
            img.setAttribute('loading', 'auto');
            img.setAttribute('fetchpriority', 'low');

            // Nettoyage des attributs data-* et du marqueur defer
            img.removeAttribute('data-src');
            img.removeAttribute('data-srcset');
            img.removeAttribute('data-sizes');
            img.removeAttribute('data-defer');
        }
    }




    /* Resize all the grid items on the load and resize events */
    var masonryEvents = ['load', 'resize'];
    masonryEvents.forEach( function(event) {
        window.addEventListener(event, resizeAllMasonryItems);
    } );



    /* Do a resize once more when all the images finish loading */
    waitForImages();


</script>
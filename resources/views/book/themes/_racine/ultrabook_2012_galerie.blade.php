{{-- Porte depuis 2011_html_pages_v2/ultrabook_2012_galerie.tlp.php (_outils/porter_gabarits.py) --}}
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?=$b->prenom;?> <?=$b->nom;?> : Ultra-book galerie</title>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 


		<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css' />
		
		<style>
			
			body {
				font-family: Oswald, helvetica;
				font-size:13px;
				background-color:#fefefe;
				margin:0;
			}
				
			.am-wrapper{
		    float:left;
		    position:relative;
		    overflow:hidden;
			}
			
			.am-wrapper img{
			    position:absolute;
			    outline:none;
			}
		
		
			h3 {
				text-transform: uppercase;
				color: #999;
				margin-bottom:4px;
			}

			
		</style>

		<link rel="stylesheet" href="<?=$b->url_abs_site;?>/2012_js/colorbox/colorbox.css"/>

		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/AutomaticImageMontage/js/jquery.montage.js"></script>
		<script type="text/javascript" src="<?=$b->url_abs_site;?>/2012_js/colorbox/jquery.colorbox-min.js"></script>
		
		<script type="text/javascript">
			$(function() {
				
				$('.am-container').montage({
					fillLastRow				: false,
					alternateHeight			: true,								
					alternateHeightRange	: {
						min	: 120,
						max	: 120
					},
						margin : 1									
				});
				
				$(".ubgalerie").colorbox({
					rel:'group2', 
					transition:"fade",
					current:	"{current} / {total}",
					previous:	"Précédent",
					next:		"Suivant",
					close: 		"fermer",
					opacity:	0.65,
					width:"75%", 
					height:"75%"
					});
							
			});
		</script>
		
		
		
    </head>
    <body>
	<?php 		// chemin vignettes
	if ( $b->us_pf_img_vignette ) {
		$img =        $b->url_abs_site.$b->rep_pref.$b->us_pf_img_vignette;
		}
	else {
		$img =        '/img_front/_ultra_book_62x62.gif' ;	
		}
	
	?>
	
<div style="width:100%;height:40px;background-color:#000;color:white;padding:10px;margin:0;text-transform: uppercase;">
<div style="width:980px;margin: 0 auto ;">		
	<div style="float:left;margin-right: 10px;display:block;padding:4px;">
	<img src="<?=$img;?>"  width="32" height="32" alt="<?=$b->prenom;?> <?=$b->nom;?>" />
	</div>
	<div><?=$b->prenom;?> <?=$b->nom;?></div>
	
</div>    
</div>   

<div style="width:980px;margin: 0 auto ;">
		
<?php  
 
// bouclage rub
$array_rub = $b->gal_cont['gal'];


// page default
//$img = $b->gal_cont['img'][ $array_rub[0]['rub_id'] ][0];

//print_r($img);
//print_r($array_rub);

// rub par default
//$b->rub_id = $array_rub[0]['rub_id'];
		
		
if (is_array($array_rub)) 
    foreach ($array_rub as $k=>$rub) {	
    	//$kk[$k]=$k;
    	
    	//if ($k > $b->us_formule_img_rub_nb) break;
		
	 //  if (is_array($array_rub['img'][$rub['rub_id']]) && $rub['rub_id'] == $b->rub_id)	
	 //  { 
	    ?>
		
		<h3><?=$rub[rub_nom];?></h3>
		
		<div class="am-container" id="am-container">
		<?php  
						
           if ($array_rub['img'][$rub['rub_id']]) 
            foreach ($array_rub['img'][$rub['rub_id']] as $key=>$img) {                	
 			if ($key >= $b->us_formule_img_nb ) break;
				
			//$img['img_desc'] = 	strip_tags($img['img_desc']);
			$img['img_desc'] = 	html_entity_decode($img['img_desc'], ENT_QUOTES, 'UTF-8');
			$img['img_titre'] = strip_tags($img['img_titre']);
			
			if ($img['img_fichier']!='') {		                    
            ?>
            
				<?php  if (preg_match('/\.swf$/', $img['img_fichier'])) $tmp_swf = true; else $tmp_swf = false; /* aff des swf */ ?>    
			    <a href="<?=$b->url_abs_site.$b->rep_img900.$img['img_fichier'];?>" class="ubgalerie" title="<?=$img['img_titre'];?>">
			    	<img src="<?=(!$tmp_swf)?$b->url_abs_site.$b->rep_img320.$img['img_fichier']:$b->url_abs_site.'/2010_images/icone_flash.gif';?>" title="<?=$img['img_titre'];?>"></img></a>
			
            	<?php  
				//}
			
					}
				} 
			?>
			</div>
			<br clear="all"/>
			
			<?php 
			} 
			
			?>
		</div>
















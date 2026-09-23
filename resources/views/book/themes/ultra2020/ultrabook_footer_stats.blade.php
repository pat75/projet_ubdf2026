{{-- Porte depuis 2011_html_pages_v2/ultra2020/ultrabook_footer_stats.php (_outils/porter_gabarits.py) --}}
<?php 
// stats : pixel de comptage interne (voir StatsBookController)
//
$stats_action =		    'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($b->us_dir . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = '/ubstats.gif?r='.$stats_i;
?>								  	
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>


<?php /*
<?php /* Analytics : ga.js a ete arrete par Google en 2024. Seule la
   mesure propre au createur subsiste, en GA4, quand il en a declare une. */ ?>
<?php if (! empty($b->cont_analytic)) : ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=e($b->cont_analytic)?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?=e($b->cont_analytic)?>');
</script>
<?php endif; ?>
*/ ?>





<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-464814-11"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-464814-11');
</script>
<?php  if (isset($b->cont_analytic)) : ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?=$b->cont_analytic?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?=$b->cont_analytic?>');
    </script>
<?php  endif; ?>

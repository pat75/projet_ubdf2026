{{-- Porte depuis 2011_html_pages_v2/grid2015/ultrabook_footer_stats.php (_outils/porter_gabarits.py) --}}
<?php 
// stats sur le serveur extra-book.com
//
$stats_url =			'https://www.extra-book.com/2012_stats/st_action.php';
$stats_action =		    'add';
$stats_st_champ =		'st_book';
$stats_us_login =		$b->us_dir;
$stats_st_cles = 		md5($b->us_dir . 'pat75ub2012publique' );
$stats_i = 				rand(0,9999);
$stats_img = $stats_url.'?action='.$stats_action.'&st_champ='.$stats_st_champ.'&us_login='.$stats_us_login.'&st_cles='.$stats_st_cles.'&r='.$stats_i;
?>
<img src="<?=$stats_img;?>" width="1" height="1" style="display:none"/>







<script type="text/javascript">
// ga
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-464814-11']);
  _gaq.push(['_setDomainName', 'ultra-book.com']);
  _gaq.push(['_trackPageview']);
<?php  if (isset($b->cont_analytic)) : ?>
  _gaq.push(['t2._setAccount', '<?=$b->cont_analytic?>']);
  _gaq.push(['t2._setDomainName', '<?=$b->us_dir;?>.ultra-book.com']);
  _gaq.push(['t2._trackPageview']);
<?php  endif; ?>
  (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);})();  
</script>
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

{{-- Porte depuis 2011_html_pages_v2/zoom2016/_fonction.php (_outils/porter_gabarits.py) --}}
<?php



// refresh extension -sep2016
//
$maps_googleapis = 'AIzaSyChcd_aICKMtRG81CImIt9c6an6z9Jc8wc';

if ( ! preg_match('/ub([0-9]{4})\.ddns\.net/',request()->getHost()) ) {
	$js_ext_date = '.min.js?v='.date('d');
} else {
	$js_ext_date = '.js?v='.date('d');
}










// aff nav actu -menu gauche
// nav - func
//

/**
 * @@param $array_rub
 * @@param $array_pag
 * @@param $rub_id
 * @@param $pag_id
 * @@return array
 */


?>
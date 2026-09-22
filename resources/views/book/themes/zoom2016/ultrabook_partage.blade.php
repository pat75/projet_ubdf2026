{{-- Porte depuis 2011_html_pages_v2/zoom2016/ultrabook_partage.tlp.php (_outils/porter_gabarits.py) --}}
<div class="social-bookmarks-services group "> 	
<?php if ($b->us_partage_lien=='1' || true) { ?>	
 		<?php list($tmp_titre, $tmp_url) = book_socializer ($b->cont_page_titre); ?>
        <div class="social-bookmarks-services">
		   
		        <a class="partage_sociaux" data-sociaux="facebook" href="https://www.facebook.com/sharer.php?u=<?=$tmp_url;?>"  title="Partager sur 'Facebook'" rel="nofollow" target="_blank">
		        	<i class="icon-facebook_circle icon24x"></i>
		        </a>
		    
		        <a class="partage_sociaux" data-sociaux="twitter" href="https://twitter.com/home?status=<?=$tmp_url;?>"  title="Partager sur 'Twitter'" rel="nofollow" target="_blank">
					<i class="icon-twitter_circle icon24x"></i>
				</a>							   			
		   				   		
		        <a class="partage_sociaux" data-sociaux="linkedin" href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"   title="Partager sur 'LinkedIn'" rel="nofollow" target="_blank">
					<i class="icon-linked_in_circle icon24x"></i>
				</a>

			  <?php /*
				<!--	
		      <a href="https://www.google.com/bookmarks/mark?op=edit&amp;bkmk=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>"  title="Partager sur 'Google'" rel="nofollow" target="_blank">
		      <i class="icon-google_plus_circle icon24x"></i>
		      </a>
		     	<a href="https://www.viadeo.com/shareit/share/?url=<?=$tmp_url;?>&amp;title=<?=$tmp_titre;?>&amp;urllanguage=fr" title="Partager sur 'Viadeo'" rel="nofollow" target="_blank">
				<img class="icon24x" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACbElEQVRIibWWPWgUURSFX4Jg1EIiVv4kpLAWFGwUUvpTxMpCBCttRFOJK4gw1guLLGZ3X979Dsaoq0jESrJpJBYRsY6gjagbxUICEQxJ2MQiE9lsZiYrmAcPhnlz/865951xLmOZWa+Z5YCapLqkhXjXgZqZ5cysN8tH4vLe9wBVoCFpJWsDDaDqve9pyzkwAMxt5jgh0BwwkOlc0uAmWc9mnC3HtoOpmUta3iTLr5ImEs6+AxebIFtfSYx5u7DcBRbj59/AgxBCn3OuQ9KPNbjWcQJU/wHvz5LGJY2VSqVu55yLoqgziqIuYLKp2qpzbrUV2+mWFqimvPe71xI0s2Nxom+bu8vMeh1wM8PRkqRHwAVJ/SGEM8AI8M0511EoFHYAJwqFwo5isbhd0nyzvZnlXAppK5I+AYedc25oaOjg8PDwSe/9EeAeMAWUQwhnm2C+nZBgzQEzSe0YQugrlUrdwPOE8wngpaRbZnbczO6kwFx3Wh39lZbSrkRRtE3SmxTongEjbfC1kBRg3nu/08wuZbWqpDxwGniYGaAVIuBdPNUvMsi/LGnczPaY2dWMAPUkkl/FpE2mGYYQjkqarVQqh9YmOCWR2oY2BabjCkZTDD9KOg8s5fP5XWZ2PS2AmeU2DBrQCCEcMLNTKYY3JH0BnsaV1lKyb/zVioSrohJXcb/l/WtJT4AZYF8M1XJKgGrrZferJfq5KIo6gWvAe+CxpFFgpFwu7/fe7wU+pDif2yBArdc1sGhmuSiKuuJPOpp0oz/mIhGaVOFRguAAPyWNAUVAwHRG16QLTnMlWyaZLZxsjeg3r//x2/IHDtDRorAVkD4AAAAASUVORK5CYII="/></a>
				-->
				*/?>

		  		<a class="pin-it-button" data-sociaux="pinterest"  href="javascript:void((function(){var%20e=document.createElement('script');e.setAttribute('type','text/javascript');e.setAttribute('charset','UTF-8');e.setAttribute('src','https://assets.pinterest.com/js/pinmarklet.js?r='+Math.random()*99999999);document.body.appendChild(e)})());">
				<img class="icon24x"  src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAACWUlEQVRIibVWMWtUQRDeCAYbDSoagpiTFAqxFcGAIJYRoz9AUsRCRAiCyFk+sLE+7u65u/N9ghYHVmJ3Yn6AomAlioWCERRFYiwkgUts5ulm7t2LAbOwxdud/b6db2Z2nnMVQ0RqIlIH0CW5SHJF5yKArojURaRWhVE6vPfjADoAeiTXqyaAHoCO9378n8ABzABY3gy4hGgZwEwlOMl5kmvm8BeSt0MIUzHGURHZR3JSROZILlhvSM4PvLkFB3C/0Wjscc65LMt2xRhPi8j5EMLR4pyInCP53Ui20RPV/KcBz3V7CMANAEvGswXv/Zhe7hSA1VSuDTEB0DHgb7MsG3bODZF8WKH7qyzLdqi8d81ep3CxVpItV5X4shq/JjmR5/lBki9T2xDClHPOxRjP2HiISM0BuGVvVmhM8jnJ9RjjdJIIdwzQNSUYtTgiUnckn9gN7/2IerCkILsTgkeG4LrGcaREwq7TCt2w0Wq1DivBG107oJk0bGtERC6q7bGSOC06LX3LPJvUxXqM8YLKcNbYrRbkJC+VEKwMInjnvR8jOalrE3rLm8b2QSLd41ICAJ8GpOEPAM8AfCxAYozTSTG+aDab+zUTT7L/BfgjUV+Qy25Z5Hue50cAnEi+DwH4MKBOuqVpaoI4p/LMArhXPM/tdnuviFwB8LXibH1QoaU1cVxlaCfrvzbx+m+hlT0VyVzz3u/UIL7fDNQQdOxjV9YDvjnnXAhhaovgy30NSBtNzxh+1r2nWwDvf66TXLYNZwVAthVwDmo4xpPtaZkmJtvT9NPxP35bfgPyz5P+gTt51AAAAABJRU5ErkJggg=="/>
				</a>
		    			    	
		    	<a class="partage_sociaux" data-sociaux="tumblr" href="https://tumblr.com/widgets/share/tool?canonicalUrl=https://<?=request()->getHost().request()->getRequestUri();?>" target="blank_">
		    	<img class="icon24x" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAABhElEQVRIib1WPUsDQRA9rARLOyGcvYX+ANt06fIPLAJCSuEUm+vTZgnsznsB4VL5B66xsPYfKFaHvQHBKCEWucjm43Y36rmw3eO9mTczOxtFjiMisYgkAHKSBclJeQsAuYgkIhK7ODYepVSDZAZgSnLmuiUmU0o1gshJtgCMfcQbhMYkW05yEemGRO3KRkS6rsh/TL5i2XImSqlGgC33InIG4DbErqWaABgFRHde2ngVmE228D32WQPgsdfr7W0jUNYjjkQkcYA+AAwBHCyy1VqfkOyQ7PjsEpEkKoeoCvTk6boLTxZ5xPmEVgFejDFNrfXRgnQwGBwaY5rGmCYA7bGqiDgffZ+fN1bUoUWekZz8i0ClRX8gUPiKvCYA4DJUAEDubFMLOLIE2nYTAPh0tmnIoJF8sNsTwDGA0zRNd0m+VgQ1/d4VgU/FdZqmO7ZQv9/fB/BWgc+2fexmAJ4BDEkCwB2A9wrceG0Bsc7n2mrB+hbOSib1rEy7Jqxr6a9Y9utvyxe+zNQBMvuJUwAAAABJRU5ErkJggg=="/>
		        </a>							    	
		    			  							    	
		    			    
		</div>		
<?php  } ?>				
</div>	
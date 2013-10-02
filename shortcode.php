<?php

/******************************************************************
/* User Function
******************************************************************/
require_once (dirname ( __FILE__ ) . '/config.php');
function hdwplayer_plugin_shortcode($atts) {
	global $wpdb;
	if (! $atts ['id'])
		$atts ['id'] = 1;
	;
	$player = $wpdb->get_row ( "SELECT * FROM " . $wpdb->prefix . "hdwplayer WHERE id=" . $atts ['id'] );
	
	$siteurl = get_option ( 'siteurl' );
	$src = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/player.swf';
	$noimage = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/noimage.jpg';
	$rr = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/right.jpg';
	$lr = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/left.jpg';
	$buttons = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/buttons1.png';
	$slider = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/js/jquery.slider.min.js';
	$playerurl = $siteurl . '/?embed=view';
	
	$flashvars = 'baseW=' . $siteurl . '&id=' . encrypt_decrypt ( 'encrypt', $player->id );
	
	$gallery = $wpdb->get_row ( "SELECT * FROM " . $wpdb->prefix . "hdwplayer_gallery WHERE id=" . $player->galleryid );
	if ($gallery->id) {
		$playlist = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE playlistid=" . intval ( $player->playlistid ) );
	}
	
	$embed = '';
	$html5 = '';
	
	if ($player->videoid) {
		$results = $wpdb->get_row ( "SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE id=" . $player->videoid );
	} else if ($player->playlistid) {
		$results = $wpdb->get_row ( "SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE playlistid=" . $player->playlistid . " LIMIT 1" );
	}
	
	switch ($results->type) {
		case 'youtube' :
			$url_string = parse_url ( $results->video, PHP_URL_QUERY );
			parse_str ( $url_string, $args );
			$html5 = '<iframe title="YouTube video player" width="' . $player->width . '" height="' . $player->height . '" src="http://www.youtube.com/embed/' . $args ['v'] . '" frameborder="0" allowfullscreen></iframe>';
			break;
		case 'dailymotion' :
			$html5 = '<iframe frameborder="0" width="' . $player->width . '" height="' . $player->height . '" src="' . $results->video . '"></iframe>';
			break;
		case 'rtmp' :
			$url_string = str_replace ( 'rtmp', 'http', $results->streamer ) . '/' . $results->video . '/playlist.m3u8';
			$html5 = '<video onclick="this.play();" width="' . $player->width . '" height="' . $player->height . '" controls>';
			$html5 .= '<source src="' . $url_string . '" />';
			$html5 .= '</video>';
			break;
		default :
			$html5 = '<video onclick="this.play();" width="' . $player->width . '" height="' . $player->height . '" controls>';
			$html5 .= '<source src="' . $results->video . '" />';
			$html5 .= '</video>';
	}
	if ($gallery->id) {
		$embed .= '<div id="player_div' . $player->id . '">';
	}
	$embed .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' . $player->width . '" height="' . $player->height . '">';
	$embed .= '<param name="movie" value="' . $src . '" />';
	$embed .= '<param name="allowfullscreen" value="true" />';
	$embed .= '<param name="allowscriptaccess" value="always" />';
	$embed .= '<param name="flashvars" value="' . $flashvars . '" />';
	$embed .= '<object type="application/x-shockwave-flash" data="' . $src . '" width="' . $player->width . '" height="' . $player->height . '">';
	$embed .= '<param name="movie" value="' . $src . '" />';
	$embed .= '<param name="allowfullscreen" value="true" />';
	$embed .= '<param name="allowscriptaccess" value="always" />';
	$embed .= '<param name="flashvars" value="' . $flashvars . '" />';
	$embed .= '<p>' . $html5 . '</p>';
	$embed .= '</object>';
	$embed .= '</object>';
	
	if ($gallery->id) {
		$link = "";
		$qstr = "";
		$column = 0;
		$row = 1;
		$totalvideo = count ( $playlist );
		if ($totalvideo < $gallery->limit) {
			$gallery->limit = $totalvideo;
		}
		$pagelimit = $gallery->rows * $gallery->columns;
		$totaldiv = intval ( $totalvideo / $pagelimit );
		$remain = $totalvideo % $pagelimit;
		if ($remain > 0)
			$totaldiv ++;
		$k = 0;
		$n = 0;
		$exit = 0;		
		if($gallery->columns > $totalvideo){
			$cols = $totalvideo;
			$rows = 1;
		}else{
			$cols = $gallery->columns;
			if($totalvideo < $pagelimit){
				if(($totalvideo % $gallery->rows) == 0){
					$rows = intval ($totalvideo / $gallery->rows);
				}else{
					$rows = intval ($totalvideo / $gallery->rows) + 1;
				}
			}else{
				$rows = $gallery->rows;
			}			 
		}
		$vh = ((($gallery->height + 7) + 28) * $rows) + (($rows) * $gallery->space);
		$vw = (($gallery->width + 4) * $cols) + (($cols - 1) * $gallery->space);
		;
		$embed .= '</div>';
		$embed .= '<style>#slider' . $player->id . ' { height: 1%; overflow:hidden; padding: 0 0 10px;   width: '. ($vw+78) .'px; }
#slider' . $player->id . ' .viewport { float: left; width: ' . $vw . 'px; height: ' . $vh . 'px; overflow: hidden; position: relative; }
#slider' . $player->id . ' .buttons { background:url("' . $buttons . '") no-repeat scroll 0 0 transparent; display: block; margin: ' . (($vh / 2) - 17) . 'px 0px 0 0; background-position: 0 -38px; text-indent: -999em; float: left; width: 39px; height: 37px; overflow: hidden; position: relative; }
#slider' . $player->id . ' .next { background-position: 0 0; margin: ' . (($vh / 2) - 17) . 'px 0 0 0px;  }
#slider' . $player->id . ' .disable { visibility: hidden; }
#slider' . $player->id . ' .overview { list-style: none; position: absolute; padding: 0; margin: 0; width: ' . $vw . 'px; left: 0 top: 0; }
#slider' . $player->id . ' .overview li{ float: left; margin: 0 20px 0 0; padding: 1px; height: ' . $vh . 'px; border: 0px solid #dcdcdc; width: ' . $vw . 'px;}
</style>';
		$embed .= '';
		$embed .= '<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>';
		$embed .= '<script src="' . $slider . '"></script>';
		$embed .= '<script type="text/javascript">
		$(document).ready(function(){
			$("#slider' . $player->id . '").slider({ display: 1 });
		});
	</script>';
		$embed .= '<script>
		function changePlayer(video,div){
			$.post(location.href,
				{
					action:"flashvars",
					id:div
				},function(response){
					var flashvars = response.flashvars+"&vid="+video;
					var code = "";
			    	code += "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+response.width+"\" height=\""+response.height+"\">";
			    	code += "<param name=\"movie\" value=\"' . $src . '\" />";
					code += "<param name=\"allowfullscreen\" value=\"true\" />";
					code += "<param name=\"allowscriptaccess\" value=\"always\" />";
					code += "<param name=\"flashvars\" value=\""+flashvars+"\" />";
					code += "<object type=\"application/x-shockwave-flash\" data=\"' . $src . '\" width=\""+response.width+"\" height=\""+response.height+"\">";
					code += "<param name=\"movie\" value=\"' . $src . '\" />";
					code += "<param name=\"allowfullscreen\" value=\"true\" />";
					code += "<param name=\"allowscriptaccess\" value=\"always\" />";
					code += "<param name=\"flashvars\" value=\""+flashvars+"\" />";
					code += "</object>";
					code += "</object>";
					var divid = "player_div"+div;
					document.getElementById(divid).innerHTML = code;
				}, "json"
			);				
		}
		</script>';
		$embed .= '<div id="slider' . $player->id . '">';
		$embed .= '<a class="buttons prev" href="#">left</a>';
		$embed .= '<div class="viewport">';
		$embed .= '<ul class="overview">';
		for($j = 0; $j < $totaldiv; $j ++) {
			$embed .= '<li>';
			if (($n + $pagelimit) > $totalvideo) {
				$n = $n + $remain;
			} else {
				$n = $n + $pagelimit;
			}
			for($i = $k; $i < $n; $i ++) {
				$item = $playlist [$i];
				$css = 'float:left';
				if ($column >= $gallery->columns) {
					$css = 'float:left; clear:both';
					$column = 0;
					$row ++;
				}
				$xpos = ($column > 0) ? $gallery->space : 0;
				$ypos = ($row > 0) ? $gallery->space : 0;
				$column ++;
				
				if ($item->thumb == '')
					$item->thumb = $noimage;
				
				$embed .= '<div style="margin:' . $ypos . 'px 0px 0px ' . $xpos . 'px; ' . $css . ' ">';
				$embed .= '<div> <a onclick="changePlayer(\'' . $item->id . '\',\'' . $player->id . '\');" style="width:' . $gallery->width . ';text-decoration:none;"> <img src="' . $item->thumb . '" width="' . $gallery->width . '" height="' . $gallery->height . '" title="' . 'Click to Watch : ' . $item->title . '" border="0"/> </a></div>';
				$embed .= '<div style="width:' . $gallery->width . 'px; margin:2px; 2px; white-space:nowrap; overflow: hidden;"> <a onclick="changePlayer(\'' . $item->id . '\',\'' . $player->id . '\');" style="text-decoration:none;" title="' . $item->title . '">' . $item->title . '</a></div>';
				$embed .= '</div>';
				if (($i + 1) == $gallery->limit) {
					$exit = 1;
				}
			}
			$k = $k + $pagelimit;
			$embed .= '</li>';
			if ($exit == 1) {
				break;
			}
		}
		$embed .= '</ul>';
		$embed .= '</div>';
		$embed .= '<a class="buttons next" href="#">right</a>';
		$embed .= '</div>';
	}
	return $embed;
}

add_shortcode ( 'hdwplayer', 'hdwplayer_plugin_shortcode' );

?>
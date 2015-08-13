<?php

/******************************************************************
/* User Function
******************************************************************/
require_once (dirname ( __FILE__ ) . '/config.php');
function hdwplayer_plugin_shortcode($atts) {
	global $wpdb;
	if (! $atts ['id'])
		$atts ['id'] = 1;
	
	$embed = '';
	$html5 = '';
	
	$player = $wpdb->get_row ($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "hdwplayer WHERE id=%d",intval($atts ['id'])));
	
	$siteurl = get_option ( 'siteurl' );
	$src = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/player.swf';
	$noimage = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/noimage.jpg';
	$buttons = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/buttons.png';
	$jquery = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/js/jquery.min.js';
	$slider = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/js/jquery.slider.min.js';
	$inner = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/inner1.png';
	$outer = $siteurl . '/wp-content/plugins/' . basename ( dirname ( __FILE__ ) ) . '/assets/outer1.png';
	$playerurl = $siteurl . '/?embed=view';
	
	$flashvars = 'baseW=' . $siteurl . '&id=' . encrypt_decrypt ( 'encrypt', $player->id );
	
	$gallery = $wpdb->get_row ($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "hdwplayer_gallery WHERE id=%d",$player->galleryid));
	
	if ($player->videoid) {
		
		$results = $wpdb->get_row ($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE id=%d",$player->videoid));
		
	} else if ($player->playlistid) {
		
		$results = $wpdb->get_row ($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE playlistid=%d ORDER BY ordering LIMIT 1",intval ( $player->playlistid )) );
		$playlist = $wpdb->get_results ($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "hdwplayer_videos WHERE playlistid = %d ORDER BY ordering",intval ( $player->playlistid )));
		
	}
	
	$detect = new Mobile_Detect();
	if ($detect->isMobile()) {
		$html5 .= '<style>
			@media only screen and (max-width: 640px) {
			#player_div' . $player->id . ', #player_container'.$player->id.'{
			width:100% !important;
			height: 360px !important;
			}
			}
			
			@media only screen and (max-width: 460px) {
			#player_div' . $player->id . ', #player_container'.$player->id.'{
			width:100% !important;
			height: 300px !important;
			}
			}
		
			@media only screen and (max-width: 320px) {
			#player_div' . $player->id . ', #player_container'.$player->id.'{
			width:100% !important;
			height: 200px !important;
			}
			}
			</style>';
		if(count($playlist) > 1 && $player->playlist == 1){
			$isHtml5 = true;
			$html5 .= '<div id="player_container'.$player->id.'" style=" background:#000; width:' . $player->width . 'px; height:' . $player->height . 'px; margin-bottom: 15px; position:relative; overflow:hidden;" >';			
		}
		$html5 .= '<div style="';
		if($isHtml5){
			$html5 .= 'position:absolute;';
		}
		$html5 .= ' width:' . $player->width . 'px; height:' . $player->height . 'px; background:#000;" id="player_div' . $player->id . '">';
		switch ($results->type) {
			case 'youtube' :
				$url_string = parse_url ( $results->video, PHP_URL_QUERY );
				parse_str ( $url_string, $args );
				$html5 .= '<iframe title="YouTube video player" width="100%" height="100%" src="http://www.youtube.com/embed/' . $args ['v'] . '" frameborder="0" allowfullscreen></iframe>';
				break;
			case 'dailymotion' :
				$html5 .= '<iframe frameborder="0" width="100%" height="100%" src="' . $results->video . '"></iframe>';
				break;
			case 'vimeo' :
				$vimeoid = substr(parse_url($results->video, PHP_URL_PATH), 1);
				$html5 .= '<iframe frameborder="0" width="100%" height="100%" src="http://player.vimeo.com/video/' . $vimeoid . '?badge=0"></iframe>';
				break;
			case 'rtmp' :
				$url_string = str_replace ( 'rtmp', 'http', $results->streamer ) . '/' . $results->video . '/playlist.m3u8';
				$html5 .= '<video poster="' . $results->preview . '" onclick="this.play();" width="100%" height="100%" controls>';
				$html5 .= '<source src="' . $url_string . '" />';
				$html5 .= '</video>';
				break;
			default :
				$html5 .= '<video poster="' . $results->preview . '" onclick="this.play();" width="100%" height="100%" controls>';
				$html5 .= '<source src="' . $results->video . '" />';
				$html5 .= '</video>';
		}
		$html5 .= '</div>';
		if(count($playlist) > 1 && $player->playlist == 1){			
			$html5 .= '<img id="' . $player->id . 'hdwplaylist" class="outer" style="display:none; margin:' . ($player->height/2 - 20) . 'px 0px 0px ' . ($player->width - 22) . 'px; position:absolute; cursor:pointer;" title="More Videos" src="'.$inner.'" />';
			$html5 .= '<div id="' . $player->id . 'hdwvideos" class="outer" style="display:block;; width:150px; height:' . $player->height . 'px; margin:0px 0px 0px ' . ($player->width) . 'px; background:black; position:absolute;">';
			$html5 .= '<div style="font-variant:small-caps; color:white; margin:0px 0px 2px 28px; height:20px;">';
			$html5 .= 'Related Videos</div>';
			$html5 .= '<div id="' . $player->id . 'playlistbody" style="height:' . ($player->height - 22) . 'px; background:#242526; overflow:scroll; overflow-x:hidden;">';
			for($i = 0; $i < count($playlist); $i++)
			{
			if($playlist[$i]->thumb != "") {
			$html5 .= '<img onclick="changePlayer(\'' . $playlist[$i]->id . '\',\'' . $player->id . '\');" id="' . $player->id . 'video' . $playlist[$i]->id . '" src="' . $playlist[$i]->thumb . '" width="112" height="78"';
					$html5 .= 'style="margin:10px 0px 0px 10px; cursor:pointer;">';
			}
					$html5 .= '<div id="' . $player->id . 'video' . $playlist[$i]->id . '" style="margin:2px 0px 0px 0px; ';
							if($i%2) { $html5 .=  'background: #1b1c1c;'; }
							$html5 .= ' font-variant:small-caps; font-size:11px; color:white; text-align:center; cursor:pointer;"><a style="color:#fff; text-decoration:none;" onclick="changePlayer(\'' . $playlist[$i]->id . '\',\'' . $player->id . '\');" >' . ($playlist[$i]->title) . '</a></div>';
			}
			$html5 .= '</div></div>';
			$html5 .= '</div>';
					$html5 .= '<script type="text/javascript">';
					$html5 .= 'var $j = jQuery.noConflict();';
					$html5 .= '$j(document).ready(function(){';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").click(function(){';
					$html5 .= 'if($j("#' . $player->id . 'hdwplaylist").attr("class") == "outer"){';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").attr("class", "inner");';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").attr("src", "'.$outer.'");';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").attr("class", "inner");';
					$html5 .= 'var list = parseInt( $j("#' . $player->id . 'hdwplaylist").css("marginLeft") );';
					$html5 .= 'list = list - 150;';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").animate({"margin-left":list+"px"},500);';
					$html5 .= '$j("#player_div' . $player->id . '").animate({"margin-left":"-160px"},500);';
					$html5 .= 'var v = parseInt( $j("#' . $player->id . 'hdwvideos").css("marginLeft") );';
					$html5 .= 'v = v - 150;';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").css({"display":"block"});';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").animate({"margin-left":v+"px"},500);';
					$html5 .= '}else{';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").attr("class", "outer");';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").attr("src", "'.$inner.'");';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").attr("class", "outer");';
					$html5 .= 'var list = parseInt( $j("#' . $player->id . 'hdwplaylist").css("marginLeft") );';
					$html5 .= '$j("#player_div' . $player->id . '").animate({"margin-left":"0px"},500);';
					$html5 .= 'list = list + 150;';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").animate({"margin-left":list+"px"},500);';
					$html5 .= 'var v = parseInt( $j("#' . $player->id . 'hdwvideos").css("marginLeft") );';
					$html5 .= 'v = v + 150;';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").animate({"margin-left":v+"px"},500,function() {';
					$html5 .= '$j(this).hide();';
					$html5 .= '});';
					$html5 .= '}});';
					$html5 .= '});';
					$html5 .= '</script>';
					$html5 .= '<script type="text/javascript">';
					$html5 .= '$j(document).ready(function(){';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").css({"display":"block","z-index":"99"});';					
					$html5 .= 'var ht = parseInt( $j("#player_container' . $player->id . '").css("height") );';
					$html5 .= 'var wd = parseInt( $j("#player_container' . $player->id . '").css("width") );';
							
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").css({"margin-top":ht/2 - 20+"px","margin-left":wd - 22+"px"});';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").css({"height":ht+"px","margin-left":wd+"px"});';
					$html5 .= '$j("#' . $player->id . 'playlistbody").css({"height":ht-22+"px"});';
					$html5 .= '});';
					$html5 .= '</script>';

					$html5 .= '<script type="text/javascript">';
					$html5 .= '$j(document).ready(function(){';
					$html5 .= '$j(window).resize(function(){';
					$html5 .= 'var ht = parseInt( $j("#player_container' . $player->id . '").css("height") );';
					$html5 .= 'var wd = parseInt( $j("#player_container' . $player->id . '").css("width") );';
					$html5 .= 'if($j("#' . $player->id . 'hdwplaylist").attr("class") == "outer"){';						
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").css({"margin-top":ht/2 - 20+"px","margin-left":wd - 22+"px"});';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").css({"height":ht+"px","margin-left":wd+"px"});';
					$html5 .= '}else{';
					$html5 .= '$j("#' . $player->id . 'hdwplaylist").css({"margin-top":ht/2 - 20+"px","margin-left":wd - 22-150+"px"}); ';
					$html5 .= '$j("#' . $player->id . 'hdwvideos").css({"height":ht+"px","margin-left":wd-150+"px"});}';
					$html5 .= '$j("#' . $player->id . 'playlistbody").css({"height":ht-22+"px"});';
					$html5 .= '});';
					$html5 .= '});';
					$html5 .= '</script>';
					if($player->playlistopen == '1'){
						$html5 .= '<script type="text/javascript">';
						$html5 .= '$j(document).ready(function(){';
						$html5 .= '$j("#' . $player->id . 'hdwplaylist").click();';
						$html5 .= '});';
						$html5 .= '</script>';
					}
		}
	}
	if ($detect->isMobile()) {
		$embed .= $html5;
	}else{
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
		$embed .= '</object>';
		$embed .= '</object>';
		if ($gallery->id) {
			$embed .= '</div>';
		}
	}

	/* ================ Gallery List ================ */
	
	if ($gallery->id) {
		$link = "";
		$qstr = "";
		$column = 0;
		$row = 1;
		$k = 0;
		$n = 0;
		$exit = 0;
		if ($detect->isMobile() && (strpos($detect->userAgent(),'iPhone') !== FALSE || strpos($detect->userAgent(),'Android') !== FALSE))
		{
			$gallery->rows = $gallery->columns = 1;
		}
		$totalvideo = count ( $playlist );
		
		if ($totalvideo < $gallery->limit) {
			$gallery->limit = $totalvideo;
		}
		if($totalvideo > $gallery->limit){
			$totalvideo = $gallery->limit;
		}
		
		$pagelimit = $gallery->rows * $gallery->columns;
		$totaldiv = intval ( $totalvideo / $pagelimit );
		$remain = $totalvideo % $pagelimit;
		
		if ($remain > 0)
			$totaldiv ++;
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
		
		$embed .= '<style>#slider' . $player->id . ' { height: 1%; overflow:hidden; padding: 0 0 10px;   width: '. ($vw+78) .'px; }
#slider' . $player->id . ' .viewport { float: left; width: ' . $vw . 'px; overflow: hidden; position: relative; }
#slider' . $player->id . ' .buttons { background:url("' . $buttons . '") no-repeat scroll 0 0 transparent; display: block; margin: ' . (($vh / 2) - 17) . 'px 0px 0 0; background-position: 0 -38px; text-indent: -999em; float: left; width: 39px; height: 37px; overflow: hidden; position: relative; }
#slider' . $player->id . ' .next { background-position: 0 0; margin: ' . (($vh / 2) - 17) . 'px 0 0 0px;  }
#slider' . $player->id . ' .disable { visibility: hidden; }
#slider' . $player->id . ' .overview { list-style: none; position: absolute; padding: 0; margin: 0; width: ' . $vw . 'px; left: 0 top: 0; }
#slider' . $player->id . ' .overview li{ float: left; margin: 0 20px 0 0; padding: 1px; height: 100%; border: 0px solid #dcdcdc; width: ' . $vw . 'px;}
</style>';
		$embed .= '';
		$embed .= '<script src="' . $slider . '"></script>';
		$embed .= '<script type="text/javascript">
		var $j = jQuery.noConflict();
		$j(document).ready(function(){
			$j("#slider' . $player->id . '").slider({ display: 1 });
		});
		</script>';
		$embed .= '<div id="slider' . $player->id . '">';
		$embed .= '<a class="buttons prev" href="#">left</a>';
		$embed .= '<div id="viewport' . $player->id . '" class="viewport">';
		$embed .= '<ul id="overview' . $player->id . '" class="overview">';
		
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
				
				$embed .= '<div style="cursor: pointer; margin:' . $ypos . 'px 0px 0px ' . $xpos . 'px; ' . $css . ' ">';
				$embed .= '<div style="width:' . $gallery->width . 'px; height:' . $gallery->height . 'px;"><img onclick="changePlayer(\'' . $item->id . '\',\'' . $player->id . '\');" style="height:' . $gallery->height . 'px; width:' . $gallery->width . 'px; text-decoration:none;" src="' . $item->thumb . '" width="' . $gallery->width . '" height="' . $gallery->height . '" title="' . 'Click to Watch : ' . $item->title . '" border="0"/></div>';
				$embed .= '<div style="width:' . $gallery->width . 'px; margin:2px; 2px;"><a onclick="changePlayer(\'' . $item->id . '\',\'' . $player->id . '\');" style="text-decoration:none;" title="' . $item->title . '">' . $item->title . '</a></div>';
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
		$embed .= '<script type="text/javascript">
				$j(window).load(function(){
				$j("#viewport' . $player->id . '").css("height",$j("#overview' . $player->id . '").outerHeight());
			});</script>';
	}
	
	if($isHtml5 == true || $gallery->id){
		$embed .= '<script>
			function changePlayer(video,div){
				$j.ajax({
				    url: location.href,
				    type: "post",
					headers : { "cache-control": "no-cache" },
				    data: {
				        action:"flashvars",id:div,vid:video
				    },
				    dataType: "json",
				    success: function (response) {';
		if($isHtml5 == true){
			$embed .= '$j("#"+div+"hdwplaylist").click();';
		}
		$embed .= '
				        var code = "";
						code = response.html5;
						var divid = "player_div"+div;
						document.getElementById(divid).innerHTML = code;
				    }
				});
			}
			</script>';
	}
	return $embed;
}

add_shortcode ( 'hdwplayer', 'hdwplayer_plugin_shortcode' );

?>
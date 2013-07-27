<?php

/******************************************************************
/* User Function
******************************************************************/
require_once( dirname(__FILE__) . '/config.php');

function hdwplayer_plugin_shortcode( $atts ) {
	global $wpdb;
	if(!$atts['id']) $atts['id'] = 1;;
	$player = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."hdwplayer WHERE id=".$atts['id']);
	
 	$siteurl = get_option('siteurl');
	$src     = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/player.swf';
	
	$flashvars = 'baseW='.$siteurl.'&hdwid='.$player->id;

	$embed  = '';
	$html5  = '';

	if($player->videoid) {
		$results = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE id=".$player->videoid);
	} else if ($player->playlistid) {
		$results = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE playlistid=".$player->playlistid." LIMIT 1");
	}
		
	switch($results->type) {
		case 'youtube' :
	    	$url_string = parse_url($results->video, PHP_URL_QUERY);
  	    	parse_str($url_string, $args);
	    	$html5  = '<iframe title="YouTube video player" width="'.$player->width.'" height="'.$player->height.'" src="http://www.youtube.com/embed/'.$args['v'].'" frameborder="0" allowfullscreen></iframe>';
			break;
		case 'dailymotion':
	 		$html5  = '<iframe frameborder="0" width="'.$player->width.'" height="'.$player->height.'" src="'.$results->video.'"></iframe>';
			break;
		case 'rtmp':
			$url_string = str_replace('rtmp', 'http', $results->streamer).'/'.$results->video.'/playlist.m3u8';
	 		$html5   = '<video onclick="this.play();" width="'.$player->width.'" height="'.$player->height.'" controls>';
  	    	$html5  .= '<source src="'.$url_string.'" />';
			$html5  .= '</video>';
			break;
		default :
    		$html5  = '<video onclick="this.play();" width="'.$player->width.'" height="'.$player->height.'" controls>';
  	    	$html5 .= '<source src="'.$results->video.'" />';
			$html5 .= '</video>';
	}
	
	$embed .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'.$player->width.'" height="'.$player->height.'">';
	$embed .= '<param name="movie" value="'.$src .'" />';
	$embed .= '<param name="allowfullscreen" value="true" />';
	$embed .= '<param name="allowscriptaccess" value="always" />';
	$embed .= '<param name="flashvars" value="'.$flashvars.'" />';
    $embed .= '<object type="application/x-shockwave-flash" data="'.$src .'" width="'.$player->width.'" height="'.$player->height.'">';
	$embed .= '<param name="movie" value="'.$src .'" />';
	$embed .= '<param name="allowfullscreen" value="true" />';
	$embed .= '<param name="allowscriptaccess" value="always" />';
	$embed .= '<param name="flashvars" value="'.$flashvars.'" />';
	$embed .= '<p>'.$html5.'</p>';
	$embed .= '</object>';
	$embed .= '</object>';

	return $embed;
} 

add_shortcode('hdwplayer', 'hdwplayer_plugin_shortcode');

?>
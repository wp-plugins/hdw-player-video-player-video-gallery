<?php

/******************************************************************
/*Bootstrap file for getting the ABSPATH constant to wp-load.php
/*This is requried when a plugin requires access not via the admin screen.
******************************************************************/
$path  = ''; 


/******************************************************************
/*Cast Numeric values as Boolean
******************************************************************/


add_filter('query_vars','plugin_add_trigger');
function plugin_add_trigger($vars) {
	$vars[] = 'wid';
	$vars[] = 'view';
    $vars[] = 'vid';
    $vars[] = 'pid';
    $vars[] = 'sid';
    $vars[] = 'lic';
    return $vars;
}
 
add_action('template_redirect', 'plugin_trigger_check');
	function plugin_trigger_check() {
		if(get_query_var('wid') && get_query_var('view') == "config"){
			configXml(get_query_var('wid'));
		}else if(get_query_var('vid') && checkL(get_query_var('lic'))){
			videoPlaylist(get_query_var('vid'));
		}else if(get_query_var('pid') && checkL(get_query_var('lic'))){
			playlist(get_query_var('pid'));
		}else if(get_query_var('sid') && checkL(get_query_var('lic'))){
			skinXml(get_query_var('sid'));
		}		  
	}
		
	function configXML($id){
		global $wpdb;
		$id = encrypt_decrypt('decrypt', $id);
		$table_name = $wpdb->prefix."hdwplayer";
		$config  = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id=".trim($id));
		$siteurl = get_option('siteurl');
		$br      = "\n";
		if(!$config->id){
			die('<b><h1>Restricted access</h1></b>');
		}
		srand ((double) microtime( )*1000000);
		$dyn      = rand( );
		$value['token'] = $dyn;
		$wpdb->update($table_name, $value, array('id' => $config->id));
		
		header("content-type:text/xml;charset=utf-8");
		echo '<?xml version="1.0" encoding="utf-8"?>'.$br;
		echo '<config>'.$br;
		echo '<skinMode>'.$config->skinmode.'</skinMode>'.$br;
		echo '<autoStart>'.castAsBoolean($config->autoplay).'</autoStart>'.$br;
		echo '<stretch>'.$config->stretchtype.'</stretch>'.$br;
		echo '<buffer>'.$config->buffertime.'</buffer>'.$br;
		echo '<volumeLevel>'.$config->volumelevel.'</volumeLevel>'.$br;		
		if($config->videoid){
			echo '<playlistXml>'.$siteurl.'/?vid='.$config->videoid.'</playlistXml>'.$br;
		} else {
			echo '<playlistXml>'.$siteurl.'/?pid='.$config->playlistid.'</playlistXml>'.$br;
		}
		echo '<skinXml>'.$siteurl.'/?sid='.$config->id.'</skinXml>';		
		echo '<playlistAutoStart>'.castAsBoolean($config->playlistautoplay).'</playlistAutoStart>'.$br;
		echo '<playlistOpen>'.castAsBoolean($config->playlistopen).'</playlistOpen>'.$br;
		echo '<playlistRandom>'.castAsBoolean($config->playlistrandom).'</playlistRandom>'.$br;
		echo '<emailPhp>'.$siteurl.'/wp-content/plugins/' . basename(dirname(__FILE__)) . '/email.php</emailPhp>'.$br;
		echo '<token>'.$dyn.'</token>'.$br;
		echo '</config>'.$br;
		exit();		
	}
	
	function videoPlaylist($id){
		global $wpdb;		
		$siteurl = get_option('siteurl');
		$br      = "\n";
		
		$config  = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE id=".intval($id));
		$item = $config[0];
		
		header("content-type:text/xml;charset=utf-8");
		echo '<?xml version="1.0" encoding="utf-8"?>'.$br;
		echo '<playlist>'.$br;		
		echo '<media>'.$br;
		echo '<id>'.$item->id.'</id>'.$br;
		echo '<type>'.$item->type.'</type>'.$br;
		echo '<video>'.$item->video.'</video>'.$br;
		if($item->hdvideo) {
			echo '<hd>'.$item->hdvideo.'</hd>'.$br;
		}
		echo '<streamer>'.$item->streamer.'</streamer>'.$br;
		if($item->dvr) {
			echo '<dvr>'.$item->dvr.'</dvr>'.$br;
		}
		echo '<thumb>'.$item->thumb.'</thumb>'.$br;
		if($item->token) {
			echo '<token>'.$item->token.'</token>'.$br;
		}
		echo '<preview>'.$item->preview.'</preview>'.$br;
		echo '<title>'.$item->title.'</title>'.$br;
		echo '</media>'.$br.$br;			
		echo '</playlist>'.$br;
		exit();
	}
	
	function playlist($id){
		global $wpdb;		
		$siteurl = get_option('siteurl');
		$br      = "\n";
		
		$config  = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."hdwplayer_videos WHERE playlistid=".intval($id));
		$count   = count($config);
		
		header("content-type:text/xml;charset=utf-8");
		echo '<?xml version="1.0" encoding="utf-8"?>'.$br;
		echo '<playlist>'.$br;
		
		for ($i=0, $n=$count; $i < $n; $i++) {
			$item = $config[$i];
			$br;
			echo '<media>'.$br;
			echo '<id>'.$item->id.'</id>'.$br;
			echo '<type>'.$item->type.'</type>'.$br;
			echo '<video>'.$item->video.'</video>'.$br;
			if($item->hdvideo) {
				echo '<hd>'.$item->hdvideo.'</hd>'.$br;
			}
			echo '<streamer>'.$item->streamer.'</streamer>'.$br;
			if($item->dvr) {
				echo '<dvr>'.$item->dvr.'</dvr>'.$br;
			}
			echo '<thumb>'.$item->thumb.'</thumb>'.$br;
			if($item->token) {
				echo '<token>'.$item->token.'</token>'.$br;
			}
			echo '<preview>'.$item->preview.'</preview>'.$br;
			echo '<title>'.$item->title.'</title>'.$br;
			echo '</media>'.$br.$br;
		}		
		echo '</playlist>'.$br;
		exit();
	}
	
	function skinXml($id){
		global $wpdb;	
		$config  = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."hdwplayer WHERE id=".$id);
		$siteurl = get_option('siteurl');
		$br      = "\n";
		
		header("content-type:text/xml;charset=utf-8");
		echo '<skin>'.$br;
		echo '<controlbar>'.$br;
        echo '<display>'.castAsBoolean($config->controlbar) .'</display>'.$br;
		echo '</controlbar>'.$br;
		echo '<playpause>'.$br;
		echo '<display>'.castAsBoolean($config->playpause).'</display>'.$br;
		echo '</playpause>'.$br;
		echo '<progressbar>'.$br;
		echo '<display>'.castAsBoolean($config->progressbar).'</display>'.$br;
		echo '</progressbar>'.$br;
		echo '<timer>'.$br;
		echo '<display>'.castAsBoolean($config->timer).'</display>'.$br;
		echo '</timer>'.$br;
		echo '<share>'.$br;
		echo '<display>'.castAsBoolean($config->share).'</display>'.$br;
		echo '</share>'.$br;
		echo '<volume>'.$br;
		echo '<display>'.castAsBoolean($config->volume).'</display>'.$br;
		echo '</volume>'.$br;
		echo '<fullscreen>'.$br;
		echo '<display>'.castAsBoolean($config->fullscreen).'</display>'.$br;
		echo '</fullscreen>'.$br;
		echo '<playdock>'.$br;
		echo '<display>'.castAsBoolean($config->playdock).'</display>'.$br;
		echo '</playdock>'.$br;
		echo '<videogallery>'.$br;
		echo '<display>'.castAsBoolean($config->playlist).'</display>'.$br;
		echo '</videogallery>'.$br;
		echo '</skin>'.$br;
		exit();
	}
	
	function castAsBoolean($val){
		if($val == 1) {
			return 'true';
		} else {
			return 'false';
		}
	}
	
	function encrypt_decrypt($action, $string) {
	   $output = false;  
	
	   if( $action == 'encrypt' ) {
	       $output = (double)$string*525325.24;
	       $output = base64_encode($output);
	   }
	   else if( $action == 'decrypt' ){
	       $output = base64_decode(substr($string,0,-3));
	       $output = (double)$output/525325.24;
	   }
	   return $output;
	}
	
	function  checkL($lic){
		global $wpdb;
		$token = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."hdwplayer");
		$license = array();
		foreach($token as $tok){
			$license[] = trim($tok->token);	
		}		
		if(in_array(trim($lic),$license)){
			return true;
		}
		return false;
		
	}
	

?>
<?php
	
/******************************************************************
/* Deleting the Table Row
******************************************************************/
if($_GET['page'] == 'videos' && $_GET['opt'] == 'delete') {
	$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id=%d",inval($_GET['id'])));
	echo '<script>window.location="?page=videos";</script>';
}

?>
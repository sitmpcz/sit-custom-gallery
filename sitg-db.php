<?php

function sit_gallery_database_table() {

    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->base_prefix}sit_gallery` ( ";
    $sql .= " `pid` bigint(20) NOT NULL auto_increment,";
    $sql .= " `post_id` bigint(20) NOT NULL,";
    $sql .= " `title` varchar(200) NOT NULL,";
    $sql .= " `filename` varchar(200) NOT NULL,";
    $sql .= " `path` varchar(200) NOT NULL,";
    $sql .= " `slug` varchar(200) NOT NULL,";
    $sql .= " `meta` longtext NULL,";
    $sql .= " `date_image` datetime NOT NULL,";
    $sql .= " `date_upload` datetime NOT NULL,";
    $sql .= " `date_update` datetime NOT NULL,";
    $sql .= " `featured` int(1) NOT NULL DEFAULT '0',";
    $sql .= " `sort` int(11) NOT NULL DEFAULT '0',";
    $sql .= " PRIMARY KEY  (pid) ";
    $sql .= ") $charset_collate;";

    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    dbDelta( $sql );
/*
    if( empty( $wpdb->last_error ) ){
        add_action( 'admin_notices', function(){ echo '<div class="updated notice is-dismissible"><p>Tabulka v DB byla vytvořena</p></div>'; } );
    }
    else {
        add_action( 'admin_notices', function(){ echo '<div class="error notice is-dismissible"><p>'. $wpdb->last_error .'</p></div>'; } );
    }
*/
}

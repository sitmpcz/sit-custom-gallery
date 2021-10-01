<?php
/**
 * Plugin Name: SIT galerie
 * Description: Nahrávání a správa galerií k postům
 * Version: 1.2.8
 * Author: SIT:Jaroslav Dvořák
 **/

// Natahneme funkci ktera vytvori tabulku v DB
// pokud neexistuje
require_once "sitg-db.php";

// Zakladni nastaveni
require_once "config.php";

// Vyrobime stranku s nastavenim
require_once "sitg-option-page.php";

// Natahneme potrebny funkce
require_once "sitg-upload.php";
require_once "sitg-images.php";
require_once "sitg-ajax.php";

// Funkce pro frontend do sablon
require_once "sitg-frontend-func.php";

// Po aktivaci pluginu vytvorime tabulku v DB
register_activation_hook( __FILE__, 'sit_gallery_database_table' );

// Definujeme konstantu cesty k souborum pluginu
if ( !defined('SIT_GALLERY_PLUGIN_PATH') ) {
    define( 'SIT_GALLERY_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
}

// Pripojene JS a CSS
// Pro upload pouzivame: https://github.com/danielm/uploader

add_action( 'admin_enqueue_scripts', 'sit_custom_core_scripts' );

function sit_custom_core_scripts() {

	$max_upload_size = get_option("sitg_max_upload_size");
	$max_upload_size = ( $max_upload_size && $max_upload_size <= 32 ) ? $max_upload_size : 10;
	$max_upload_size = 1000000 * $max_upload_size;

    wp_enqueue_script('sitg-vendor-js', SIT_GALLERY_PLUGIN_PATH . 'assets/dist/js/vendor/vendor.min.js', 'jquery', '1.0', true);

    wp_enqueue_script('sitg-core', SIT_GALLERY_PLUGIN_PATH . 'assets/dist/js/sit-gallery-core.min.js', 'jquery', '1.0', true);
    wp_localize_script( 'sitg-core','sitg_ajax', array(
	    	'ajax_url' => admin_url( 'admin-ajax.php' ),
		    'max_upload_size' => $max_upload_size )
    );

    wp_enqueue_style( 'sitg-css', SIT_GALLERY_PLUGIN_PATH . 'assets/dist/css/main.min.css' );

}

// Vytvorime metabox ve kterym se to vsechno bude odehravat
require_once "sitg-metabox.php";

<?php

// Odkaz na stranku v admin
// Submenu Hlavniho nastaveni
add_action( 'admin_menu', 'sitg_add_admin_plugin_menu' );

function sitg_add_admin_plugin_menu(){

	add_submenu_page(
		'options-general.php',
		'SIT galerie',
		'SIT galerie',
		'administrator',
		'sit-gallery-settings',
		'sitg_add_admin_plugin_page' );

	//call register settings function
	add_action( 'admin_init', 'sitg_register_plugin_settings' );
}

function sitg_register_plugin_settings(){

	register_setting( "sitg_options", "sitg_objects" );
	register_setting( "sitg_options", "sitg_debug" );
	register_setting( "sitg_options", "sitg_max_upload_size" );

}

// Stranka nastaveni pluginu
function sitg_add_admin_plugin_page(){
	require_once __DIR__ . "/views/admin-option-page.php";
}

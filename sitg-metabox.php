<?php

// Pridame metabox pro nahrávání a výběr fotek

add_action('add_meta_boxes', 'sitg_custom_gallery_metabox', 10, 2);

function sitg_custom_gallery_metabox( $post_type, $post )
{
	// Pokud se pouzije Polylang, budem ten metabox zobrazovat jen na cesky verzi.
	// Je nainstalovany?
	if ( in_array( 'polylang/polylang.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

		global $polylang;

		// CPT ktery se prekladaji
		$translates_post_types = $polylang->model->options["post_types"];

		// Post type postu
		$post_type = get_post_type( $post->ID );

		if ( in_array( $post_type, $translates_post_types ) ) {

			// Jazyk aktualniho postu musi byt cesky
			if ( pll_get_post_language( $post->ID ) != "cs" ) {
				return;
			}
		}
	}

    // Nazev adresare se vytvarel ze slugu
	// protoze se ale slug muze menit, nazev se bere z CPT slugu
	// proto uz se nemusi cekat, az se galerie ulozi a je public
    //if( $post->post_status != 'auto-draft' ) {
    //    $cb = 'sitg_custom_gallery_metabox_html';
    //}
    //else {
    //    $cb = 'sitg_info_for_auto_draft_post';
    //}
	$cb = 'sitg_custom_gallery_metabox_html';

    // Kde se ma metabox zobrazovat se vybira v "Settings -> SIT galerie"
    $screens = get_option("sitg_objects");

    if ( !$screens ) {
    	return;
    }

    foreach ( $screens as $screen ) {
        add_meta_box(
            'sit_custom_gallery',                    // Unique ID
            'Obrázky do galerie',                  // Box title
            $cb,  // Content callback, must be of type callable
            $screen,                                    // Post type
            'normal',
            'high'
        );

    }

}

function sitg_info_for_auto_draft_post(){
    include("views/metabox-auto-draft-info.php");
}

// Vlozime do metaboxu sablonu
// Kde bude taková jednoduchá správa fotek

function sitg_custom_gallery_metabox_html( $post, $args )
{

	global $sitg_thumb_sizes;

    require_once( __DIR__ . '/config.php' );

    $post_id = $post->ID;

    $data_debug_attr = ( SITG_DEBUG ) ? 1 : 0;

    // Vlozime do metaboxu vsechny nahrany obrazky, pokud existujou

    //$images = get_post_meta( $post->ID, '_sitg_images', true );

    $images = sitg_get_images( $post_id );

    $metabox_content = "";

    if( !empty( $images ) ){

        foreach( $images as $image ){

            $pid        = $image->pid;
            $slug       = $image->slug;
            $path       = $image->path;
            $extension  = pathinfo( $image->filename, PATHINFO_EXTENSION );
            $width      = $sitg_thumb_sizes["admin"]["width"];
            $height     = $sitg_thumb_sizes["admin"]["height"];
            $title      = $image->title;
            $featured   = $image->featured;
            $sort       = $image->sort;

            $img_path = $path . "/thumb/" . $slug . "-" . $width . "x" . $height . "." . $extension;

	        $featured_class = ( $featured == 1 ) ? " is-featured" : "";

            ob_start();
            include( __DIR__ . "/views/metabox-image-html.php" );
            $metabox_content .= ob_get_clean();

        }
    }
    else {
        ob_start();
        include( __DIR__ . "/views/metabox-no-images.php" );
        $metabox_content = ob_get_clean();
    }

    include( "views/metabox-default-html.php");
    include( "views/popup-uploader.php");

}

// Ukladani dat

add_action('save_post', 'sitg_save_data');

function sitg_save_data( $post_id )
{
    require_once( __DIR__ . '/config.php' );

    if ( array_key_exists('_sitg_images', $_POST ) ) {

        // Pripravime data
        $items = $_POST['_sitg_images'];
        $data = [];

        foreach( $items as $id => $path ){
            $data[] = ["pid" => $id, "path" => $path];
        }

        // Ulozime post meta
        update_post_meta(
            $post_id,
            '_sitg_images',
            serialize( $data )
        );

        // Aktualizujeme poradi obrazku
        sitg_update_sort( $data );

    }
}

// Zmena stavu pri editaci postu v admin
// Metabox muzeme zobrazit nejdriv, kdyz je post publish nebo koncept
// Potrebujeme znat SLUG
/*
add_action( 'post_updated', 'sitg_fired_on_post_edit', 10, 3 );

function sitg_fired_on_post_edit( $post_ID, $post_after, $post_before ) {

    if ( $post_after->post_status == 'trash' && $post_before->post_status == 'publish' ) {
        // Do something when post is trashed after being published
    }
    if ( $post_after->post_status == 'publish' && $post_before->post_status == 'trash' ) {
        // Do something when post is published after being trash (post undelete)
    }
}
*/




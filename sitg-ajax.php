<?php

require_once(__DIR__ . '/sitg-images.php');


if ( is_admin() ) {
    add_action('wp_ajax_sitg_ajax_get_image', 'sitg_ajax_get_image');
    add_action('wp_ajax_nopriv_sitg_ajax_get_image', 'sitg_ajax_get_image');
}

function sitg_ajax_get_image(){

	global $sitg_thumb_sizes;

    // Z conf.php tahame velikost obrazku - varianta "admin"
    require_once( __DIR__ . '/config.php' );

    // Pokud nam AJAX nepreda id obrazku serem na to
    if( !isset( $_GET["pid"] ) && !is_int( $_GET["pid"] ) {
        $image = false;
    }

    // Zaznamy k obrazku z DB
    $image = sitg_get_image( $_GET["pid"] );

    // Vytahneme si pro sablonu toho obrazku a hidden input:
    // Status
    // ID obrazku v DB
    // Cestu k thumbnailu, velikost typ "admin" (150x150)
    // Width
    // Height
    if( $image !== false ){

        $pid        = $image->pid;
        $slug       = $image->slug;
        $path       = $image->path;
        $extension  = pathinfo( $image->filename, PATHINFO_EXTENSION );
        $width      = $sitg_thumb_sizes["admin"]["width"];
        $height     = $sitg_thumb_sizes["admin"]["height"];
        $title      = $image->title;
        $sort       = $image->sort;

        $img_path = $path . "/thumb/" . $slug . "-" . $width . "x" . $height . "." . $extension;

        ob_start();
        include( __DIR__ . "/views/metabox-image-html.php" );
        $output = ob_get_clean();

        echo $output;

    } else {

        // Kdyz tohle nastane, neznamena to, ze se nahravani nezdarilo
        // Co s tim?
        echo 'Nahrávání obrázku se nezdařilo';

    }

    wp_die();

}

if ( is_admin() ) {
    add_action('wp_ajax_sitg_ajax_get_images', 'sitg_ajax_get_images');
    add_action('wp_ajax_nopriv_sitg_ajax_get_images', 'sitg_ajax_get_images');
}

function sitg_ajax_get_images(){

	global $sitg_thumb_sizes;

    require_once( __DIR__ . '/config.php' );

    if( !isset( $_POST["sort"] ) && $_POST["sort"] == ""
        && !isset( $_GET["post_id"] ) && !is_int( $_GET["post_id"] ) ) {
        return false;
    }

    $sort = $_POST["sort"];
    $post_id = $_POST["post_id"];

    switch( $sort ){
        case "custom_desc": $order = "sort DESC"; break;
        case "date_asc": $order = "date_image ASC"; break;
        case "date_desc": $order = "date_image DESC"; break;
        case "name_asc": $order = "title ASC"; break;
        case "name_desc": $order = "title DESC"; break;
        case "custom_asc": $order = "sort ASC";
    }

    $output = "";

    $images = sitg_get_images( $post_id, $order );

    if( !empty( $images ) ){

        foreach( $images as $image ){

            $pid        = $image->pid;
            $slug       = $image->slug;
            $path       = $image->path;
            $extension  = pathinfo( $image->filename, PATHINFO_EXTENSION );
            $width      = $sitg_thumb_sizes["admin"]["width"];
            $height     = $sitg_thumb_sizes["admin"]["height"];
            $title      = $image->title;
            $sort       = $image->sort;

            $img_path = $path . "/thumb/" . $slug . "-" . $width . "x" . $height . "." . $extension;

            ob_start();
            include( __DIR__ . "/views/metabox-image-html.php" );
            $output .= ob_get_clean();

        }

    }

    echo $output;

    wp_die();

    return true;
}

// Editace nazvu (zatim pouze) obrazku

if ( is_admin() ) {
	add_action('wp_ajax_sitg_ajax_edit_image', 'sitg_ajax_edit_image');
	add_action('wp_ajax_nopriv_sitg_ajax_edit_image', 'sitg_ajax_edit_image');
}

function sitg_ajax_edit_image(){

	require_once( __DIR__ . '/config.php' );

	header('Content-type:application/json;charset=utf-8');

	$response = false;

	// Pokud nam AJAX nepreda id obrazku serem na to
	if( isset( $_POST["pid"] ) && is_int( $_POST["pid"] ) ) {

		$pid = $_POST["pid"];
		$title = sanitize_text_field( $_POST["title"] );

		$response = sitg_update_title( $pid, $title );
	}
	else {
		echo json_encode([
			'response'    => $response
		]);
	}

	echo json_encode([
		'response'    => $response
	]);

	wp_die();
}

// Nastavovani featured image

if ( is_admin() ) {
	add_action('wp_ajax_sitg_ajax_set_featured_image', 'sitg_ajax_set_featured_image');
	add_action('wp_ajax_nopriv_sitg_ajax_set_featured_image', 'sitg_ajax_set_featured_image');
}

function sitg_ajax_set_featured_image(){

	require_once( __DIR__ . '/config.php' );

	$pid = filter_var( $_POST["pid"], FILTER_VALIDATE_INT );
	$post_id = filter_var( $_POST["post_id"], FILTER_VALIDATE_INT );
	$selected = filter_var( $_POST["selected"], FILTER_VALIDATE_INT );

	$result = sitg_set_featured_image( $pid, $post_id, $selected );

	echo ( $result === false ) ? "unselected" : true;

	wp_die();
}

// Mazani obrázku

if ( is_admin() ) {
    add_action('wp_ajax_sitg_ajax_remove_one_image', 'sitg_ajax_remove_one_image');
    add_action('wp_ajax_nopriv_sitg_ajax_remove_one_image', 'sitg_ajax_remove_one_image');
}

function sitg_ajax_remove_one_image(){

    require_once( __DIR__ . '/config.php' );

    $state = 1;

    // Pokud nam AJAX nepreda id obrazku serem na to
    if( !isset( $_POST["pid"] ) && !is_int( $_POST["pid"] ) ) {
    	$state = 0;
    }

    if( $state > 0 ){

	    $pid = $_POST["pid"];

	    // Vytahneme si info o obrazku
	    $image = sitg_get_image( $pid );

	    if( $image === false ){
		    $state = 0;
	    }

	    // Pak ho smazeme z DB
	    if ( sitg_remove_image_from_db( $pid ) === false ){
		    $state = 0;
	    }

	    // Pak smazeme vsechny obrazky
	    if( sitg_remove_image_file( $image ) === false ){
		    $state = 0;
	    }

    }

    echo json_encode( $state );

    wp_die();

}


// Mazani obrázků
/*
if ( is_admin() ) {
    add_action('wp_ajax_sitg_ajax_remove_images', 'sitg_ajax_remove_images');
    add_action('wp_ajax_nopriv_sitg_ajax_remove_images', 'sitg_ajax_remove_images');
}

function sitg_ajax_remove_images(){

    require_once(__DIR__ . '/conf.php');

    // Pokud nam AJAX nepreda id obrazku serem na to
    if( !isset( $_POST["img_ids"] ) && !is_int( $_POST["img_ids"] ) ) {
        $image = false;
    }

    $images = $_POST["img_ids"];

    if( !empty( $images ) ){

        foreach( $images as $pid ){

            if( sitg_remove_image_file( $pid ) !== false ){
                if ( sitg_remove_image_from_db( $pid ) !== false ){
                    echo "Smazano";
                    return true;
                }
            }

            echo "Nesmazalo se to";
            return false;

        }

    }

    wp_die();

}


if ( is_admin() ) {
    add_action('wp_ajax_sitg_ajax_update_sort', 'sitg_ajax_update_sort');
    add_action('wp_ajax_nopriv_sitg_ajax_update_sort', 'sitg_ajax_update_sort');
}

function sitg_ajax_update_sort(){

    if( !isset( $_POST["sort"] ) && empty( $_POST["sort"] ) ) {
        $sort = false;
    }

    $sort = $_POST["sort"];

    print_r( $sort );

    echo "php sort success";

    wp_die();

}

*/

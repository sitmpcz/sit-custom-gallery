<?php

function sitg_add_image_size( $name, $width = 0, $height = 0, $crop = false ){
	global $sitg_thumb_sizes;

	$sitg_thumb_sizes[ $name ] = array(
		'width'  => absint( $width ),
		'height' => absint( $height ),
		'crop'   => $crop,
	);
}

// Funkce, ktere se pouzivaji v sablonach
// *****

function sitg_gallery_images( $post_id = NULL, $order_by = "sort" ){

    require_once( __DIR__ . '/config.php' );

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    if( NULL !== $post_id ) {

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}$table_name WHERE post_id = '$post_id' ORDER BY $order_by");

    }

    return ( !empty( $results ) ) ? $results : false;

}

function sitg_thumbnail( $pid = NULL, $size = "full" ){

    require_once( __DIR__ . '/config.php' );

    if( NULL === $pid ){
        return false;
    }

    $image = sitg_get_image( $pid );

    if( NULL === $image ) {
        return false;
    }

    $meta = unserialize( $image->meta );

    if( empty( $meta ) ){
        return false;
    }

    if( $size == "full" ){
        $src = '/' . $meta["file"];
        $width = $meta["width"];
        $height = $meta["height"];
        $title = $image->title;
        $id = $image->pid;
    }
    else {
        $sizes = $meta["sizes"];

        if( empty( $sizes ) ){
            return false;
        }

        $size = $sizes[$size];

        $src = '/' . $size["file"];
        $width = $size["width"];
        $height = $size["height"];
        $title = $image->title;
        $id = $image->pid;
    }

    $image_arr = [];

    if ( $src && $width && $height ) {
        $image_arr = array( $src, $width, $height, $title, $id );
    }

    return ( !empty( $image_arr ) ) ? $image_arr : false;

}

function sitg_featured_image( $post_id = NULL ){

	require_once( __DIR__ . '/config.php' );

	return sitg_get_featured_image( $post_id );

}

function sitg_first_thumbnail( $post_id = NULL ){

    require_once( __DIR__ . '/config.php' );

    $first_thumb = false;

    if( NULL !== $post_id ) {

        $images = sitg_get_images( $post_id );

        if( $images ){

            $first_thumb = $images[0];

        }

    }

    return $first_thumb;

}

function sitg_gallery_count( $post_id ){

    require_once( __DIR__ . '/config.php' );

    $count = sitg_count( $post_id );

    return ( $count !== false ) ? $count : 0;
}

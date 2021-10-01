<?php

function sitg_get_image( $pid = false ){

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    $row = NULL;

    if( false !== $pid ){

        $row = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}$table_name WHERE pid = '$pid'" );

    }

    return ( NULL !== $row ) ? $row : false;

}

function sitg_get_images( $post_id = false, $order_by = "sort" ){

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    if( false !== $post_id ) {

        $results = $wpdb->get_results("SELECT * FROM {$wpdb->base_prefix}$table_name WHERE post_id = '$post_id' ORDER BY $order_by");

    }

    return ( !empty( $results ) ) ? $results : false;

}

function sitg_remove_image_from_db( $pid = false ){

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    $result = false;

    if( false !== $pid ) {
        $result = $wpdb->delete($wpdb->base_prefix.$table_name, array( 'pid' => $pid ) );
    }

    return $result;

}

function sitg_remove_image_file( $image ){

    $doc_root = $_SERVER["DOCUMENT_ROOT"] . '/';

    // V meta datech mame ulozeny cesty k originalu i k tem nahledum
    // Projdem to a promazem
    $meta = unserialize( $image->meta );

    // Cesta k originalu
    $original = $doc_root . $meta["file"];

    // Thumbnaily
    $thumbs = $meta["sizes"];

    // Smazeme thumbs
    foreach ( $thumbs as $size => $value ){

        $thumb = $doc_root . $value["file"];

        if( is_file( $thumb ) ){
            unlink( $thumb );
        }

    }

    // Smazeme original
    if ( unlink( $original ) !== false ) {
        return true;
    }
    else {
        return false;
    }

}

// Tohle by melo mazat vse
/*
function sitg_rrmdir( $dir ){

    if ( is_dir( $dir ) ) {

        $objects = scandir( $dir );

        foreach ( $objects as $object ) {
            if ( $object != "." && $object != ".." ) {
                if ( filetype($dir . "/" . $object ) == "dir" ){
                    sitg_rrmdir( $dir . "/" . $object );
                }
                else {
                    unlink   ( $dir . "/" . $object );
                }
            }
        }

        reset( $objects );
        rmdir( $dir );
    }

}
*/

function sitg_get_max_sort(){

    global $wpdb;
    $table_name = SITG_TBL_NAME;
    return  $wpdb->get_var( "SELECT MAX(sort) FROM {$wpdb->base_prefix}$table_name" );

}

function sitg_update_sort( $data ){

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    if( !empty( $data ) ){

        $i = 1;

        foreach( $data as $key => $value ){

            $wpdb->update( $wpdb->base_prefix.$table_name, array( "sort" => $i ), array( "pid" => $value["pid"] ) );

            $i++;
        }

    }

}

function sitg_count( $post_id = false ){

    global $wpdb;
    $table_name = SITG_TBL_NAME;

    if( false !== $post_id ){
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->base_prefix}$table_name WHERE post_id = '$post_id'" );
    }

    return ( NULL !== $count ) ? $count : false;
}

function sitg_update_title( $pid = false, $title = "" ){

	global $wpdb;
	$table_name = SITG_TBL_NAME;

	$result = false;

	if( false !== $pid ) {
		$result = $wpdb->update( $wpdb->base_prefix . $table_name, array( "title" => $title ), array( "pid" => $pid ) );
	}

	return $result;
}

function sitg_set_featured_image( $pid = false, $post_id = false, $selected = 0 ){

	global $wpdb;
	$table_name = SITG_TBL_NAME;

	$result = false;

	// Odznacime vsechny
	$wpdb->update( $wpdb->base_prefix . $table_name, array( "featured" => 0 ), array( "post_id" => $post_id ) );

	// Pokud neni vybrany, oznacime vybrany
	if ( false !== $pid && false !== $post_id && $selected == 0 ) {
		$result = $wpdb->update( $wpdb->base_prefix . $table_name, array( "featured" => 1 ), array( "pid" => $pid ) );
	}

	return $result;
}

function sitg_get_featured_image( $post_id = NULL ){

	global $wpdb;
	$table_name = SITG_TBL_NAME;

	$row = false;

	if( NULL !== $post_id ) {
		$row = $wpdb->get_row( "SELECT * FROM {$wpdb->base_prefix}$table_name WHERE post_id = '$post_id' AND featured = 1" );
	}

	return ( NULL !== $row ) ? $row : false;
}


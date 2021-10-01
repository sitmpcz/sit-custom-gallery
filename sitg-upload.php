<?php

if ( is_admin() ) {
	add_action('wp_ajax_sitg_ajax_upload', 'sitg_ajax_upload');
	add_action('wp_ajax_nopriv_sitg_ajax_upload', 'sitg_ajax_upload');
}

function sitg_ajax_upload(){

	if ( isset( $_FILES['file'] ) ) {

		sit_handle_upload( $_FILES['file'] );

	} else {

		sitg_echo_json_message( false );

	};

	wp_die();
}

// ---------------------------------------------------------------------------------------------------------------------
// Nahravani souboru na server
function sit_handle_upload( $file ){

    // nastavime vlastni adresare
    add_filter( 'upload_dir', 'sitg_change_upload_dir' );

    $upload_overrides = array( 'test_form' => false );

    // Nahrajeme to do vlastni adresarove struktury
    $result = wp_handle_upload( $file, $upload_overrides );

    // Nakonec to nastavime zase zpatky na default hodnoty
    remove_filter( 'upload_dir', 'sitg_change_upload_dir' );

    if( !isset( $result["file"] ) ) {

        sitg_echo_json_message(false);

        return false;
    }

    $file = $result["file"];

    // Musime ten obrazek odrotovat pokud ma spatnou orientaci
    sitg_fix_image_rotation( $file );

    // Info o souboru vytahame z url adresy nahranyho souboru
    $pathinfo = pathinfo( $result["url"] );
    $dirname = parse_url( $pathinfo["dirname"] );

    // Pripravime data pro ulozeni do DB
    $post_id = isset( $_POST["post_id"] ) ? $_POST["post_id"] : 0;
    $title = $pathinfo["filename"];
    $filename = $pathinfo["basename"];
    $path = $dirname["path"];
    $slug = $pathinfo["filename"];
    $date = date("Y-m-d H:i:s");
    // Datum vytvoreni obrazku
    $date_image = sitg_get_image_date( $file );

    // Vytvorime vsechny nahledy
    // Zpatky dostaneme pole s informacema o vygenerovanych nahledech
    $sizes = sitg_resize( $result["file"], $path );

    // Kdyz se povede vygenerovat nahledy
    // Ulozime data do DB
    $db_result = false;

    if( !empty( $sizes ) ){

        // Sestavime meta data do DB
        $meta = sitg_generate_meta_data( $file, $sizes );

        $sort = sitg_get_max_sort();

        $data = array(
            "post_id"       => $post_id,
            "title"         => $title,
            "filename"      => $filename,
            "path"          => $path,
            "slug"          => $slug,
            "date_image"    => $date_image,
            "date_upload"   => $date,
            "meta"          => $meta,
            "sort"          => $sort + 1,
        );

        // Ulozime to do DB
        $db_result = sitg_insert_data_db( $data );

    }

    // Mame orezano, ulozeno a zapsano v DB
    // Vypiseme OK hlasku
    if( $db_result !== false ){

        $data = $db_result;

        sitg_echo_json_message( true, $data );

        return true;
    }
    else {

        sitg_echo_json_message( false );

        return false;
    }

}

function sitg_resize( $image_full_path, $dir_path ){

	global $sitg_thumb_sizes;

	if ( !file_exists( $image_full_path ) ) {
		return false;
	}

    // Zajimava stranka
    // https://bhoover.com/wp_image_editor-wordpress-image-editing-tutorial/

    require_once( __DIR__ . '/config.php' );

    // Budeme sem hazet detaily o vygenerovanych nahledech
    $sizes = [];

    // $thumb_sizes tahame z conf.php
    foreach( $sitg_thumb_sizes as $key => $value ){

        $img = wp_get_image_editor( $image_full_path );

        if ( is_wp_error( $img ) ) {
            return false;
        }

        // Zmenime velikost originalu (setrime misto na serveru)
        // Nova velikost originalu je nastavena na 1.5 x 1920
        // Pak vytvorime thumbnaily
        // Nastaveni velikosti mame v conf.php
        $resize = $img->resize( $value["width"], $value["height"], $value["crop"] );

        if( $resize === false ) {
            return false;
        }

        if ( $key == "full" ) {

            $img->set_quality( SITG_ORIGINAL_IMG_QUALITY );
            $sizes[ $key ] = $img->save( $image_full_path );

        } else {

            $img->set_quality( SITG_THUMB_IMG_QUALITY );
            $sizes[ $key ] = $img->save( str_replace( $dir_path, $dir_path . "/thumb", $img->generate_filename() ) );

        }

    }

    return $sizes;

}

function sitg_fix_image_rotation( $file ){

    $suffix = substr( $file, strrpos( $file, '.', -1 ) + 1 );
    if ( !in_array( strtolower( $suffix ), array( 'jpg', 'jpeg', 'tiff' ), true ) ) {
        return false;
    }

    $exif = exif_read_data( $file );

    if ( isset( $exif ) && isset( $exif['Orientation'] ) && $exif['Orientation'] > 1 ) {

        $operations = sitg_calculate_flip_and_rotate( $file, $exif );

        if ( false !== $operations ) {
            sitg_do_flip_and_rotate( $file, $operations );
        }

    }

    return true;
}

function sitg_do_flip_and_rotate( $file, $operations ){

    $editor = wp_get_image_editor( $file );

    if ( ! is_wp_error( $editor ) ) {
        // Lets rotate and flip the image based on exif orientation.
        if ( true === $operations['rotator'] ) {
            $editor->rotate( $operations['orientation'] );
        }
        if ( false !== $operations['flipper'] ) {
            $editor->flip( $operations['flipper'][0], $operations['flipper'][1] );
        }
        $editor->save( $file );

        return true;
    }

    return false;
}

function sitg_calculate_flip_and_rotate( $file, $exif ){

    $rotator     = false;
    $flipper     = false;
    $orientation = 0;

    // Lets switch to the orientation defined in the exif data.
    switch ( $exif['Orientation'] ) {
        case 1:
            // We don't want to fix an already correct image :).
            return false;
        case 2:
            $flipper = array( false, true );
            break;
        case 3:
            $orientation = -180;
            $rotator     = true;
            break;
        case 4:
            $flipper = array( true, false );
            break;
        case 5:
            $orientation = -90;
            $rotator     = true;
            $flipper     = array( false, true );
            break;
        case 6:
            $orientation = -90;
            $rotator     = true;
            break;
        case 7:
            $orientation = -270;
            $rotator     = true;
            $flipper     = array( false, true );
            break;
        case 8:
        case 9:
            $orientation = -270;
            $rotator     = true;
            break;
        default:
            $orientation = 0;
            $rotator     = true;
            break;
    }

    return compact( 'orientation', 'rotator', 'flipper' );

}

function sitg_insert_data_db( $data ){

    global $wpdb;

    $table = $wpdb->prefix.'sit_gallery';

    $result = $wpdb->insert( $table, array(
        "post_id"           => $data["post_id"],
        "title"             => $data["title"],
        "filename"          => $data["filename"],
        "path"              => $data["path"],
        "slug"              => $data["slug"],
        "meta"              => $data["meta"],
        "date_image"        => $data["date_image"],
        "date_upload"       => $data["date_upload"],
        "sort"              => $data["sort"],
    ) );

    if( $result !== false ){
        // Vratime ID zaznamu
        $result = array( "id" => $wpdb->insert_id, "sort" => $data["sort"] );
    }
    else {
        return false;
    }

    return $result;

}

// Nastaveni vlastniho adresare a podadresare
function sitg_change_upload_dir( $url ) {

    $post_id = isset( $_POST["post_id"] ) ? $_POST["post_id"] : "";

    if( $post_id !== "" ) {

        $post = get_post( $post_id );

        // Jako hlavni adresar pro media galerii pouzivame sit-gallery
        $new_basedir = "sit-gallery";

        $url["basedir"] = str_replace( "uploads", $new_basedir, $url["basedir"] );
        $url["baseurl"] = str_replace( "uploads", $new_basedir, $url["baseurl"] );

        // Do adresare sit-gallery vytvarime podadresare podle post_type
        // kdyby se to nahodou pouzivalo treba i pro novinky apod.
        $post_type = get_post_type( $post_id );

        $new_subdir = "/" . $post_type;

        // ADRESARE NEBUDEME POJMENOVAVAT PODLE SLUGU - MUZE SE ZMENIT!
        // BUDEME POUZIVAT POST TYPE
        // # Kazda galerie ma vlastni adresar
        // # Metabox pro upload zobrazujeme az, kdyz je post ulozeny
        // # Takze ten slug zname, ale kdyby nahodou tak adresar nazveme ID-gallery
        // # $post_name = ( $post->post_name ) ? $post->post_name : "gallery";
        $new_galdir = "/" . $post_id . "-" . $post_type;

        $new_path = $new_basedir . $new_subdir . $new_galdir;

        $url["path"]    = str_replace( "uploads" . $url["subdir"], '', $url["path"] ); //remove default subdir
        $url["url"]     = str_replace( "uploads" .$url["subdir"], '', $url["url"] );
        $url["subdir"]  = $new_path;
        $url["path"]   .= $new_path;
        $url["url"]    .= $new_path;
    }

    return $url;

}

function sitg_generate_meta_data( $orig_file, $sizes ){

    // Prvni je ten upraveny original
    $original   = $sizes["full"];

    $file       = _sitg_relative_upload_path( $original["path"] );
    $width      = $original['width'];
    $height     = $original['height'];

    // Ostatni jsou ty dalsi
    $subsizes = [];
    $i = 0;
    foreach( $sizes as $key => $value ){
        // Prvni je ten upraveny original
        if ( $i > 0 ){
            // File, Width, Height, Mime-type
            $subsizes[ $key ] = array(
                "file"      => _sitg_relative_upload_path( $value['path'] ),
                "width"     => $value['width'],
                "height"    => $value['height'],
                "mime-type" => $value['mime-type'],
            );
        }
        $i++;
    }

    // K vytazeni meta dat muzeme pouzijeme WP funkci
    // wp-admin/includes/image.php -> wp_read_image_metadata
    // Struktura dat
    //array(
    //    'aperture'          => 0,
    //    'credit'            => '',
    //    'camera'            => '',
    //    'caption'           => '',
    //    'created_timestamp' => 0,
    //    'copyright'         => '',
    //    'focal_length'      => 0,
    //    'iso'               => 0,
    //    'shutter_speed'     => 0,
    //    'title'             => '',
    //    'orientation'       => 0,
    //    'keywords'          => array(),
    //);
    $image_meta = wp_read_image_metadata( $orig_file );

    // Konecna struktura metadat:
    // (Vychazime z toho, co tam uklada Wordpress)
    // width
    // height
    // file
    // sizes array( size array( file, width, height, mime-type ) )

    $meta = array(
        'width'         => $width,
        'height'        => $height,
        'file'          => $file,
        'sizes'         => $subsizes,
        'image_meta'    => $image_meta,
    );

    return serialize( $meta );

}

function sitg_get_image_date( $file ){

    // Vytahneme meta data pro datum
    $exif_data = exif_read_data( $file, 'IFD0' );

    // Potrebujem datum originalu
    // Kdyz se nam nepodari vytahnout zadna data
    // Nastavime obrazku dnesni datum
    $date_image = date("Y-m-d H:i:s");

    // Tohle nevraci dobry datum - potrebujeme spis datum originalu
    //if( $meta['created_timestamp'] ){
    //    $date_image = new DateTime( $meta['created_timestamp'] );
    //    $date_image = $date_image->format("Y-m-d H:i:s");
    //}
    //else {
    //    $date_image = $date;
    //}

    // Pro $date_image_d pouzivame $date_image_d->format()
    $date_image_d = "";

    if ( $exif_data !== false ) {

        // Tenhle udaj tam nemusi byt
        if (array_key_exists("DateTimeOriginal", $exif_data)) {
            $date_image_d = new DateTime($exif_data["DateTimeOriginal"]);
        } // Tenhle taky ne
        elseif (array_key_exists("DateTime", $exif_data)) {
            $date_image_d = new DateTime($exif_data["DateTime"]);
        } // Tenhle by tam mel byt vzdy
        else {
            $date_image = date("Y-m-d H:i:s", $exif_data["FileDateTime"]);
        }

        $date_image = ($date_image_d) ? $date_image_d->format("Y-m-d H:i:s") : $date_image;

    }

    return $date_image;

}

function _sitg_relative_upload_path( $file ){

    $root = $_SERVER['DOCUMENT_ROOT'];

    if ( 0 === strpos( $file, $root ) ) {
        $file   = str_replace( $root, '', $file );
        $file   = ltrim( $file, '/' );
    }

    return $file;

}

function sitg_echo_json_message( $state = true, $data = [] ){

    header('Content-type:application/json;charset=utf-8');

    if( $state === true ){

        echo json_encode([
            'status'    => 'ok',
            'file_id'   => $data["id"],
            'sort'      => $data["sort"]
        ]);

    } else {

        echo json_encode([
            'status' => 'error',
            'message' => 'Nahrávání obrázku se nezdařilo'
        ]);

    }

}

<?php

// Kdyz je true, zobrazime debug block v upload okne
// A data-debug na = 1 pro kontrolu pres JS
// Zapina se v Settings -> SIT galerie
$debug = get_option("sitg_debug");
$debug = ( $debug == 1 ) ? true : false;
define( "SITG_DEBUG", $debug );

define( "SITG_TBL_NAME", "sit_gallery" );

$sitg_thumb_sizes = [
    "full"      => ["width" => 2880, "height" => 2880, "crop" => false],
	"admin"     => ["width" => 160, "height" => 160, "crop" => ['center', 'center']], // Tohle zatim nemenit, nemame to pripraveny na zmenu thumbnailu za pochodu
];

define( "SITG_ORIGINAL_IMG_QUALITY", 90 );
define( "SITG_THUMB_IMG_QUALITY", 80 );
<?php
//include my core functions
include_once('fnc.core.php');
//add the menu
add_action('admin_menu', 'waraPay_menu_constructor');
//init
add_action('init', 'waraPay_init');
//admin_init
add_action('admin_init', 'waraPay_admin_init');
//register the taxonomy
//add_action( 'admin_init', 'waraPay_register_taxonomy' );
//load the languages pack
add_action('init', 'waraPay_languages');
//add a short code of my plugin for pages
add_shortcode('zfb', 'waraPay_shortcode_parser');
//dos with first active my plugin
register_activation_hook(__FILE__, 'waraPay_activate');
add_action('init', 'waraPay_request_handle');


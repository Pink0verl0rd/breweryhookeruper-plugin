<?php

/**
 * 
 * Plugin Name: BreweryHookerUper Plugin
 * Description: This is a test plugin
 * Version: 1.0.0
 * Text Domain: breweryhookeruper-plugin
 * 
 */

 // Check to make sure the method of acces is Abasulte from root
 if( !defined('ABSPATH') ) {

    die('You cannot be here');

 }
 if( !class_exists( 'ContactPlugin' ) ) {
 class ContactPlugin {

    public function __construct() {

        define('BREWERYHOOKERUPER_PATH', plugin_dir_path( __FILE__ ));

        define('BREWERYHOOKERUPER_URL', plugin_dir_url( __FILE__ ));

        // require_once(BREWERYHOOKERUPER_PATH . '/vendor/autoload.php');

    }

    public function initialize(){

        // include_once BREWERYHOOKERUPER_PATH . 'includes/utils.php';

        // include_once BREWERYHOOKERUPER_PATH . 'includes/options-page.php';

        include_once BREWERYHOOKERUPER_PATH . 'functions.php';

    }

 }

 $contactPlugin = new ContactPlugin;

 $contactPlugin->initialize();

}
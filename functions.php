<?php

add_action('init','register_brewery_cpt');
add_shortcode('brew','get_breweries_from_api');
add_action('wp_ajax_nopriv_get_breweries_from_api', 'get_breweries_from_api');
add_action('wp_ajax_get_breweries_from_api', 'get_breweries_from_api');

function register_brewery_cpt(){
    register_post_type('brewery', [
        'label' => 'Breweries',
        'public' => true,
        'capability_type' => 'post',
    ]);
}


function get_breweries_from_api(){
    $file = BREWERYHOOKERUPER_PATH . '/report.txt';
    $per_page = 50;
    $current_page = ( ! empty($_POST['current_page']) ) ? $_POST['current_page'] : 1;
    $breweries = [];

    $results = wp_remote_retrieve_body(wp_remote_get('https://api.openbrewerydb.org/breweries?page=' 
        . $current_page 
        . '&per_page=' 
        . $per_page
    ));
    file_put_contents($file,"Current Page: " . $current_page . "\n\n", FILE_APPEND);
    
    $results = json_decode($results);

    if ( ! is_array( $results ) || empty( $results ) ) {
        return false;
    }

    $breweries[] = $results;

    foreach ($breweries[0] as $brewery){

        $brewery_slug = sanitize_title($brewery->name. '-' . $brewery->id);
        $inserted_brewery = wp_insert_post([
            'post_name' => $brewery_slug,
            'post_title' => $brewery_slug,
            'post_type' => 'brewery',
            'post_status' => 'publish',
        ]);

        if ( is_wp_error( $inserted_brewery ) )  {
            continue;
        }

        $fillable = [
            'field_64e79f11772ad' => 'name',
            'field_64e79f66772ae' => 'brewery_type',
            'field_64e79f75772af' => 'address',
            'field_64e79f7d772b0' => 'city',
            'field_64e79f84772b1' => 'state_province',
            'field_64e79f8e772b2' => 'postal_code',
            'field_64e79f95772b3' => 'country',
            'field_64e79fa0772b4' => 'longitude',
            'field_64e79fac772b5' => 'latitude',
            'field_64e79fb1772b6' => 'phone',
            'field_64e79fb7772b7' => 'website_url',
            'field_64e79fc0772b8' => 'state',
            'field_64e79fc5772b9' => 'street',
        ];

        foreach ($fillable as $key => $name) {
            update_field($key,$brewery->$name,$inserted_brewery);
        }
    }

    $current_page++;

    wp_remote_post( admin_url('admin-ajax.php?action=get_breweries_from_api'), [
        'blocking' => false,
        'sslverify' => false,
        'body' => [
            'current_page' => $current_page
        ]
    ]);

}
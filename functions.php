<?php

add_action('init','register_brewery_cpt');
add_action('wp_ajax_nopriv_get_breweries_from_api', 'get_breweries_from_api');
add_action('wp_ajax_get_breweries_from_api', 'get_breweries_from_api');
add_action('wp_ajax_nopriv_brew_delete_all', 'brew_delete_all');
add_action('wp_ajax_brew_delete_all', 'brew_delete_all');

function register_brewery_cpt(){
    register_post_type('brewery', [
        'label' => 'Breweries',
        'public' => true,
        'capability_type' => 'post',
    ]);
}


/**
 * admin-ajax.php?action=get_breweries_from_api
 * 
 */
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

    foreach ( $breweries[0] as $brewery ){

        $brewery_slug = sanitize_title( $brewery->name. '-' . $brewery->id );
        $inserted_brewery = wp_insert_post([
            'post_name' => $brewery_slug,
            'post_title' => $brewery_slug,
            'post_type' => 'brewery',
            'post_status' => 'publish',
        ]);

        $existing_brewery = get_page_by_path($brewery_slug, 'OBJECT', 'brewery');

        if ( is_wp_error( $inserted_brewery ) )  {
            continue;
        }

        if ($existing_brewery === null) {

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
                'field_64e8dcf184ee2' => 'updated_at'
            ];
    
            foreach ($fillable as $key => $name) {
                update_field($key,$brewery->$name,$inserted_brewery);
            }

        } else {
            
            $existing_brewery_id = $existing_brewery->ID;
            $existing_brewery_timestamp = get_field('updated_at',$existing_brewery_id);

            if ( $brewery->updated_at >= $existing_brewery_timestamp ) {

            }

            $fillable = [
                'field_64e79f11772ad' => 'name',
                'field_64e79f66772ae' => 'brewery_type',
                'field_64e79f75772af' => 'address_1',
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
                'field_64e8dcf184ee2' => 'updated_at'
            ];
    
            foreach ($fillable as $key => $name) {
                update_field($key,$brewery->$name,$inserted_brewery);
            }

        }
    }

    $current_page++;

    /*wp_remote_post( admin_url('admin-ajax.php?action=get_breweries_from_api'), [
        'blocking' => false,
        'sslverify' => false,
        'body' => [
            'current_page' => $current_page
        ]
    ]);*/

}

// admin-ajax.php?action=brew_delete_all
function brew_delete_all( ) {
    $allposts = get_posts( array('post_type'=>'brewery','numberposts'=>-1) );
    foreach ($allposts as $eachpost) {
        wp_delete_post( $eachpost->ID, true );
    }
    return 1;
}

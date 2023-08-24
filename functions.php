<?php

add_action('init','register_brewery_cpt');

function register_brewery_cpt(){
    register_post_type('brewery', [
        'label' => 'Breweries',
        'public' => true,
        'capability_type' => 'post',
    ]);
}
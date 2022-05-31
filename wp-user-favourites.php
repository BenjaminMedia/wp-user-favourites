<?php
/**
 * Plugin Name: User Favourites
 * Version: 2.0.1
 * Plugin URI: https://github.com/BenjaminMedia/wp-user-favourites
 * Description: A plugin for marking articles as favourites.
 * Author: Bonnier Interactive
 * License: GPL v3
 */

// Do not access this file directly
if (!defined('ABSPATH')) {
    exit;
}

function loadUserFavourites()
{
    return \Bonnier\WP\UserFavourites\UserFavourites::instance();
}

register_activation_hook(__FILE__, function () {
    \Bonnier\WP\UserFavourites\Repository\DbRepository::createTable();
});

add_action('plugins_loaded', 'loadUserFavourites');

<?php


namespace Bonnier\WP\UserFavourites;


use Bonnier\WP\UserFavourites\Http\Controller;

class UserFavourites
{
    protected static $instance;

    /**
     * Returns the instance of this class.
     */
    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', [__CLASS__, 'registerApiControllers']);
    }

    public static function registerApiControllers()
    {
        $controller = new Controller();
        $controller->register_routes();
    }
}

<?php


namespace Bonnier\WP\UserFavourites;


use Bonnier\WP\UserFavourites\Http\Controller;

class UserFavourites
{
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
        $controller = new Controller();
        $controller->register_routes();
    }
}

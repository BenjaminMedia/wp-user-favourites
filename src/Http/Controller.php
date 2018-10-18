<?php


namespace Bonnier\WP\UserFavourites\Http;


use Bonnier\WP\UserFavourites\Repository\DbRepository;

class Controller extends \WP_REST_Controller
{
    protected $dbRepository;

    public function __construct()
    {
        $this->dbRepository = new DbRepository();
    }

    public function register_routes()
    {
        register_rest_route('app', '/user/favourites/(?P<id>[a-zA-Z0-9-]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'getUserFavourites']
        ]);
        register_rest_route('app', '/user/favourites/(?P<id>[a-zA-Z0-9-]+)', [
            'methods' => \WP_REST_Server::EDITABLE,
            'callback' => [$this, 'setUserFavourites']
        ]);
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getUserFavourites(\WP_REST_Request $request)
    {
        $locale = $request->get_param('lang');
        $userId = $request->get_param('id');

        $results = $this->dbRepository->get($userId);
        $favourites = json_decode($results->favourites);

        return new \WP_REST_Response([
            "data" => $favourites->$locale
        ]);
    }

    /**
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function setUserFavourites(\WP_REST_Request $request)
    {
        $locale = $request->get_param('lang');
        $userId = $request->get_param('id');
        $compositeId = $request->get_param('article_id');

        if ($this->dbRepository->save($userId, $locale, $compositeId)) {
            $response = new \WP_REST_Response([
                "status" => 'ok',
                "message" => 'Favourite was saved!'
            ]);
        } else {
            $response = new \WP_REST_Response([
                "status" => 'error',
                'message' => 'Unable to save favourite'
            ], 409); // 409 Conflict - something went wrong with the DB insert/update
        }

        return $response;
    }
}

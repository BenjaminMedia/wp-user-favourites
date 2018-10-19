<?php


namespace Bonnier\WP\UserFavourites\Repository;


use Bonnier\Willow\MuPlugins\Helpers\LanguageProvider;

class DbRepository
{
    /** @var \wpdb */
    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public static function createTable()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'user_favourites';
        $charset = $wpdb->get_charset_collate();

        $sql = "SET sql_notes = 1;
            CREATE TABLE `wp_user_favourites` (
                `user_id` varchar(36) NOT NULL DEFAULT '',
                `favourites` text NOT NULL,
                `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            SET sql_notes = 1;
            ";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get($userId)
    {
        try {
            $results = $this->db->get_row(
                $this->db->prepare(
                    "SELECT `favourites`
                        FROM wp_user_favourites
                        WHERE `user_id` = %s",
                    $userId
                )
            );
            $favourites = null;
            if ($results) {
                $favourites = json_decode($results->favourites, JSON_OBJECT_AS_ARRAY);
            } else {
                $languages = LanguageProvider::getLanguageList();
                foreach ($languages as $language) {
                    $favourites[$language->slug] = [];
                }
            }
            return $favourites;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function save($userId, $locale, $compositeId)
    {
        $favourites = $this->get($userId);
        array_push($favourites[$locale], $compositeId);
        return $this->updateRow($userId, $favourites);
    }

    public function delete($userId, $locale, $compositeId)
    {
        $favourites = $this->get($userId);
        if (($key = array_search($compositeId, $favourites[$locale])) !== false) {
            unset($favourites[$locale][$key]);
            $favourites[$locale] = array_values($favourites[$locale]);
        }
        return $this->updateRow($userId, $favourites);
    }

    private function updateRow($userId, $favourites)
    {
        $favouriteData = json_encode($favourites);
        try {
            $success = $this->db->query(
                $this->db->prepare(
                    "INSERT INTO wp_user_favourites 
                    (`user_id`, `favourites`) 
                    VALUES (%s, %s)
                    ON DUPLICATE KEY UPDATE `favourites` = %s",
                    $userId,
                    $favouriteData,
                    $favouriteData
                )
            );
            return $success !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}

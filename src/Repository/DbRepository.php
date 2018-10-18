<?php


namespace Bonnier\WP\UserFavourites\Repository;


class DbRepository
{
    /** @var \wpdb */
    protected $db;

    public function __construct()
    {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function get($userId) {
        try {
            return $this->db->get_row(
                $this->db->prepare(
                    "SELECT `favourites`
                        FROM wp_user_favourites
                        WHERE `user_id` = %s",
                    $userId
                )
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    public function save($userId, $locale, $compositeId) {
        $results = $this->get($userId);
        $favourites = json_decode($results->favourites);
        array_push($favourites->$locale, $compositeId);
        $favourites = json_encode($favourites);
        try {
            $success = $this->db->query(
                $this->db->prepare(
                    "INSERT INTO wp_user_favourites 
                    (`user_id`, `favourites`) 
                    VALUES (%s, %s)
                    ON DUPLICATE KEY UPDATE `favourites` = %s",
                    $userId,
                    $favourites,
                    $favourites
                )
            );
            return $success !== false;
        } catch (\Exception $e) {
            return false;
        }
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
}

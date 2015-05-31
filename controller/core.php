<?php

class Mf100RegException extends Exception {

    private $errors;

    function __construct($message, $errors) {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors() {
        return $this->errors;
    }

}

class Mf100RegistrationCore {

    const EMAIL_FIELD = 'user_email';
    const RACE_FIELD = 'trasa';
    const YEAR_FIELD = 'rocnik';
    const FIRST_NAME_FIELD = 'first_name';
    const LAST_NAME_FIELD = 'last_name';
	const BIRTH_FIELD = 'narodeny';

    const META_KEY_PREFIX = 'mf100-';
    const REG_KEY = 'mf100';

    const CRON_TRANSACTIONS = 'mf100transactionscronaction';


    public static function activateCrons() {
        if (!wp_next_scheduled(self::CRON_TRANSACTIONS)) {
            wp_schedule_event(time(), 'hourly', self::CRON_TRANSACTIONS);
        }
    }

    public static function deactivateCrons() {
        wp_clear_scheduled_hook(self::CRON_TRANSACTIONS);
    }


    protected function getRegistrationYears() {
        global $wpdb;

        $select =
            "SELECT `meta_key` FROM `{$wpdb->prefix}usermeta`
                WHERE `meta_key` LIKE '" . self::REG_KEY . "_20%'
                GROUP BY `meta_key`
                ORDER BY `meta_key` DESC";
        $results = $wpdb->get_results($select);

        $aYears = array();
        foreach ($results as $metaKey) {
            $metaKey = $metaKey->meta_key;
            $year = intval(substr($metaKey, strlen(self::REG_KEY) + 1));
            $aYears[$year] = true;
        }

        return $aYears;
    }

    protected function getRegisteredUsersCount($year) {
        global $wpdb;

        $select =
            "SELECT COUNT(*) FROM `{$wpdb->prefix}users` AS `u`
                JOIN `{$wpdb->prefix}usermeta` AS `m`
                    ON `u`.`ID` = `m`.`user_id`
                WHERE `m`.`meta_key` = '" . self::REG_KEY . "_{$year}'";
        $count = intval($wpdb->get_var($select));

        return $count;
    }

    protected function isRegFull($year) {
        $options = Mf100Options::getInstance();
        if ($options->getRegLimit()) {
            $regUsers = $this->getRegisteredUsersCount($year);
            return ($regUsers >= $options->getRegLimit());
        }
        return false;
    }

    protected function sortUsers(&$users, $sortby, $order) {
        if (!$sortby && 'DESC' == $order) {
            $users = array_reverse($users, true);
        } else if (!$sortby) {
            /// do nothing
        } else {
            $function = function($userA, $userB) use ($sortby, $order) {
                if (is_numeric($userA->$sortby)) {
                    $cmp = function($a, $b) use ($order) {
                        if ('ASC' == $order) {
                            return $a - $b;
                        } else {
                            return $b - $a;
                        }
                    };
                } else {
                    $cmp = function($a, $b) use ($order) {
                        if ('ASC' == $order) {
                            strcasecmp($a, $b);
                        } else {
                            strcasecmp($b, $a);
                        }
                    };
                }

                return $cmp($userA->$sortby, $userB->$sortby);
            };

            uasort($users, $function);
        }
    }

    protected function getRegisteredUsers($year, $sortby = '', $order = 'ASC') {
        $rawUsers = get_users(array(
            'meta_key' => self::REG_KEY . '_' . $year,
            'fields' => 'all_with_meta'
        ));

        $users = array();
        foreach ($rawUsers as $user) {
            $wpUser = new Mf100User($user);
            $users[$user->ID] = $wpUser;
        }

        $this->sortUsers($users, $sortby, $order);

        return $users;
    }

    protected function getUnregisteredUsers($year, $sortby = '', $order = 'ASC') {
        global $wpdb;

        $select =
            "SELECT * FROM `{$wpdb->prefix}users` AS `u`
                LEFT JOIN (SELECT DISTINCT `user_id` FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = '" . self::REG_KEY . "_{$year}') AS `m`
                    ON `u`.`ID` = `m`.`user_id`
                WHERE `m`.`user_id` IS NULL";
        $rawUsers = $wpdb->get_results($select);

        $users = array();
        foreach ($rawUsers as $user) {
            $wpUser = new Mf100User($user);
            $users[$user->ID] = $wpUser;
        }

        $this->sortUsers($users, $sortby, $order);

        return $users;
    }

    protected function getAvailableUserMeta() {
        global $wpdb;

        $select =
            "SELECT `meta_key` FROM `{$wpdb->prefix}usermeta`
                WHERE `meta_key` LIKE '" . self::META_KEY_PREFIX . "%'
                GROUP BY `meta_key`";
        $results = $wpdb->get_results($select);

        $return = array();
        foreach ($results as $row) {
            $return[] = substr($row->meta_key, strlen(self::META_KEY_PREFIX));
        }

        return $return;
    }

    protected function showTemplate($name, $vars = array(), $section = 'admin') {
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $$key = $value;
            }
        }

        include(MF100_BASE_PATH . '/view/' . $section . '/' . $name . '.php');
    }
}
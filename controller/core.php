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

    const META_KEY_PREFIX = 'mf100-';
    const REG_KEY = 'mf100';

    protected function registerUser($user, $year, $race) {
        update_user_meta($user->ID, self::REG_KEY . '_' . $year, $race);
    }

    protected function unregisterUser($user, $year) {
        delete_user_meta($user->ID, self::REG_KEY . '_' . $year);
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

    protected function getRegisteredUsers($year) {
        $users = get_users(array(
            'meta_key' => self::REG_KEY . '_' . $year,
            'fields' => 'all_with_meta'
        ));
        return $users;
    }

    protected function prepareMeta($meta) {
        $newMeta = array();
        foreach ($meta as $key => $value) {
            if (is_string($key) && self::META_KEY_PREFIX == substr($key, 0, strlen(self::META_KEY_PREFIX))) {
                $key = substr($key, strlen(self::META_KEY_PREFIX));
            }
            if (!is_array($value[0]) && !is_object($value[0])) {
                $newMeta[$key] = $value[0];
            }
        }
        return $newMeta;
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
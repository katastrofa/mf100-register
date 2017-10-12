<?php

class Mf100UserOptions {

    const OPT_VISIBLE_FIELDS = 'visible-fields';
    const OPTIONS_NAME = 'mf100_user_options';

    private static $INSTANCE = null;

    private $visibleFields = array();


    public static function getInstance() {
        if (null == self::$INSTANCE) {
            self::$INSTANCE = new Mf100UserOptions(get_user_option(self::OPTIONS_NAME));
        }
        return self::$INSTANCE;
    }

    private function __construct($options) {
        if (is_array($options) && isset($options[self::OPT_VISIBLE_FIELDS]) && is_array($options[self::OPT_VISIBLE_FIELDS])) {
            $this->visibleFields = $options[self::OPT_VISIBLE_FIELDS];
        }
    }

    private function generateOptionsArray() {
        return array(
            self::OPT_VISIBLE_FIELDS => $this->visibleFields
        );
    }

    public function storeOptions() {
        $user = wp_get_current_user();
        $options = $this->generateOptionsArray();
        update_user_option($user->ID, self::OPTIONS_NAME, $options);
    }

    public function getVisibleFields() {
        return $this->visibleFields;
    }

    public function isFieldVisible($key) {
        if (isset($this->visibleFields[$key]) && $this->visibleFields[$key]) {
            return true;
        }
        return false;
    }

    public function addVisibleField($key) {
        $this->visibleFields[$key] = true;
    }

    public function removeVisibleField($key) {
        $this->visibleFields[$key] = false;
    }
}
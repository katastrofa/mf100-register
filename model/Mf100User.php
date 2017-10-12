<?php

class Mf100User extends WP_User {

    const FIRST_NAME_FIELD = 'first_name';
    const LAST_NAME_FIELD = 'last_name';
    const EMAIL_FIELD = 'user_email';

    const META_KEY_PREFIX = 'mf100-';
    const REG_KEY = 'mf100';

    private $DO_NOT_DELETE = '/(first_name)|(last_name)|(mf100_[0-9]+(_pay)?)/i';

    private $metaKeys = array();

    public function __construct($id = 0, $name = '', $blog_id = '') {
        parent::__construct($id, $name, $blog_id);

        /// Parse MF100 meta
        $meta = self::getMf100Meta($this->ID);
        foreach (array_keys($meta) as $key) {
            $this->metaKeys[$key] = $key;
        }

        foreach ($meta as $key => $value) {
            $this->$key = $value;
        }

        $this->metaKeys[self::FIRST_NAME_FIELD] = self::FIRST_NAME_FIELD;
        $this->metaKeys[self::LAST_NAME_FIELD] = self::LAST_NAME_FIELD;
    }


    public static function getMf100Meta($idUser) {
        $meta = get_user_meta($idUser);
        $return = array();
        foreach ($meta as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (self::META_KEY_PREFIX == substr($key, 0, strlen(self::META_KEY_PREFIX))) {
                $return[substr($key, strlen(self::META_KEY_PREFIX))] = $value[0];
            }
            if (preg_match('/' . self::REG_KEY . '_[0-9]+(_pay)?/i', $key)) {
                $return[$key] = $value[0];
            }
            if ($key == self::FIRST_NAME_FIELD || $key == self::LAST_NAME_FIELD) {
                $return[$key] = $value[0];
            }
        }

        return $return;
    }


    public function isRegistered($year) {
        $key = self::REG_KEY . '_' . $year;
        if (isset($this->$key)) {
            return $this->$key;
        }
        return false;
    }

    public function register($year, $race) {
        $key = self::REG_KEY . '_' . $year;
        $this->$key = $race;
        update_user_meta($this->ID, $key, $race);
    }

    public function unregister($year) {
        $key = self::REG_KEY . '_' . $year;
        unset($this->$key);
        delete_user_meta($this->ID, $key);
    }

    public function toggleRegister($year, $race) {
        if ($this->isRegistered($year)) {
            $this->unregister($year);
        } else {
            $this->register($year, $race);
        }
    }

    public function validatePayment($year) {
        $key = self::REG_KEY . '_' . $year . '_pay';
        $this->$key = 'yes';
        update_user_meta($this->ID, $key, 'yes');
    }

    public function unvalidatePayment($year) {
        $key = self::REG_KEY . '_' . $year . '_pay';
        unset($this->$key);
        delete_user_meta($this->ID, $key);
    }

    public function getMetaKeys() {
        return $this->metaKeys;
    }

    public function addMetaKey($key) {
        $this->metaKeys[] = $key;
    }

    public function mf100Update($data) {
        foreach ($data as $key => $value) {
            $bPureOption = $key == self::FIRST_NAME_FIELD
                    || $key == self::LAST_NAME_FIELD
                    || 0 < preg_match('/' . self::REG_KEY . '_\d+(?:_pay)?/i', $key);

            if ($bPureOption) {
                if ($value) {
                    update_user_meta($this->ID, $key, $value);
                } else {
                    delete_user_meta($this->ID, $key);
                }
            } else if ($key == self::EMAIL_FIELD) {
                wp_update_user(array('ID' => $this->ID, $key => $value));
            } else {
                update_user_meta($this->ID, self::META_KEY_PREFIX . $key, $value);
            }
        }

        $keys = $this->metaKeys;
        $toRemove = array_diff_key($keys, $data);
        foreach ($toRemove as $key) {
            if (!preg_match($this->DO_NOT_DELETE, $key)) {
                delete_user_meta($this->ID, self::META_KEY_PREFIX . $key);
            }
        }
    }
}


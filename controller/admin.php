<?php

class Mf100RegistrationAdmin extends Mf100RegistrationCore {

    public function __construct() {
        add_action('admin_menu', array($this, 'addUsersMenuPage'));
        add_action('admin_enqueue_scripts', array($this, 'addUsersMenuPageScripts'));
    }


    public function addUsersMenuPage() {
        add_users_page('MF100', 'MF100', 'activate_plugins', 'mf100', array($this, 'showUsersMenuPage'));
    }

    public function addUsersMenuPageScripts($hook) {
        if ('users_page_mf100' == $hook) {
            wp_register_script('mf100-admin-script', MF100_BASE_LINK . 'js/admin.js', array('jquery'), '0.1', true);
            wp_enqueue_script('mf100-admin-script');
        }
    }

    public function showUsersMenuPage() {
        $years = $this->getRegistrationYears();

        $this->showTemplate('users-page', array('years' => $years));
    }
}


$objMf100 = new Mf100RegistrationAdmin();

<?php

class Mf100RegistrationAdmin extends Mf100RegistrationCore {

    public function __construct() {
        add_action('admin_menu', array($this, 'addUsersMenuPage'));
        add_action('admin_menu', array($this, 'addOptionsPage'));
        add_action('admin_init', array($this, 'initOptions'));
        add_action('admin_enqueue_scripts', array($this, 'addUsersMenuPageScripts'));
    }


    public function addOptionsPage() {
        add_options_page(
            'MF100 Settings',
            'MF100 Settings',
            'activate_plugins',
            'mf100-settings',
            array($this, 'showOptionsPage')
        );
    }

    public function initOptions() {
        register_setting('mf100-options', self::OPTIONS_NAME, array($this, 'parseOptions'));

        add_settings_section(
            'mf100-section',
            'MF100 Settings',
            array($this, 'showSectionTitle'),
            'mf100-options-page'
        );

        add_settings_field(
            self::OPT_STOP_REG,
            'Pozastavit registraciu',
            array($this, 'stopRegCallback'),
            'mf100-options-page',
            'mf100-section'
        );
    }

    public function parseOptions($rawOptions) {
        $options = array();
        $options[self::OPT_STOP_REG] = isset($rawOptions[self::OPT_STOP_REG]) ? 'yes' : 'no';
        return $options;
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

    public function showOptionsPage() {
        $this->options = get_option(self::OPTIONS_NAME);
        $this->showTemplate('options-page');
    }

    public function showSectionTitle() {
        echo 'MF100 Options:';
    }

    public function stopRegCallback() {
        $stopReg = (isset($this->options[self::OPT_STOP_REG]) && 'yes' == $this->options[self::OPT_STOP_REG]);
        printf(
            '<input type="checkbox" id="%s" name="mf100_options[%s]" value="yes" %s />',
            self::OPT_STOP_REG,
            self::OPT_STOP_REG,
            ($stopReg) ? 'checked="checked"' : ''
        );
    }
}


$objMf100 = new Mf100RegistrationAdmin();

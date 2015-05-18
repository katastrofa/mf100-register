<?php

class Mf100RegistrationAdmin extends Mf100RegistrationCore {

    public function __construct() {
        add_action('admin_menu', array($this, 'addUsersMenuPage'));
        add_action('admin_menu', array($this, 'addOptionsPage'));
        add_action('admin_init', array($this, 'initOptions'));
        add_action('admin_enqueue_scripts', array($this, 'addUsersMenuPageScripts'));

        add_action('wp_ajax_mf100_update_field_visibility', array($this, 'updateVisibility'));
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
        register_setting('mf100-options', Mf100Options::OPTIONS_NAME, array($this, 'parseOptions'));

        add_settings_section(
            'mf100-section',
            'MF100 Settings',
            array($this, 'showSectionTitle'),
            'mf100-options-page'
        );

        add_settings_field(
            Mf100Options::OPT_STOP_REG,
            'Pozastavit registraciu',
            array($this, 'stopRegCallback'),
            'mf100-options-page',
            'mf100-section'
        );

        add_settings_field(
            Mf100Options::OPT_REG_LIMIT,
            'Limit ucastnikov',
            array($this, 'regLimitCallback'),
            'mf100-options-page',
            'mf100-section'
        );

        add_settings_field(
            Mf100Options::OPT_FIO_TOKEN,
            'Token pre ucet do FIO banky',
            array($this, 'fioTokenCallback'),
            'mf100-options-page',
            'mf100-section'
        );
    }

    public function parseOptions($rawOptions) {
        $options = Mf100Options::getInstance();
        return $options->parseOptionsPage($rawOptions);
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
        $fields = $this->getAvailableUserMeta();

        $this->showTemplate('users-page', array('years' => $years, 'fields' => $fields));
    }

    public function showOptionsPage() {
        $options = Mf100Options::getInstance();
        $this->showTemplate('options-page', array('options' => $options));
    }

    public function showSectionTitle() {
        echo 'MF100 Options:';
    }

    public function stopRegCallback() {
        $options = Mf100Options::getInstance();
        printf(
            '<input type="checkbox" id="%s" name="%s[%s]" value="yes" %s />',
            Mf100Options::OPT_STOP_REG,
            Mf100Options::OPTIONS_NAME,
            Mf100Options::OPT_STOP_REG,
            ($options->isStopReg()) ? 'checked="checked"' : ''
        );
    }

    public function regLimitCallback() {
        $options = Mf100Options::getInstance();
        printf(
            '<input type="text" id="%s" name="%s[%s]" value="%s" />',
            Mf100Options::OPT_REG_LIMIT,
            Mf100Options::OPTIONS_NAME,
            Mf100Options::OPT_REG_LIMIT,
            (is_numeric($options->getRegLimit()) && 0 < $options->getRegLimit()) ? "" . $options->getRegLimit() : ''
        );
    }

    public function fioTokenCallback() {
        $options = Mf100Options::getInstance();
        printf(
            '<input type="text" id="%s" name="%s[%s]" value="%s" class="small-text" />',
            Mf100Options::OPT_FIO_TOKEN,
            Mf100Options::OPTIONS_NAME,
            Mf100Options::OPT_FIO_TOKEN,
            (is_string($options->getFioToken())) ? $options->getFioToken() : ''
        );
    }


    public function updateVisibility() {
        $key = trim($_POST['field']);
        $checked = ('true' === trim($_POST['checked']));

        $userOptions = Mf100UserOptions::getInstance();
        if ($checked) {
            $userOptions->addVisibleField($key);
        } else {
            $userOptions->removeVisibleField($key);
        }
        $userOptions->storeOptions();

        wp_die();
    }
}


$objMf100 = new Mf100RegistrationAdmin();

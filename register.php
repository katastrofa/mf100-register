<?php
/*
Plugin Name: MF100 registration
Description: Registration form and management options for MF100
Version: 0.1
Author: Peter Baran
License: LGPL2
*/

define('MF100_BASE_PATH', dirname(__FILE__));

if (is_admin()) {
    define('MF100_BASE_LINK', plugin_dir_url(__FILE__));

    require(dirname(__FILE__) . '/controller/core.php');
	require(dirname(__FILE__) . '/controller/admin.php');
} else {
    require(dirname(__FILE__) . '/model/FormField.php');
    require(dirname(__FILE__) . '/model/InputField.php');
    require(dirname(__FILE__) . '/model/GeneralInputField.php');
    require(dirname(__FILE__) . '/model/CheckboxInputField.php');
    require(dirname(__FILE__) . '/model/SelectField.php');
    require(dirname(__FILE__) . '/model/FormFieldFactory.php');
    require(dirname(__FILE__) . '/model/FormFieldIterator.php');

    require(dirname(__FILE__) . '/controller/construct.php');
    require(dirname(__FILE__) . '/controller/core.php');
	require(dirname(__FILE__) . '/controller/front.php');
}


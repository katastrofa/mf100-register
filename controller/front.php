<?php



class Mf100RegistrationFront extends Mf100RegistrationCore {

    const REPLACEMENT_REG_FIELD = 'mf100-replacement-reg';

    private $FIELDS = array(
        'trasa' => 'mf100_%year%',
        'platba' => 'mf100_%year%_pay'
    );

    private $objErrors = null;
    private $bUserRegistered = false;
    private $bReplacementRegistered = false;
    private $bSettingsChange = false;
    private $objRegisteredUser = null;
    private $filledValues = false;
    private $bUserLoggedIn = false;

	public function __construct() {
		add_shortcode('mf100_register', array($this, 'parseRegisterForm'));
        add_shortcode('mf100_list', array($this, 'showRegisteredUsers'));
        add_shortcode('mf100_response', array($this, 'showRegistrationResponse'));
        add_shortcode('mf100_login', array($this, 'showCustomLogin'));
		add_action('plugins_loaded', array($this, 'signUpUser'));
        add_action('plugins_loaded', array($this, 'loginUser'));
	}

	protected function addInputField($form, $inputName, $value = '', $type = 'text') {
		$strRegExp = '/<input[^>]*name=["\']' . preg_quote($inputName) . '["\']/imsU';
		if (!preg_match($strRegExp, $form, $match)) {
			$form = "<input type=\"{$type}\" name=\"{$inputName}\" value=\"{$value}\" />\n" . $form;
		}

		return $form;
	}

	protected function addTagAttribute($tag, $attribute, $value) {
		if (preg_match('/' . $attribute . '="(.*)"/iU', $tag)) {
			$tag = preg_replace('/' . $attribute . '="(.*)"/iU', $attribute . '="' . $value . '"', $tag);
		} else {
			$tag = preg_replace('/>/', ' ' . $attribute . '="' . $value . '">', $tag);
		}

		return $tag;
	}

    private function addRequiredFields($formContent, $year) {
        $formContent = $this->addInputField($formContent, 'user_email');
        $formContent = $this->addInputField($formContent, 'mf100-reg', 'yes', 'hidden');
        $formContent = $this->addInputField($formContent, 'rocnik', $year, 'hidden');

        return $formContent;
    }

    private function prefillValues($formFields, $values) {
        if (!is_array($values) || is_object($values)) {
            return;
        }

        foreach ($formFields as $formField) {
            $name = $formField->getName();
            $fillValue = false;
            if (is_array($values) && isset($values[$name])) {
                $fillValue = $values[$name];
            } else if(is_object($values) && property_exists($values, $name)) {
                $fillValue = $values->$name;
            }

            if ($fillValue) {
                $formField->fillValue($fillValue);
                if ('user_email' == $name || !$formField->isEditable()) {
                    $formField->transformToHidden();
                }
            }
        }
    }

    private function parseRequiredFields($formFields) {
        foreach ($formFields as $formField) {
            $formField->parseRequired();
        }
    }

    private function updateAllFieldsInForm($strHtml, $formFields) {
        foreach ($formFields as $formField) {
            $strHtml = str_replace($formField->getOriginalHtml(), $formField->getHtml(), $strHtml);
        }
        return $strHtml;
    }

	protected function parseFormFields($content, $year, $values, $additionalHtml = array()) {
		if (preg_match('/<form[^>]*>(.*)<\/form>/imsU', $content, $match)) {
			$strHtml = $match[1];

			$strHtml = $this->addRequiredFields($strHtml, $year);

            $formFields = new FormFieldIterator();
            $formFields->loadHtml($strHtml);
            $this->prefillValues($formFields, $values);
            $this->parseRequiredFields($formFields);
            $strHtml = $this->updateAllFieldsInForm($strHtml, $formFields);

            if (isset($additionalHtml['before'])) {
                $strHtml = $additionalHtml['before'] . $strHtml;
            }
            if (isset($additionalHtml['after'])) {
                $strHtml .= $additionalHtml['after'];
            }

			$content = str_replace($match[1], $strHtml, $content);
		}

		return $content;
	}

    protected function isError() {
        return (null !== $this->objErrors);
    }

    protected function showErrors($content) {
        $errorText = '<div class="errors">';

        if (is_array($this->objErrors)) {
            foreach ($this->objErrors as $key => $value) {
                $errorText .= "<p>" . $key . " nie je vyplnene</p>\n";
            }
        } else if (is_object($this->objErrors)) {
            $errorText .= "<p>" . $this->objErrors->get_error_message() . "</p>\n";
        } else {
            $errorText .= "<p>" . $this->objErrors . "</p>\n";
        }

        $errorText .= "</div>\n";
        $content = $errorText . $content;
        return $content;
    }

	public function parseRegisterForm($atts, $content = '') {
        if ($this->bUserRegistered && !$this->isError()) {
            return '';
        } else if ($this->isError()) {
            $content = $this->showErrors($content);
        }

        if (!isset($atts['rocnik'])) {
            return '';
        }

        $user = wp_get_current_user();
        if (!is_array($this->filledValues) && 0 != $user->ID) {
            $this->filledValues = array();
            foreach ($user->data as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $this->filledValues[$key] = $value;
                }
            }

            $meta = Mf100User::getMf100Meta($user->ID);
            $this->filledValues = array_merge($this->filledValues, $meta);
            $user = new Mf100User($user->ID);
        } else if (0 != $user->ID) {
            $user = new Mf100User($user->ID);
        }

        $allowReplacement = (0 != $user->ID && $user->isRegistered($atts['rocnik']) && $user->isPayment($atts['rocnik']));

        $options = Mf100Options::getInstance();
        $formAndYearLocated = (0 < preg_match('/<form[^>]*>/imsU', $content, $match))
            && (0 < preg_match("/<form.*<\\/form>/imsU", $content, $matchWhole));
        $submission = (is_array($this->filledValues) && count($this->filledValues) > 0);

        if ($formAndYearLocated && ((!$this->isRegFull($atts['rocnik']) && !$options->isStopReg()) || $submission)) {
            $strForm = str_replace("\n", ' ', $match[0]);
			$strForm = $this->addTagAttribute($strForm, 'action', '');
			$strForm = $this->addTagAttribute($strForm, 'name', 'registerform');

            $formHtml = str_replace($match[0], $strForm, $matchWhole[0]);
            $replacementFormHtml = $formHtml;

            $formHtml = $this->parseFormFields($formHtml, $atts['rocnik'], $this->filledValues);

            $additionalContentAfter = "";
            $additionalContentBefore = "";
            if ($allowReplacement) {
                $json = new stdClass();
                $json->data = $formHtml;
                $json = json_encode($json);

                $replacementFormHtml = $this->parseFormFields(
                    $replacementFormHtml,
                    $atts['rocnik'],
                    array(),
                    array('before' => $this->addInputField('', self::REPLACEMENT_REG_FIELD, 'yes', 'hidden'))
                );

                $jsonOriginal = new stdClass();
                $jsonOriginal->data = $replacementFormHtml;
                $jsonOriginal = json_encode($jsonOriginal);

                $url = plugins_url('js/form.js', dirname(__FILE__));
                $additionalContentAfter =
                    '<script type="text/javascript"> '
                    . 'var mf100Own = ' . $json . ";\n"
                    . 'var mf100Original = ' . $jsonOriginal . ";\n"
                    . "</script>\n"
                    . '<script type="text/javascript" src="' . $url . '"></script>';
                $additionalContentBefore =
                    '<input type="checkbox" name="nahradnik" id="nahradnik" value="yes" /> '
                    . '<label>Registrácia náhradníka / Replacement registration</label>';
            }
            $formHtml = $additionalContentBefore . $formHtml . $additionalContentAfter;

            $content = str_replace($matchWhole[0], $formHtml, $content);

		} else {
            $content = '';
        }

		return $content;
	}

///==========================================================================================
/// Sign up
///==========================================================================================

    protected function validateRequiredFields() {
        $aPosted = $_POST;
        $aErrors = array();

        foreach ($aPosted as $key => $value) {
            if (substr($key, 0, strlen(MF100_REQ_PREFIX)) == MF100_REQ_PREFIX) {
                if ($value) {
                    $_POST[substr($key, 9)] = $value;
                    unset($_POST[$key]);
                } else {
                    $aErrors[substr($key, 9)] = true;
                }
            }
        }

        return $aErrors;
    }

	public function signUpUser() {
		if (isset($_POST['mf100-reg'])) {

            try {
                $errors = $this->validateRequiredFields();
                $this->filledValues = $_POST;
                unset($this->filledValues['rocnik'], $this->filledValues['mf100-reg']);
                if (count($errors) > 0) {
                    throw new Mf100RegException('Some required fields are not set', $errors);
                }

                $email = trim($_POST[self::EMAIL_FIELD]);
                $year = intval(trim($_POST[self::YEAR_FIELD]));
                $race = intval(trim($_POST[self::RACE_FIELD]));

                $user = wp_get_current_user();
                if (0 != $user->ID) {
                    $user = new Mf100User($user->ID);
                    if (isset($_POST[self::REPLACEMENT_REG_FIELD]) && $user->isRegistered($year) && !$user->isPayment($year)) {
                        throw new Mf100RegException(
                            'Registrant musi mat zaplatene',
                            "Registrácia náhradníka je možná iba po zaplatení štartovného poplatku"
                        );
                    }
                }

                $idUser = register_new_user($email, $email);

                $bUserRegistered = is_wp_error($idUser)
                        && ('username_exists' == $idUser->get_error_code() || 'email_exists' == $idUser->get_error_code());
                if (!is_wp_error($idUser) || $bUserRegistered) {
                    if ($bUserRegistered) {
                        $user = get_user_by('email', $email);
                        $user = new Mf100User($user);
                    } else {
                        $user = new Mf100User($idUser);
                    }

                    /// Register new user only if nobody is logged in
                    $currentUser = wp_get_current_user();
                    if (0 != $currentUser->ID) {
                        $currentUser = new Mf100User($currentUser->ID);
                    } else {
                        $user->register($year, $race);
                    }

                    $aValues = $_POST;
                    unset($aValues['user_email'], $aValues['rocnik'], $aValues['mf100-reg']);
                    $user->mf100Update($aValues, "");

                    /// Register replacement and unregister self, move the payment info over
                    if (isset($_POST[self::REPLACEMENT_REG_FIELD]) && 0 != $currentUser->ID) {
                        $currentUser = new Mf100User($currentUser->ID);
                        if ($currentUser->isRegistered($year)) {
                            $currentUser->unregister($year);
                            $user->register($year, $race);
                            if ($currentUser->isPayment($year)) {
                                $user->validatePayment($year);
                                $currentUser->unvalidatePayment($year);
                            }

                            $this->bReplacementRegistered = true;

                            $user = new Mf100User($user->ID);
                            $infoMail =
                                $currentUser->last_name . ' ' . $currentUser->first_name
                                . ' -> zaregistroval nahradnika -> '
                                . $user->last_name . ' ' . $user->first_name;
                            wp_mail(get_option('admin_email'), 'Registracia nahradnika', $infoMail);
                        }
                    } else if (0 != $currentUser->ID) {
                        $this->bSettingsChange = true;
                    }

                    $this->bUserRegistered = true;
                    $this->objRegisteredUser = $user;

                } else if (is_wp_error($idUser)) {
                    throw new Mf100RegException('Problem during user sign-up', $idUser);
                }

            } catch (Mf100RegException $ex) {
                $this->objErrors = $ex->getErrors();
            }
		}
	}

    public function loginUser() {
        if (isset($_POST['mf100-custom-login'])) {
            $user = wp_signon();
            if (is_wp_error($user)) {
                $this->objErrors = $user;
            } else {
                $this->bUserLoggedIn = true;
            }
        }
    }

    public function showCustomLogin($atts, $content = '') {
        $atts = shortcode_atts(
            array('type' => 'form'),
            $atts,
            'mf100_login'
        );
        $user = wp_get_current_user();

        if ('form' == $atts['type']) {
            if ($user->ID || $this->bUserLoggedIn) {
                return '';
            }
        } else if ('response' == $atts['type']) {
            if (is_wp_error($this->objErrors)) {
                $content = "<div>" . $this->objErrors->get_error_message() . '</div>';
            } else if (!$this->bUserLoggedIn && 0 == $user->ID) {
                $content = '';
            }
        } else {
            $content = '';
        }

        return $content;
    }

///==========================================================================================
/// Display registered users
///==========================================================================================

    public function parseUserTemplateCallback($match) {
        if (isset($this->FIELDS[$match[1]])) {
            $replaceField = str_replace('%year%', $this->tmpYear, $this->FIELDS[$match[1]]);
            return $this->tmpUser->$replaceField;
        } else if ('order' == $match[1]) {
            return "" . $this->tmpOrder;
        } else {
            $field = $match[1];
            return $this->tmpUser->$field;
        }
    }

    protected function parseUserTemplate($user, $template, $year, $i) {
        $this->tmpYear = $year;
        $this->tmpUser = $user;
        $this->tmpOrder = $i;
        $template = preg_replace_callback("/\\%([^%]+)\\%/iU", array($this, 'parseUserTemplateCallback'), $template);
        return $template;
    }

    public function showRegisteredUsers($atts, $content = '') {
        $atts = shortcode_atts(
            array('rocnik' => date('Y'), 'sortby' => 'last_name', 'order' => 'ASC'),
            $atts,
            'mf100_list'
        );

        $year = intval($atts['rocnik']);
        $users = $this->getRegisteredUsers($year, $atts['sortby'], $atts['order']);
        $template = $content;
        $content = '';

        $i = 0;
        foreach ($users as $user) {
            $i++;
            $content .= $this->parseUserTemplate($user, $template, $year, $i);
        }

        return $content;
    }

    public function showRegistrationResponse($atts, $content = '') {
        $atts = shortcode_atts(
            array( 'type' => 'success', 'rocnik' => date('Y') ),
            $atts,
            'mf100_response'
        );

        $options = Mf100Options::getInstance();

        if ($this->bUserRegistered && 'success' == $atts['type'] && !$this->bReplacementRegistered && !$this->bSettingsChange) {
            $content = str_replace('%first_name%', $this->objRegisteredUser->first_name, $content);
            $content = str_replace('%last_name%', $this->objRegisteredUser->last_name, $content);
        } else if ('replacement' == $atts['type'] && $this->bReplacementRegistered && !$this->bSettingsChange) {
            $content = str_replace('%first_name%', $this->objRegisteredUser->first_name, $content);
            $content = str_replace('%last_name%', $this->objRegisteredUser->last_name, $content);
        } else if ('settings-change' == $atts['type'] && $this->bSettingsChange) {
            $content = str_replace('%first_name%', $this->objRegisteredUser->first_name, $content);
            $content = str_replace('%last_name%', $this->objRegisteredUser->last_name, $content);
        } else if ($options->isStopReg() && 'reg-stopped' == $atts['type']) {
            // Output the plain text then
        } else if ($this->isRegFull($atts['rocnik']) && 'reg-full' == $atts['type']) {
            // Output the plain text then
        } else {
            $content = '';
        }

        return $content;
    }

}

$objMf100 = new Mf100RegistrationFront();
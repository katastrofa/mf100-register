<?php

/// TODO: Protect registration form
/// TODO: Unregister
/// TODO: Show page options for backend



class Mf100RegistrationFront extends Mf100RegistrationCore {

    private $FIELDS = array(
        'trasa' => 'mf100_%year%'
    );

    private $objErrors = null;
    private $bUserRegistered = false;
    private $objRegisteredUser = null;
    private $filledValues = false;

	public function __construct() {
		add_shortcode('mf100_register', array($this, 'parseRegisterForm'));
        add_shortcode('mf100_list', array($this, 'showRegisteredUsers'));
        add_shortcode('mf100_response', array($this, 'showRegistrationResponse'));
		add_action('plugins_loaded', array($this, 'signUpUser'));
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
                if ('user_email' == $name) {
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

	protected function parseFormFields($content, $year, $values) {
		if (preg_match('/<form[^>]*>(.*)<\/form>/imsU', $content, $match)) {
			$strHtml = $match[1];

			$strHtml = $this->addRequiredFields($strHtml, $year);

            $formFields = new FormFieldIterator();
            $formFields->loadHtml($strHtml);
            $this->prefillValues($formFields, $values);
            $this->parseRequiredFields($formFields);
            $strHtml = $this->updateAllFieldsInForm($strHtml, $formFields);

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
        } else {
            $errorText .= "<p>" . $this->objErrors->get_error_message() . "</p>\n";
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

        $user = wp_get_current_user();
        if (!is_array($this->filledValues) && 0 != $user->ID) {
            $this->filledValues = array();
            foreach ($user->data as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $this->filledValues[$key] = $value;
                }
            }

            $meta = get_user_meta($user->ID);
            $this->filledValues = array_merge($this->filledValues, $this->prepareMeta($meta));
        }

        if (preg_match('/<form[^>]*>/imsU', $content, $match) && isset($atts['rocnik'])) {
			$strForm = str_replace("\n", ' ', $match[0]);

			$strForm = $this->addTagAttribute($strForm, 'action', '');
			$strForm = $this->addTagAttribute($strForm, 'name', 'registerform');

			$content = str_replace($match[0], $strForm, $content);
			$content = $this->parseFormFields($content, $atts['rocnik'], $this->filledValues);

		} else if (!isset($atts['rocnik'])) {
			return "<p>Nastavte rocnik prihlasovacieho formularu</p>";
		} else {
			return "<p>Text shorttagu neobsahuje formular</p>";
		}

		return $content;
	}

///==========================================================================================
/// Sign up
///==========================================================================================

	protected function updateUserMeta($user) {
        $aValues = $_POST;
        unset($aValues['user_email'], $aValues['rocnik'], $aValues['mf100-reg']);

        foreach ($aValues as $key => $value) {
            if ($key == self::FIRST_NAME_FIELD || $key == self::LAST_NAME_FIELD) {
                update_user_meta($user->ID, $key, $value);
            } else {
                update_user_meta($user->ID, self::META_KEY_PREFIX . $key, $value);
            }
        }

        $storedMeta = $this->getMf100Meta($user->ID);
        $metaToRemove = array_diff_key($storedMeta, $aValues);
        foreach ($metaToRemove as $key => $value) {
            delete_user_meta($user->ID, self::META_KEY_PREFIX . $key);
        }
	}

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
                $idUser = register_new_user($email, $email);

                $bUserRegistered = is_wp_error($idUser)
                        && ('username_exists' == $idUser->get_error_code() || 'email_exists' == $idUser->get_error_code());
                if (!is_wp_error($idUser) || $bUserRegistered) {
                    if ($bUserRegistered) {
                        $user = get_user_by('email', $email);
                    } else {
                        $user = get_user_by('id', $idUser);
                    }

                    $this->registerUser($user, $year, $race);
                    $this->updateUserMeta($user);
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

///==========================================================================================
/// Display registered users
///==========================================================================================

    public function parseUserTemplateCallback($match) {
        if (isset($this->FIELDS_MAP[$match[1]])) {
            $replaceField = str_replace('%year%', $this->tmpYear, $this->FIELDS[$match[1]]);
            return $this->tmpUser->$replaceField;
        } else {
            $field = $match[1];
            return $this->tmpUser->$field;
        }
    }

    protected function parseUserTemplate($user, $template, $year) {
        $this->tmpYear = $year;
        $this->tmpUser = $user;
        $template = preg_replace_callback("/\\%([^%]+)\\%/iU", array($this, 'parseUserTemplateCallback'), $template);
        return $template;
    }

    public function showRegisteredUsers($atts, $content = '') {
        if (isset($atts['rocnik'])) {
            $year = intval($atts['rocnik']);
            $users = $this->getRegisteredUsers($year);
            $template = $content;
            $content = '';

            foreach ($users as $user) {
                $content .= $this->parseUserTemplate($user, $template, $year);
            }
        } else {
            $content = "<p>Treba definovat rocnik</p>";
        }

        return $content;
    }

    public function showRegistrationResponse($atts, $content = '') {
        $atts = shortcode_atts(
            array( 'type' => 'success' ),
            $atts,
            'mf100_response'
        );

        if ($this->bUserRegistered && 'success' == $atts['type']) {
            $content = str_replace('%first_name%', $this->objRegisteredUser->first_name, $content);
            $content = str_replace('%last_name%', $this->objRegisteredUser->last_name, $content);
        } else {
            $content = '';
        }

        return $content;
    }

}

$objMf100 = new Mf100RegistrationFront();
<?php

class FormFieldFactory {

    protected $requiredPrefix;

    public function setRequiredField($requiredPrefix) {
        $this->requiredPrefix = $requiredPrefix;
    }

    public function createGeneralInput($html) {
        $obj = new GeneralInputField($html, 'input');
        $obj->setRequiredPrefix($this->requiredPrefix);
        return $obj;
    }

    public function createCheckboxInput($html) {
        $obj = new CheckboxInputField($html, 'input');
        $obj->setRequiredPrefix($this->requiredPrefix);
        return $obj;
    }

    public function createSelect($html) {
        $obj = new SelectField($html, 'select');
        $obj->setRequiredPrefix($this->requiredPrefix);
        return $obj;
    }
}
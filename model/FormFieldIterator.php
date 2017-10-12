<?php

class FormFieldIterator implements Iterator {

    private $formFields = array();
    private $position = 0;

    public function loadHtml($html) {
        global $formFieldFactory;

        $matchInputs = array();
        if (preg_match_all('/<input[^>]*>/imsU', $html, $matchInputs, PREG_SET_ORDER)) {
            foreach ($matchInputs as $input) {
                $type = 'default';
                if (preg_match("/type=[\"'](.*)[\"']/iU", $input[0], $type)) {
                    $type = $type[1];
                }
                $type = strtolower($type);

                switch ($type) {
                    case 'checkbox':
                        $this->formFields[] = $formFieldFactory->createCheckboxInput($input[0]);
                        break;
                    default:
                        $this->formFields[] = $formFieldFactory->createGeneralInput($input[0]);
                }
            }
        }

        $matchSelects = array();
        if (preg_match_all("/<select.*<\\/select>/imsU", $html, $matchSelects, PREG_SET_ORDER)) {
            foreach ($matchSelects as $select) {
                $this->formFields[] = $formFieldFactory->createSelect($select[0]);
            }
        }
    }


    /**
     * @return FormField
     */
    public function current() {
        return $this->formFields[$this->position];
    }

    public function next() {
        ++$this->position;
    }

    public function key() {
        return $this->position;
    }

    public function valid() {
        return isset($this->formFields[$this->position]);
    }

    public function rewind() {
        $this->position = 0;
    }
}
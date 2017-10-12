<?php

class GeneralInputField extends InputField {

    public function fillValue($value) {
        $value = str_replace(array('"', "\\"), '', $value);
        $matches = array();

        if (preg_match('/value=[\'"](.*)[\'"]/i', $this->html, $matches)) {
            $this->html = str_replace($matches[0], "value=\"$value\"", $this->html);
        } else {
            $this->html = preg_replace('/^(<.*)/?>$/iU', "\\1 value=\"$value\" />", $this->html);
        }
        $this->value = $value;
    }

    protected function parseValue($html) {
        $value = array();
        if (preg_match("/value=[\"'](.*)[\"']/iU", $html, $value)) {
            return $value[1];
        }
        return false;
    }

}
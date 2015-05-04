<?php

class CheckboxInputField extends InputField {

    public function fillValue($value) {
        if (!$this->isChecked($this->html)) {
            $this->html = preg_replace("/^(<.*)\\/?>$/iU", "\\1 checked=\"checked\" />", $this->html);
        }
    }

    protected function parseValue($html) {
        $value = array();
        if ($this->isChecked($html) && preg_match("/value=[\"'](.*)[\"']/iU", $html, $value)) {
            return $value[1];
        }
        return false;
    }

    private function isChecked($html) {
        return (preg_match('/checked/iU', $html) > 0);
    }
}
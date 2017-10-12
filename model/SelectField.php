<?php

class SelectField extends FormField {

    private function clearSelected() {
        $this->html = preg_replace('/selected/i', '', $this->html);
        $this->html = preg_replace("/selected=[\"']selected[\"']/i", '', $this->html);
    }

    public function fillValue($value) {
        $this->clearSelected();
        $matches = array();
        $value = preg_quote($value);
        if (preg_match("/<option[^>]+value=[\"']{$value}[\"']/", $this->html, $matches)) {
            $this->html = str_replace($matches[0], $matches[0] . ' selected="selected"', $this->html);
            $this->value = $value;
        }
    }

    public function parseRequiredRegex($matches) {
        $this->prefixHtmlTagName($this->requiredPrefix);
        return $this->getHtml();
    }

    public function parseRequired() {
        $self = $this;
        $this->html = preg_replace_callback(
            '/<select[^>]*required[^>]*>.*<\/select>/imsU',
            array($this, 'parseRequiredRegex'),
            $this->html
        );
    }

    protected function parseValue($html) {
        $option = array();
        if (preg_match('/<option[^>]+selected[^>]*>/imsU', $html, $option)) {
            $value = array();
            if (preg_match("/value=[\"'](.*)[\"']/iU", $option[0], $value)) {
                return $value[0];
            }
        }
        return false;
    }
}
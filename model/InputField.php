<?php

abstract class InputField extends FormField {

    public function parseRequiredRegex($matches) {
        $this->prefixHtmlTagName($this->requiredPrefix);
        return $this->getHtml();
    }

    public function parseRequired() {
        $self = $this;
        $this->html = preg_replace_callback(
            '/<input[^>]*required[^>]*>/imsU',
            array($this, 'parseRequiredRegex'),
            $this->html
        );
    }

}
<?php

abstract class InputField extends FormField {

    public function parseRequired() {
        $self = $this;
        $this->html = preg_replace_callback(
            '/<input[^>]*required[^>]*>/imsU',
            function ($matches) use ($self) {
                $self->prefixHtmlTagName($self->requiredPrefix);
                return $self->getHtml();
            },
            $this->html
        );
    }

}
<?php

abstract class FormField {

    protected $originalHtml;
    protected $html;

    protected $tag;
    protected $name;
    protected $value;

    protected $requiredPrefix;

    public function __construct($originalHtml, $tag) {
        $this->originalHtml = $originalHtml;
        $this->html = $originalHtml;
        $this->tag = $tag;

        $this->name = $this->parseName($originalHtml);
        $this->value = $this->parseValue($originalHtml);
    }

    abstract public function fillValue($value);
    abstract public function parseRequired();
    abstract protected function parseValue($html);

    protected function parseName($html) {
        $name = array();
        if (preg_match('/name=[\'"]([^\'"]*)[\'"]/iU', $html, $name)) {
            return $name[1];
        }
        return false;
    }

    public function getOriginalHtml() {
        return $this->originalHtml;
    }

    public function getHtml() {
        return $this->html;
    }

    public function setHtml($html) {
        $this->html = $html;
    }

    public function getValue() {
        return $this->value;
    }

    public function getName() {
        return $this->name;
    }


    public function setRequiredPrefix($requiredPrefix) {
        $this->requiredPrefix = $requiredPrefix;
    }

    public function prefixHtmlTagName($prefix) {
        if (preg_match('/name=[\'"]([^\'"]*)[\'"]/iU', $this->html, $name)) {
            $this->html = str_replace($name[0], 'name="' . $prefix . $name[1] . '"', $this->html);
        }
    }

    public function transformToHidden() {
        $this->html = '<div class="">' . $this->value . "</div>\n"
                . "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$this->value}\" />";
    }

    public function isEditable() {
        $tag = preg_quote($this->tag);
        return (preg_match('/<'.$tag.'[^>]*no-edit[^>]*>/imsU', $this->originalHtml) <= 0);
    }
}
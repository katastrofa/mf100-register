<?php

class Mf100Options {

    const OPT_STOP_REG = 'stop-reg';
    const OPT_REG_LIMIT = 'reg-limit';
    const OPT_FIO_TOKEN = 'fio-token';
    const OPT_MATCHING_YEAR = 'matching-year';

    const OPTIONS_NAME = 'mf100_options';

    private static $INSTANCE = null;

    private $stopReg = false;
    private $regLimit = false;
    private $fioToken = false;
    private $matchingYear = '';

    public static function getInstance() {
        if (null == self::$INSTANCE) {
            self::$INSTANCE = new Mf100Options(get_option(self::OPTIONS_NAME));
        }
        return self::$INSTANCE;
    }

    private function __construct($options) {
        if (is_array($options)) {
            if (isset($options[self::OPT_STOP_REG])) {
                $this->stopReg = $options[self::OPT_STOP_REG];
            }
            if (isset($options[self::OPT_REG_LIMIT])) {
                $this->regLimit = $options[self::OPT_REG_LIMIT];
            }
            if (isset($options[self::OPT_FIO_TOKEN])) {
                $this->fioToken = $options[self::OPT_FIO_TOKEN];
            }
            if (isset($options[self::OPT_MATCHING_YEAR])) {
                $this->matchingYear = $options[self::OPT_MATCHING_YEAR];
            }
        }
    }

    private function generateOptionsArray() {
        return array(
            self::OPT_STOP_REG => $this->stopReg,
            self::OPT_REG_LIMIT => $this->regLimit,
            self::OPT_FIO_TOKEN => $this->fioToken,
            self::OPT_MATCHING_YEAR => $this->matchingYear
        );
    }

    public function parseOptionsPage($rawOptions) {
        if (isset($rawOptions[self::OPT_STOP_REG])) {
            $this->setStopReg($rawOptions[self::OPT_STOP_REG]);
        } else {
            $this->setStopReg(false);
        }
        if (isset($rawOptions[self::OPT_REG_LIMIT])) {
            $this->setRegLimit($rawOptions[self::OPT_REG_LIMIT]);
        } else {
            $this->setRegLimit(false);
        }
        $this->setFioToken($rawOptions[self::OPT_FIO_TOKEN]);
        $this->setMatchingYear($rawOptions[self::OPT_MATCHING_YEAR]);

        return $this->generateOptionsArray();
    }

    public function storeOptions() {
        $options = $this->generateOptionsArray();
        update_option(self::OPTIONS_NAME, $options);
    }

    public function isStopReg() {
        return $this->stopReg;
    }

    public function setStopReg($stopReg) {
        if (is_string($stopReg)) {
            $stopReg = trim($stopReg);
            $this->stopReg = ('yes' == $stopReg) ? true : false;
        } else {
            $this->stopReg = false;
        }
    }

    public function getRegLimit() {
        return $this->regLimit;
    }

    public function setRegLimit($regLimit) {
        if (is_numeric($regLimit)) {
            $this->regLimit = intval($regLimit);
        } else {
            $this->regLimit = false;
        }
    }

    public function getFioToken() {
        return $this->fioToken;
    }

    public function setFioToken($fioToken) {
        if (is_string($fioToken) && 0 < strlen(trim($fioToken))) {
            $this->fioToken = $fioToken;
        } else {
            $this->fioToken = false;
        }
    }

    public function getMatchingYear() {
        return $this->matchingYear;
    }

    public function setMatchingYear($matchingYear) {
        if (is_string($matchingYear)) {
            $this->matchingYear = trim($matchingYear);
        } else {
            $this->matchingYear = '';
        }
    }
}
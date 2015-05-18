<?php

class BankAccount extends Mf100RegistrationCore {

    public static $TABLE = '';

    public static function init() {
        global $wpdb;
        self::$TABLE = $wpdb->prefix . 'mf100-bank-transactions';
    }

    public static function install() {
        global $wpdb;

        $create =
            "CREATE TABLE IF NOT EXISTS `" . self::$TABLE . "` (
                `id` INT NOT NULL,
                `amount` INT NOT NULL,
                `date` DATETIME NOT NULL,
                `user` INT,
                `data` TEXT,

                PRIMARY KEY (id)
            )";

        $wpdb->query($create);
    }

    private function parseJson($json) {
        $objData = json_decode($json);
        $rawTransactions = $objData->accountStatement->transactionList->transaction;

        $transactions = array();
        foreach ($rawTransactions as $jsonTransaction) {
            $transaction = new Transaction($jsonTransaction);
            $transactions[$transaction->getId()] = $transaction;
        }

        return $transactions;
    }

    private function getRecordsFromDb($from, $to) {
        global $wpdb;

        $select =
            "SELECT FROM `" . self::$TABLE . "` WHERE
                `date` >= '{$from}' AND
                `date` <= '{$to}'";
        $rawTransactions = $wpdb->get_results($select);

        $transactions = array();
        foreach ($rawTransactions as $dbRow) {
            $transaction = new Transaction($dbRow, false);
            $transactions[$transaction->getId()] = $transaction;
        }

        return $transactions;
    }

    private function generateLink($from, $to) {
        $options = Mf100Options::getInstance();

        $from = substr($from, 0, 10);
        $to = substr($to, 0, 10);
        $token = $options->getFioToken();
        $link = "https://www.fio.sk/ib_api/rest/periods/{$token}/{$from}/{$to}/transactions.json";

        return $link;
    }
}

BankAccount::init();

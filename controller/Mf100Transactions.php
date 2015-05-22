<?php

class Mf100Transactions extends Mf100RegistrationCore {

    public static $TABLE = '';

    public static function init() {
        global $wpdb;
        self::$TABLE = $wpdb->prefix . 'mf100_bank_transactions';
    }

    public static function install() {
        global $wpdb;

        $create =
            "CREATE TABLE IF NOT EXISTS `" . self::$TABLE . "` (
                `id` VARCHAR(20) NOT NULL,
                `amount` INT NOT NULL,
                `date` DATETIME NOT NULL,
                `user` INT,
                `data` TEXT,

                PRIMARY KEY (id)
            )";

        $wpdb->query($create);
    }


    public function __construct() {
        add_action(self::CRON_TRANSACTIONS, array($this, 'updateBankMatchings'));
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

    public function getRecordsFromDb($from, $to) {
        global $wpdb;

        $select =
            "SELECT * FROM `" . self::$TABLE . "` WHERE
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

    public function getTransactions() {
        $transactions = $this->getRecordsFromDb('2014-01-01 00:00:00', date('Y-m-d H:i:s'));
        return array(
            'matched' => array_filter($transactions, function($entry) {
                return $entry->getUser();
            }),
            'unmatched' => array_filter($transactions, function($entry) {
                return !$entry->getUser();
            })
        );
    }

    private function generateLink($from, $to, $token) {
        $from = substr($from, 0, 10);
        $to = substr($to, 0, 10);
        $link = "https://www.fio.sk/ib_api/rest/periods/{$token}/{$from}/{$to}/transactions.json";

        return $link;
    }

    private function grabUrl($url) {
        $session = curl_init();
        curl_setopt($session, CURLOPT_URL, $url);
        curl_setopt($session, CURLOPT_HTTPGET, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($session);
        curl_close($session);

        return $output;
    }

    private function updateBankTransactionsFromApi($from, $to, $token) {
        $url = $this->generateLink($from, $to, $token);
        $apiTransactions = $this->grabUrl($url);
        $apiTransactions = $this->parseJson($apiTransactions);
        $dbTransactions = $this->getRecordsFromDb($from, $to);

        $newRecords = array_diff_key($apiTransactions, $dbTransactions);
        foreach ($newRecords as $record) {
            $record->save();
        }
    }

    public function prepareUserNamesCallback($user) {
        $user->first_name = strtolower(trim(remove_accents($user->first_name)));
        $user->last_name = strtolower(trim(remove_accents($user->last_name)));
        return $user;
    }

    private function tryToMatchUser($transactionData, $user) {
        $field = self::BIRTH_FIELD;
	    if (!$transactionData['birth'] || !isset($user->$field) || $transactionData['birth'] != $user->$field) {
            return false;
        }

        foreach ($transactionData['name'] as $namePart) {
            if ($namePart != $user->first_name && $namePart != $user->last_name) {
                return false;
            }
        }

        return true;
    }

    private function matchTransactions($from, $to, $year) {
        $users = $this->getRegisteredUsers($year);
        $users = array_map(array($this, 'prepareUserNamesCallback'), $users);
        $transactions = $this->getRecordsFromDb($from, $to);

        foreach ($transactions as $transaction) {
            if (!$transaction->getUser()) {
                $transactionData = $transaction->getParsedComment();
                foreach ($users as $user) {
                    if ($this->tryToMatchUser($transactionData, $user)) {
                        $transaction->setUser($user->ID);
                        $transaction->save();
                        $this->userPaymentValidated($user, $year);
                    }
                }
            }
        }
    }

    public function updateBankMatchings() {
        $options = Mf100Options::getInstance();
        if ($options->getFioToken() && $options->getMatchingYear()) {
            $to = date('Y-m-d H:i:s');
            $from = date('Y-m-d H:i:s', time() - 604800);

            $this->updateBankTransactionsFromApi($from, $to, $options->getFioToken());
            $this->matchTransactions($from, $to, $options->getMatchingYear());
        }
    }
}

Mf100Transactions::init();
$objMf100Transactions = new Mf100Transactions();

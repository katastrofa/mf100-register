<?php


class Transaction {

    private $id;
    private $amount;
    private $date;
    private $user;

    private $data = array();

    public function __construct($data, $isJson = true) {
        if ($isJson) {
            $this->id = $data->column22->value;
            $this->amount = $data->column1->value;
            $this->date = $this->parseDate($data->column0->value);
            $this->user = null;
            $this->data = $this->parseData($data);
        } else {
            $this->id = intval($data->id);
            $this->amount = intval($data->amount);
            $this->date = $data->date;
            $this->user = ($data->user) ? intval($data->user) : null;
            $this->data = unserialize($data->data);
        }
    }


    private function parseDate($date) {
        $matches = array();
        if (preg_match('/(\d+-\d+-\d+).(\d+)/i', $date, $matches)) {
            return $matches[1] . ' ' . substr($matches[2], 0, 2) . ':' . substr($matches[2], 2) . ':00';
        }
        return date('Y-m-d H:i:s');
    }

    private function parseData($jsonData) {
        $data = array();
        $data['mena'] = $jsonData->column14->value;
        $data['uzivatel'] = $jsonData->column7->value;
        $data['komentar'] = $jsonData->column25->value;
        return $data;
    }

    public function save() {
        global $wpdb;

        $exists = intval($wpdb->get_var("SELECT COUNT(*) FROM `" . Mf100Transactions::$TABLE . "` WHERE `id` = {$this->id}"));
        $data = $wpdb->escape(serialize($this->data));
        $user = ($this->user) ? $this->user : 'NULL';

        if ($exists > 0) {
            /// Update the record
            $query =
                "UPDATE `" . Mf100Transactions::$TABLE . "`
                    SET
                        `amount`={$this->amount},
                        `date`='{$this->date}',
                        `user`={$user},
                        `data`='{$data}'
                    WHERE
                        `id`={$this->id}";
        } else {
            /// Insert new record
            $query =
                "INSERT INTO `" . Mf100Transactions::$TABLE . "` VALUE (
                    {$this->id},
                    {$this->amount},
                    '{$this->date}',
                    {$this->user},
                    '{$this->data}'
                )";
        }

        $wpdb->query($query);
    }


    public function getParsedComment() {
        $parts = preg_replace('/[^a-zA-Z0-9]/i', ' ', strtolower(trim(remove_accents($this->data['komentar']))));
        $parts = array_map('trim', array_filter(explode(' ', $parts)));

        $return = array('birth' => 0, 'name' => array());
        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $return['birth'] = intval($part);
            } else {
                $return['name'][] = trim($part);
            }
        }

        return $return;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
    }
}
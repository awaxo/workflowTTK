<?php

namespace App\Traits;

use Webklex\IMAP\Facades\Client;

trait ImapTrait {

    protected $imapConnection;

    public function connectToImap() {
        $this->imapConnection = Client::account('default');
        $this->imapConnection->connect();
    }

    public function checkImapConnection() {
        return $this->imapConnection->checkConnection();
    }

    public function getImapConnection() {
        return $this->imapConnection;
    }
}

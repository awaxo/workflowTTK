<?php

namespace App\Traits;

use Webklex\IMAP\Facades\Client;

/**
 * ImapTrait provides methods to manage IMAP connections.
 * It includes functionality to connect to an IMAP server,
 * check the connection status, and retrieve the connection instance.
 */
trait ImapTrait {

    protected $imapConnection;

    /**
     * Connect to the IMAP server using the default account configuration.
     * This method initializes the IMAP connection.
     */
    public function connectToImap() {
        $this->imapConnection = Client::account('default');
        $this->imapConnection->connect();
    }

    /**
     * Check the IMAP connection status.
     * This method returns true if the connection is active, false otherwise.
     *
     * @return bool
     */
    public function checkImapConnection() {
        return $this->imapConnection->checkConnection();
    }

    /**
     * Get the IMAP connection instance.
     * This method returns the current IMAP connection instance.
     *
     * @return \Webklex\IMAP\Client
     */
    public function getImapConnection() {
        return $this->imapConnection;
    }
}

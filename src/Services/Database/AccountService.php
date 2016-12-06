<?php

namespace PayPal\Services\Database;

use Plenty\Modules\Plugin\DynamoDb\Contracts\DynamoDbRepositoryContract;

class AccountService extends DatabaseBaseService
{
    protected $tableName = 'settings';

    public function __construct(DynamoDbRepositoryContract $dynamoDbRepositoryContract)
    {
        parent::__construct($dynamoDbRepositoryContract);
    }

    /**
     * Load all accounts from the database
     *
     * @return mixed
     */
    public function getAccounts()
    {
        return json_decode($this->getValue($this->tableName), true);
    }

    /**
     * Load the settings from by the given account id
     *
     * @param $accountId
     * @return array
     */
    public function getAccount($accountId)
    {
        $accounts = $this->getAccounts();
        return $accounts[$accountId];
    }

    public function createAccount($newAccount)
    {
        if($newAccount)
        {
            $accounts = array();
            $accounts = json_decode($this->getValue($this->tableName), true);
            $accounts[$newAccount['email']] = $newAccount;
            $this->setValue($this->tableName, $accounts);
            return true;
        }
    }

    public function updateAccount($updatedAccount)
    {
        $accounts = array();
        if($updatedAccount)
        {
            $accounts = json_decode($this->getValue($this->tableName), true);
            $accounts = array_merge($accounts, $updatedAccount);
            $this->setValue($this->tableName, $accounts);
            return true;
        }
    }

    public function deleteAccount($accountId)
    {
        if($accountId)
        {
            $accounts = json_decode($this->getValue($this->tableName), true);
            if(array_key_exists($accountId, $accounts))
            {
                unset($accounts[$accountId]);
                $this->setValue($this->tableName, $accounts);

                return true;
            }
        }
    }
}
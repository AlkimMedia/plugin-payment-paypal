<?php

namespace PayPal\Services\Database;

use PayPal\Models\Database\Account;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

class AccountService extends DatabaseBaseService
{
    protected $tableName = 'settings';

    public function __construct(DataBase $dataBase)
    {
        parent::__construct($dataBase);
    }

    /**
     * Load all accounts from the database
     *
     * @return mixed
     */
    public function getAccounts()
    {
        return json_decode($this->getValues($this->tableName), true);
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
            $accounts = json_decode($this->getValues($this->tableName), true);
            $accounts[$newAccount['email']] = $newAccount;
            $this->setValue(pluginApp(Account::class));
            return true;
        }
    }

    public function updateAccount($updatedAccount)
    {
        $accounts = array();
        if($updatedAccount)
        {
            $accounts = json_decode($this->getValues($this->tableName), true);
            $accounts = array_merge($accounts, $updatedAccount);
            $this->setValue(pluginApp(Account::class));
            return true;
        }
    }

    public function deleteAccount($accountId)
    {
        if($accountId)
        {
            $accounts = json_decode($this->getValues($this->tableName), true);
            if(array_key_exists($accountId, $accounts))
            {
                unset($accounts[$accountId]);
                $this->setValue(pluginApp(Account::class));

                return true;
            }
        }
    }
}
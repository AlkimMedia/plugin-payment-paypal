<?php

namespace PayPal\Services\Database;

use PayPal\Models\Database\Account;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

class AccountService extends DatabaseBaseService
{
    protected $tableName = 'settings';

    /**
     * AccountService constructor.
     * @param DataBase $dataBase
     */
    public function __construct(DataBase $dataBase)
    {
        parent::__construct($dataBase);
    }

    /**
     * Load all accounts from the database
     *
     * @return array
     */
    public function getAccounts()
    {
        $accounts = array();
        $results = $this->getValues(Account::class);
        if($results)
        {
            foreach ($results as $account)
            {
                if($account instanceof Account)
                {
                    $accounts[$account->id] = $account->value;
                }
            }
        }

        return $accounts;
    }

    /**
     * Load the settings from by the given account id
     *
     * @param $accountId
     * @return array
     */
    public function getAccount($accountId=0)
    {
        if($accountId > 0)
        {
            $account = $this->getValue(Account::class, $accountId);
            if($account instanceof Account)
            {
                return [$account->id => $account->value];
            }
        }
        return null;
    }

    /**
     * @param $newAccount
     * @return bool|null
     */
    public function createAccount($newAccount)
    {
        if($newAccount)
        {
            /** @var Account $accountModel */
            $accountModel = pluginApp(Account::class);
            $accountModel->name = $newAccount['email'];
            $accountModel->value = $newAccount;
            $accountModel->createdAt = date('Y-m-d H:i:s');
            return $this->setValue($accountModel);
        }
        return null;
    }

    /**
     * @param $updatedAccount
     * @return int|null
     */
    public function updateAccount($updatedAccount)
    {
        if(is_array($updatedAccount) && count($updatedAccount) > 0)
        {
            foreach ($updatedAccount as $accountId => $accountData)
            {
                if($accountId > 0 && is_numeric($accountId))
                {
                    // load the account
                    $account = $this->getValue(Account::class, $accountId);

                    if($account instanceof Account)
                    {
                        // do not overwrite webhookId
                        if(array_key_exists('webhookId', $account->value))
                        {
                            $accountData['webhookId'] = $account->value['webhookId'];
                        }

                        $account->value = $accountData;
                        $account->updatedAt = date('Y-m-d H:i:s');
                        $this->setValue($account);
                    }
                }
            }
            return 1;
        }
        return null;
    }

    /**
     * @param $accountId
     * @return bool|null
     */
    public function deleteAccount($accountId)
    {
        if($accountId && $accountId > 0)
        {
            /** @var Account $accountModel */
            $accountModel = pluginApp(Account::class);
            $accountModel->id = $accountId;

            return $this->deleteValue($accountModel);
        }
        return null;
    }
}
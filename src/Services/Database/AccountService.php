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
                    $accounts[] = ["id"=>$account->id, "clientId"=> $account->value['clientId'], 'clientSecret'=>$account->value['clientSecret'], "email"=>$account->value['email']];
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

    public function createAccount($newAccount)
    {
        if($newAccount)
        {
            $accounts = array();
            /** @var Account $accountModel */
            $accountModel = pluginApp(Account::class);
            $accountModel->name = $newAccount['email'];
            $accountModel->value = $newAccount;
            return $this->setValue($accountModel);
        }
        return null;
    }

    public function updateAccount($updatedAccount)
    {
        if(is_array($updatedAccount) && count($updatedAccount) > 0)
        {
            foreach ($updatedAccount as $accountData)
            {
                if(array_key_exists('id', $accountData))
                {
                    $account = $this->getValue(Account::class, $accountData['id']);
                    if($account instanceof Account)
                    {
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
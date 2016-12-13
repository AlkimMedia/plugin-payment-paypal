<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 24.11.16
 * Time: 15:01
 */

namespace PayPal\Controllers;

use PayPal\Services\SettingsService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

class SettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * SettingsController constructor.
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * @param Request $request
     */
    public function createAccount(Request $request)
    {
        $newAccount = $request->get('account');
        if($newAccount)
        {
            $accounts = [];
            $accounts = json_decode($this->settingsService->getSettingsValue(SettingsService::ACCOUNTS), true);
            $accounts[$newAccount['email']] = $newAccount;
            $this->settingsService->setSettingsValue(SettingsService::ACCOUNTS, $accounts);

            $this->loadAccounts();
        }
    }

    public function loadAccounts()
    {
        echo $this->settingsService->getSettingsValue(SettingsService::ACCOUNTS);
    }

    /**
     * @param Request $request
     */
    public function loadAccount(Request $request)
    {
        $accounts = json_decode($this->settingsService->getSettingsValue(SettingsService::ACCOUNTS), true);
        echo $accounts[$request->get('accountId')];
    }

    /**
     * @param Request $request
     */
    public function updateAccount(Request $request)
    {
        $updatedAccount = $request->get('accountId');

        $accounts = [];
        if($updatedAccount)
        {
            $accounts = json_decode($this->settingsService->getSettingsValue(SettingsService::ACCOUNTS), true);
            $accounts = array_merge($accounts, $updatedAccount);
            $this->settingsService->setSettingsValue(SettingsService::ACCOUNTS, $accounts);
        }

        echo "true";
    }

    /**
     * @param Request $request
     */
    public function deleteAccount(Request $request)
    {
        $accountId = $request->get('accountId');
        if($accountId)
        {
            $accounts = json_decode($this->settingsService->getSettingsValue(SettingsService::ACCOUNTS), true);
            if(array_key_exists($accountId, $accounts))
            {
                unset($accounts[$accountId]);
                $this->settingsService->setSettingsValue(SettingsService::ACCOUNTS, $accounts);

                $this->loadAccounts();
            }
        }
    }

    /**
     * @param Request $request
     */
    public function saveSettings(Request $request)
    {
        $this->settingsService->setSettingsValue(SettingsService::SETTINGS, $request->get('settings'));
    }

    /**
     * @return bool|mixed
     */
    public function loadSettings()
    {
        echo $this->settingsService->getSettingsValue(SettingsService::SETTINGS);
    }
}
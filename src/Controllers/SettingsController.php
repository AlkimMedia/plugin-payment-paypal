<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 24.11.16
 * Time: 15:01
 */

namespace PayPal\Controllers;

use PayPal\Services\Database\AccountService;
use PayPal\Services\Database\SettingsService;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;

class SettingsController extends Controller
{
    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * SettingsController constructor.
     * @param SettingsService $settingsService
     * @param AccountService $accountService
     */
    public function __construct(    SettingsService $settingsService,
                                    AccountService $accountService)
    {
        $this->settingsService = $settingsService;
        $this->accountService = $accountService;
    }

    /**
     * @param Request $request
     */
    public function createAccount(Request $request)
    {
        $newAccount = $request->get('account');
        if($newAccount)
        {
            if($this->accountService->createAccount($newAccount))
            {
                $this->loadAccounts();
            }
        }
    }

    public function loadAccounts()
    {
        echo $this->accountService->getAccounts();
    }

    /**
     * @param Request $request
     */
    public function loadAccount(Request $request)
    {
        if($request->get('accountId') && $request->get('accountId') > 0)
        {
            echo $this->accountService->getAccount($request->get('accountId'));
        }
    }

    /**
     * @param Request $request
     */
    public function updateAccount(Request $request)
    {
        $updatedAccount = $request->get('accountId');

        if($this->accountService->updateAccount($updatedAccount))
        {
            echo true;
        }
    }

    /**
     * @param Request $request
     */
    public function deleteAccount(Request $request)
    {
        $accountId = $request->get('accountId');
        if($accountId)
        {
            if($this->accountService->deleteAccount($accountId))
            {
                $this->loadAccounts();
            }
        }
    }

    /**
     * @param Request $request
     */
    public function saveSettings(Request $request)
    {
        if($request->get('settings'))
        {
            if($this->settingsService->saveSettings($request->get('settings')))
            {
                echo true;
            }
        }
    }

    /**
     * @return bool|mixed
     */
    public function loadSettings()
    {
        echo $this->settingsService->loadSettings();
    }
}
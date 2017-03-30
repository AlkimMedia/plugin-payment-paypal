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
use PayPal\Services\NotificationService;
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
     * @var NotificationService
     */
    private $notificationService;

    /**
     * SettingsController constructor.
     * @param SettingsService $settingsService
     * @param AccountService $accountService
     * @param NotificationService $notificationService
     */
    public function __construct(SettingsService $settingsService,
                                AccountService $accountService,
                                NotificationService $notificationService)
    {
        $this->settingsService = $settingsService;
        $this->accountService = $accountService;
        $this->notificationService = $notificationService;
    }

    /**
     * @param Request $request
     */
    public function createAccount(Request $request)
    {
        $newAccount = $request->get('account');

        $webhookId = $this->notificationService->createWebhook($newAccount['clientId'], $newAccount['clientSecret']);

        $newAccount['webhookId'] = $webhookId;

        if($newAccount)
        {
            if($this->accountService->createAccount($newAccount))
            {
                return $this->loadAccounts();
            }
        }
    }

    public function loadAccounts()
    {
        return json_encode($this->accountService->getAccounts());
    }

    /**
     * @param int $accountId
     */
    public function loadAccount($accountId)
    {
        if($accountId && $accountId > 0)
        {
            return $this->accountService->getAccount($accountId);
        }
    }

    /**
     * @param Request $request
     */
    public function updateAccount(Request $request)
    {
        $updatedAccount = $request->get('account');

        return $this->accountService->updateAccount($updatedAccount);
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
                return $this->loadAccounts();
            }
        }
    }

    /**
     * @param Request $request
     */
    public function saveSettings(Request $request)
    {
        if($request->get('PayPalMode') == 'paypal' || $request->get('PayPalMode') == 'paypal_installment')
        {
            return $this->settingsService->saveSettings($request->get('PayPalMode'), $request->get('settings'));
        }
    }

    /**
     * @return bool|mixed
     */
    public function loadSettings($settingType)
    {
        return $this->settingsService->loadSettings($settingType);
    }

    /**
     * Load the settings for one webshop
     *
     * @param $webstore
     */
    public function loadSetting($webstore, $mode)
    {
        return $this->settingsService->loadSetting($webstore, $mode);
    }
}
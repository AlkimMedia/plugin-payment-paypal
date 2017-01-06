<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 05.01.17
 * Time: 12:22
 */

namespace PayPal\Methods;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodService;

class PayPalPlusPaymentMethod extends PaymentMethodService
{
    public function __construct()
    {
    }

    /**
     * Check whether the plugin is active
     *
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Get the name of the plugin
     *
     * @return string
     */
    public function getName()
    {
        $name = '';

        if(!strlen($name))
        {
            $name = 'PayPal Plus';
        }

        return $name;
    }

    /**
     * Get additional costs for PayPal. Additional costs can be entered in the config.json.
     *
     * @return float
     */
    public function getFee()
    {
        $fee = 0;

        return (float)$fee;
    }

    /**
     * Get the path of the icon
     *
     * @return string
     */
    public function getIcon()
    {
        $icon = '';
        return $icon;
    }

    /**
     * Get the description of the payment method. The description can be entered in the config.json.
     *
     * @return string
     */
    public function getDescription()
    {
        $desc = '';

        return $desc;
    }
}
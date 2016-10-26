<?php

namespace PayPal\Migrations;

use Plenty\Modules\Payment\Method\Contracts\PaymentMethodRepositoryContract;
use PayPal\Helper\PaymentHelper;

/**
 * Migration to create payment mehtods
 *
 * Class CreatePaymentMethod
 * @package PayPal\Migrations
 */
class CreatePaymentMethod
{
    /**
     * @var PaymentMethodRepositoryContract
     */
    private $paymentMethodRepositoryContract;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * CreatePaymentMethod constructor.
     *
     * @param PaymentMethodRepositoryContract $paymentMethodRepositoryContract
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(    PaymentMethodRepositoryContract $paymentMethodRepositoryContract,
                                    PaymentHelper $paymentHelper)
    {
        $this->paymentMethodRepositoryContract = $paymentMethodRepositoryContract;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Run on plugin build
     *
     * Create Method of Payment ID for PayPal and PayPal Express if they don't exist
     */
    public function run()
    {
        // Check whether the ID of the PayPal payment method has been created
        if($this->paymentHelper->getPayPalMopId() == 'no_paymentmethod_found')
        {
            $paymentMethodData = array( 'pluginKey' => 'plentyPayPal',
                                        'paymentKey' => 'PAYPAL',
                                        'name' => 'PayPal');

            $this->paymentMethodRepositoryContract->createPaymentMethod($paymentMethodData);
        }

        // Check whether the ID of the PayPal Express payment method has been created
        if($this->paymentHelper->getPayPalExpressMopId() == 'no_paymentmethod_found')
        {
            $paymentMethodData = array( 'pluginKey'   => 'plentyPayPal',
                                        'paymentKey'  => 'PAYPALEXPRESS',
                                        'name'        => 'PayPalExpress');

            $this->paymentMethodRepositoryContract->createPaymentMethod($paymentMethodData);
        }
    }
}
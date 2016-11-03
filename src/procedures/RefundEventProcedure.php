<?php
namespace PayPal\Procedures;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;

/**
 * Class RefundEventProcedure
 * @package PayPal\Procedures
 */
class RefundEventProcedure
{
    /**
     * @param EventProceduresTriggered $eventProceduresTriggered
     * @param LibraryCallContract $libCall
     * @param PaymentService $paymentService
     * @param PaymentHelper $paymentHelper
     * @param PaymentRepositoryContract $paymentContract
     */
    public function run(EventProceduresTriggered $eventProceduresTriggered,
                        LibraryCallContract $libCall,
                        PaymentService $paymentService,
                        PaymentHelper $paymentHelper,
                        PaymentRepositoryContract $paymentContract)
    {
        /** @var Order $order */
        $order = $eventProceduresTriggered->getOrder();

        $payPalRequestParams = $paymentService->getPaypalParams();

        /** @var Payment $payment */
        $payment = $paymentContract->getPaymentsByOrderId($order->id);

        $paymentData = array(   'currency' => $payment->currency,
                                'total'    => $payment->amount);

        $payPalRequestParams['payment'] = $paymentData;

        $result = $libCall->call('PayPal::refundPayment', $payPalRequestParams);
    }
}
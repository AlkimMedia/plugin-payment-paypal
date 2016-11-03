<?php
namespace PayPal\Events;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\EventAction\Events\EventActionTriggered;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;

/**
 * Class RefundEventProcedure
 * @package PayPal\Events
 */
class RefundEventProcedure
{
    /**
     * @param EventActionTriggered  $eventTriggered
     */
    public function run(EventActionTriggered $eventTriggered,
                        LibraryCallContract $libCall,
                        PaymentService $paymentService,
                        PaymentHelper $paymentHelper,
                        PaymentRepositoryContract $paymentContract)
    {
        /** @var Order $order */
        $order = $eventTriggered->getOrder();

        $payPalRequestParams = $paymentService->getPaypalParams();

        /** @var Payment $payment */
        $payment = $paymentContract->getPaymentsByOrderId($order->id);

        $paymentData = array(   'currency' => $payment->currency,
                                'total'    => $payment->amount);

        $payPalRequestParams['payment'] = $paymentData;

        $result = $libCall->call('PayPal::refundPayment', $payPalRequestParams);
    }
}
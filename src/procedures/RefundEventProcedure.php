<?php
namespace PayPal\Procedures;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Payment\Models\Payment;
use Plenty\Modules\Payment\Models\PaymentProperty;
use Plenty\Modules\Payment\Contracts\PaymentPropertyRepositoryContract;
use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;

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
     * @param PaymentService $paymentService
     * @param PaymentRepositoryContract $paymentContract
     * @throws \Exception
     */
    public function run(EventProceduresTriggered $eventProceduresTriggered,
                        PaymentService $paymentService,
                        PaymentRepositoryContract $paymentContract,
                        PaymentHelper $paymentHelper,
                        PaymentPropertyRepositoryContract $paymentPropertyRepositoryContract)
    {
        /** @var Order $order */
        $order = $eventProceduresTriggered->getOrder();

        /** @var Payment[] $payment */
        $payments = $paymentContract->getPaymentsByOrderId($order->id);

        /** @var Payment $payment */
        foreach($payments as $payment)
        {
            if($payment->mopId == $paymentHelper->getPayPalMopId()
            OR $payment->mopId == $paymentHelper->getPayPalExpressMopId())
            {
                $properties = $paymentPropertyRepositoryContract->allByPaymentId($payment->id);

                foreach($properties as $property)
                {
                    if($property->id == 1)  //PaymentProperty::TYPE_TRANSACTION_ID
                    {
                        $payId = $property->value;
                    }
                }

                if(isset($payId))
                {
                    $refundResult = $paymentService->refundPayment($payId);

                    if($refundResult['error'])
                    {
                        throw new \Exception($refundResult['error_msg']);
                    }

                    if($refundResult['state'] == 'failed')
                    {
                        //TODO log the reason_code
                    }
                    else
                    {
                        $paymentData = array(   'status'    => $refundResult['state'],
                                                'currency'  => $refundResult['amount']['currency'],
                                                'amount'    => $refundResult['amount']['total'],
                                                'entryDate' => $refundResult['create_time'],
                                                'payId'     => $refundResult['sale_id'],
                                                'type'      => 'debit');  //Payment::TYPE_DEBIT

                        // if the refund is pending, set the payment unaccountable
                        if($refundResult['state'] == 'pending')
                        {
                            $paymentData['unaccountable'] = 1;  //1 true 0 false
                        }

                        /** @var Payment $payment */
                        $payment = $paymentHelper->createPlentyPayment($paymentData);

                        if($payment instanceof Payment)
                        {
                            if($refundResult['state'] == 'completed')
                            {
                                $paymentHelper->assignPlentyPaymentToPlentyOrder($payment, $order->id);
                            }
                        }
                    }

                    unset($payId);
                }
            }
        }
    }
}
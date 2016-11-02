<?php
namespace PayPal\Events;

use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\EventAction\Events\EventActionTriggered;

use PayPal\Services\PaymentService;
use PayPal\Helper\PaymentHelper;

/**
 * Class RefundEventAction
 * @package PayPal\Events
 */
class RefundEventAction
{
    /**
     * @param EventActionTriggered  $eventTriggered
     */
    public function run(EventActionTriggered $eventTriggered, PaymentService $paymentService, PaymentHelper $paymentHelper)
    {
        /** @var Order $order */
        $order = $eventTriggered->getOrder();

        // TODO
    }
}
<?php
/**
 * Created by IntelliJ IDEA.
 * User: jkonopka
 * Date: 04.11.16
 * Time: 10:00
 */

namespace PayPal\Controllers;

use PayPal\Services\NotificationService;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Controller;

use PayPal\Helper\PaymentHelper;
use PayPal\Constants\NotificationEvents;
use Plenty\Plugin\Log\Loggable;

class PaymentNotificationController extends Controller
{
    use Loggable;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * PaymentNotificationController constructor.
     * @param Request $request
     * @param PaymentHelper $paymentHelper
     * @param NotificationService $notificationService
     */
    public function __construct(Request $request,
                                PaymentHelper $paymentHelper,
                                NotificationService $notificationService)
    {
        $this->request = $request;
        $this->paymentHelper = $paymentHelper;
        $this->notificationService = $notificationService;
    }

    /**
     * PayPal Plugin Notification-Handler
     */
    public function handleNotification()
    {
        $headers = $this->request->header();

        $body = $this->request->getContent();

//        $this   ->getLogger('PaymentNotificationController_handleNotification')
//                ->setReferenceType('Notification')
//                ->setReferenceValue('header')
//                ->info(json_encode($headers));
//
//        $this   ->getLogger('PaymentNotificationController_handleNotification')
//                ->setReferenceType('Notification')
//                ->setReferenceValue('body')
//                ->info($body);

        $validity = $this->notificationService->validateNotification($headers, $body, $this->request->get('id'));

        if($validity)
        {
            $eventType = $this->request->get('event_type');

            switch ($eventType)
            {
                case NotificationEvents::PAYMENT_AUTHORIZATION_CREATED:
                case NotificationEvents::PAYMENT_SALE_PENDING:
                case NotificationEvents::PAYMENT_SALE_DENIED:
                case NotificationEvents::PAYMENT_SALE_COMPLETED:
                case NotificationEvents::PAYMENT_SALE_REFUNDED:
                case NotificationEvents::PAYMENT_SALE_REVERSED:
                case NotificationEvents::INVOICING_INVOICE_PAID:
                case NotificationEvents::INVOICING_INVOICE_REFUNDED:
                case NotificationEvents::INVOICING_INVOICE_CANCELLED:
                    $resource = $this->request->get('resource');
                    $this->paymentHelper->updatePayment($resource['id'], $resource['state']);
                    break;
                default:
                    // We do not handle this event type (yet)
                    break;
            }
        }
    }
}
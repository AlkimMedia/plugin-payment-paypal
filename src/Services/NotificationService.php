<?php //strict

namespace PayPal\Services;

use PayPal\Constants\NotificationEvents;
use PayPal\Helper\PaymentHelper;
use PayPal\Services\PaymentService;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\Log\Loggable;

/**
 * Class NotificationService
 * @package PayPal\Services
 */
class NotificationService
{
    use Loggable;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * @var NotificationEvents
     */
    private $notificationEvents;

    /**
     * @var LibraryCallContract
     */
    private $libCall;

    /**
     * NotificationService constructor.
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param NotificationEvents $notificationEvents
     * @param LibraryCallContract $libraryCallContract
     */
    public function __construct(PaymentHelper $paymentHelper,
                                PaymentService $paymentService,
                                NotificationEvents $notificationEvents,
                                LibraryCallContract $libraryCallContract)
    {
        $this->paymentHelper = $paymentHelper;
        $this->paymentService = $paymentService;
        $this->notificationEvents = $notificationEvents;
        $this->libCall = $libraryCallContract;
    }

    /**
     * @return string
     */
    public function createWebhook()
    {
        $params = $this->paymentService->getApiContextParams();

        $url = $this->paymentHelper->getRestReturnUrls(PaymentHelper::MODE_PAYPAL_NOTIFICATION);
        $params['notificationUrl'] = $url[PaymentHelper::MODE_PAYPAL_NOTIFICATION];

        $params['webhookEvents'][] = NotificationEvents::PAYMENT_AUTHORIZATION_CREATED;
        $params['webhookEvents'][] = NotificationEvents::PAYMENT_SALE_PENDING;
        $params['webhookEvents'][] = NotificationEvents::PAYMENT_SALE_DENIED;
        $params['webhookEvents'][] = NotificationEvents::PAYMENT_SALE_COMPLETED;
        $params['webhookEvents'][] = NotificationEvents::PAYMENT_SALE_REFUNDED;
        $params['webhookEvents'][] = NotificationEvents::PAYMENT_SALE_REVERSED;
        $params['webhookEvents'][] = NotificationEvents::INVOICING_INVOICE_PAID;
        $params['webhookEvents'][] = NotificationEvents::INVOICING_INVOICE_REFUNDED;
        $params['webhookEvents'][] = NotificationEvents::INVOICING_INVOICE_CANCELLED;

        $response = $this->libCall->call('PayPal::createWebhook', $params);

        if(is_array($response) && !empty($response['id']))
        {
            return (string)$response['id'].'_'.(string)$response['url'];
        }
        else
        {
            $this->deleteWebhooks();

            $response = $this->libCall->call('PayPal::createWebhook', $params);

            if(is_array($response) && !empty($response['id']))
            {
                return (string)$response['id'].'_'.(string)$response['url'];
            }
        }

        $this   ->getLogger('NotificationService_createWebhook')
                ->error(json_encode($response));

        return false;
    }

    public function deleteWebhooks()
    {
        $this->libCall->call('PayPal::deleteWebhook', $this->paymentService->getApiContextParams());
    }

    /**
     * @param $headers
     * @param $body
     * @param $webhookId
     * @return bool
     */
    public function validateNotification($headers, $body, $webhookId)
    {
        $params = $this->paymentService->getApiContextParams();

        $params['headers'] = $headers;
        $params['body'] = $body;
        $params['webhookId'] = $webhookId;

        $response = $this->libCall->call('PayPal::validateNotification', $params);

        if(is_array($response) && !empty($response['verification_status']))
        {
            if($response['verification_status'] == 'SUCCESS')
            {
                return true;
            }
        }

        $this   ->getLogger('NotificationService_validateNotification')
                ->setReferenceType('webhookId')
                ->setReferenceValue($webhookId)
                ->error(json_encode($response));

        return false;
    }

    /**
     * @return array
     */
    public function listWebhooks()
    {
        return $this->libCall->call('PayPal::listAvailableWebhooks', $this->paymentService->getApiContextParams());
    }

}
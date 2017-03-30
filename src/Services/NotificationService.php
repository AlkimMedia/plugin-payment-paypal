<?php //strict

namespace PayPal\Services;

use PayPal\Constants\NotificationEvents;
use PayPal\Helper\PaymentHelper;
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
     * @var LibService
     */
    private $libService;

    /**
     * NotificationService constructor.
     * @param PaymentHelper $paymentHelper
     * @param PaymentService $paymentService
     * @param NotificationEvents $notificationEvents
     * @param LibService $libService
     */
    public function __construct(PaymentHelper $paymentHelper,
                                PaymentService $paymentService,
                                NotificationEvents $notificationEvents,
                                LibService $libService)
    {
        $this->paymentHelper = $paymentHelper;
        $this->paymentService = $paymentService;
        $this->notificationEvents = $notificationEvents;
        $this->libService = $libService;
    }

    /**
     * Create the webhooks for the account
     *
     * @param $clientId
     * @param $clientSecret
     * @return bool|string
     */
    public function createWebhook($clientId, $clientSecret)
    {
        $params['clientId'] = $clientId;
        $params['clientSecret'] = $clientSecret;

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

        $response = $this->libService->libCreateWebhook($params);

        if(is_array($response) && !empty($response['id']))
        {
            return (string)$response['id'];
        }
        else
        {
            $this->deleteWebhooks($clientId, $clientSecret);

            $response = $this->libService->libCreateWebhook($params);

            if(is_array($response) && !empty($response['id']))
            {
                return (string)$response['id'];
            }
        }

        $this   ->getLogger('NotificationService_createWebhook')
                ->error(json_encode($response));

        return false;
    }

    /**
     * Delete all webhooks
     *
     * @param $clientId
     * @param $clientSecret
     */
    public function deleteWebhooks($clientId, $clientSecret)
    {
        $params['clientId'] = $clientId;
        $params['clientSecret'] = $clientSecret;

        $this->libService->libDeleteWebhook($params);
    }

    /**
     * Validate a given webhook
     *
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

        $response = $this->libService->libValidateNotification($params);

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
     * List all available webhooks
     *
     * @return array
     */
    public function listWebhooks()
    {
        return $this->libService->libListAvailableWebhooks($this->paymentService->getApiContextParams());
    }
}
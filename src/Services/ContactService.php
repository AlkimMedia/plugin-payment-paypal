<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;

use PayPal\Services\PaymentService;

/**
 * Class ContactService
 * @package PayPal\Services
 */
class ContactService
{
    /**
     * @var ContactRepositoryContract
     */
    private $contactRepository;

    /**
     * @var PaymentService
     */
    private $paymentService;

    /**
     * ContactService constructor.
     * @param ContactRepositoryContract $contactRepository
     * @param PaymentService $paymentService
     */
    public function __construct(ContactRepositoryContract $contactRepository,
                                PaymentService $paymentService)
    {
        $this->contactRepository = $contactRepository;
        $this->paymentService = $paymentService;
    }

    /**
     * Get a contact by ID
     *
     * @param int $contactId
     * @return Contact
     */
    public function getContactById(int $contactId):Contact
    {
        return $this->contactRepository->findContactById($contactId);
    }

    /**
     * Create a contact
     *
     * @param array $contact
     * @return Contact
     */
    public function createContact(array $contact):Contact
    {
        return $this->contactRepository->createContact($contact);
    }

    /**
     * @param $payId
     */
    public function handlePayPalContact($payId)
    {
        $payment = $this->paymentService->getPayPalPayment($payId);

        // TODO add the contact data from paypal or update if contact data changed
    }
}

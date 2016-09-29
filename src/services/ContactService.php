<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;

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
     * ContactService constructor.
     * @param ContactRepositoryContract $contactRepository
     */
    public function __construct(ContactRepositoryContract $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    /**
     * @param int $contactId
     * @return Contact
     */
    public function getContactById(int $contactId):Contact
    {
        return $this->contactRepository->findContactById($contactId);
    }

    /**
     * @param array $contact
     * @return Contact
     */
    public function createContact(array $contact):Contact
    {
        return $this->contactRepository->createContact($contact);
    }
}

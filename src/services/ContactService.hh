<?hh //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;

/**
 * Class ContactService
 * @package PayPal\Services
 */
class ContactService
{
    private ContactRepositoryContract $contactRepository;

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
     * @param array<string, mixed> $contact
     * @return Contact
     */
    public function createContact(array<string, mixed> $contact):Contact
    {
        return $this->contactRepository->createContact($contact);
    }
}

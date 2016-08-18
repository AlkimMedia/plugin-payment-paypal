<?hh //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Account\Contact\Models\Contact;

class ContactService
{
    private ContactRepositoryContract $contactRepository;

    public function __construct(ContactRepositoryContract $contactRepository)
    {
        $this->contactRepository = $contactRepository;
    }

    public function getContactById(int $contactId):Contact
    {
        return $this->contactRepository->findContactById($contactId);
    }

    public function createContact(array<string, mixed> $contact):Contact
    {
        return $this->contactRepository->createContact($contact);
    }
}

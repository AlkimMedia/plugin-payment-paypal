<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Frontend\Services\AccountService;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;

/**
 * Class ContactService
 * @package PayPal\Services
 */
class ContactService
{
    /**
     * @var AddressRepositoryContract
     */
    private $addressContract;

    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * ContactService constructor.
     */
    public function __construct(AddressRepositoryContract $addressRepositoryContract,
                                Checkout $checkout)
    {
        $this->addressContract = $addressRepositoryContract;
        $this->checkout = $checkout;
    }

    /**
     * @param array $payer
     */
    public function handlePayPalContact($payer)
    {
        if(isset($payer['payer_info']['shipping_address']) && !empty($payer['payer_info']['shipping_address']))
        {
            /**
             * Map the PayPal address to a plenty address
             * @var Address $address
             */
            $address = $this->mapPPAddressToAddress($payer['payer_info']['shipping_address'], $payer['payer_info']['email']);

            /** @var AccountService $accountService */
            $accountService = pluginApp(\Plenty\Modules\Frontend\Services\AccountService::class);

            $contactId = $accountService->getAccountContactId();

            // if the user is logged in, update the contact address
            if(!empty($contactId) && $contactId > 0)
            {
                /** @var ContactAddressRepositoryContract $contactAddress */
                $contactAddress = pluginApp(\Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract::class);

                $createdAddress = $contactAddress->createAddress($address->toArray(), $contactId, AddressRelationType::DELIVERY_ADDRESS);
            }
            // if the user is a guest, create a address and set the invoice address ID
            else
            {
                $createdAddress = $this->addressContract->createAddress($address->toArray());

                if(empty($this->checkout->getCustomerInvoiceAddressId()))
                {
                    // set the customer invoice address ID
                    $this->checkout->setCustomerInvoiceAddressId($createdAddress->id);
                }
            }

            // update/set the customer shipping address ID
            $this->checkout->setCustomerShippingAddressId($createdAddress->id);
        }
    }

    /**
     * @param $ppShippingAddress
     * @param $email
     * @return Address
     */
    private function mapPPAddressToAddress($ppShippingAddress, $email)
    {
        /** @var Address $address */
        $address = pluginApp(\Plenty\Modules\Account\Address\Models\Address::class);

        $name = explode(' ', $ppShippingAddress['recipient_name']);
        $street = explode(' ', $ppShippingAddress['line1']);

        /** @var CountryRepositoryContract $countryContract */
        $countryContract = pluginApp(\Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract::class);

        /** @var Country $country */
        $country = $countryContract->getCountryByIso($ppShippingAddress['country_code'], 'isoCode2');

        $address->name2 = $name[0];
        $address->name3 = $name[1];
        $address->address1 = $street[0];
        $address->address2 = $street[1];
        $address->town = $ppShippingAddress['city'];
        $address->postalCode = $ppShippingAddress['postal_code'];
        $address->countryId = $country->id;

        $addressOptions = [];

        /** @var AddressOption $addressOption */
        $addressOption = pluginApp(\Plenty\Modules\Account\Address\Models\AddressOption::class);

        $addressOption->typeId = AddressOption::TYPE_EMAIL;
        $addressOption->value = $email;

        $addressOptions[] = $addressOption->toArray();

        $address->options = $addressOptions;

        return $address;
    }
}
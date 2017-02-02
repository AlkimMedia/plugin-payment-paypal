<?php //strict

namespace PayPal\Services;

use Plenty\Modules\Account\Address\Contracts\AddressRepositoryContract;
use Plenty\Modules\Account\Address\Models\Address;
use Plenty\Modules\Account\Address\Models\AddressOption;
use Plenty\Modules\Account\Address\Models\AddressRelationType;
use Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract;
use Plenty\Modules\Account\Contact\Contracts\ContactRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Models\Basket;
use Plenty\Modules\Frontend\Contracts\Checkout;
use Plenty\Modules\Order\Shipping\Countries\Contracts\CountryRepositoryContract;
use Plenty\Modules\Order\Shipping\Countries\Models\Country;

/**
 * Class ContactService
 * @package PayPal\Services
 */
class ContactService
{
    /**
     * @var BasketRepositoryContract
     */
    private $basketContract;

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
    public function __construct(BasketRepositoryContract $basketRepositoryContract,
                                AddressRepositoryContract $addressRepositoryContract,
                                Checkout $checkout)
    {
        $this->basketContract = $basketRepositoryContract;
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
            /** @var Basket $basket */
            $basket = $this->basketContract->load();

            /**
             * Map the PayPal address to a plenty address
             * @var Address $address
             */
            $address = $this->mapPPAddressToAddress($payer['payer_info']['shipping_address'], $payer['payer_info']['email']);

            $customerID = $basket->customerId;

            // if the user is logged in, update the contact address
            if(!empty($customerID) && $customerID > 0)
            {
                /** @var ContactAddressRepositoryContract $contactAddress */
                $contactAddress = pluginApp(\Plenty\Modules\Account\Contact\Contracts\ContactAddressRepositoryContract::class);

                $createdAddress = $contactAddress->createAddress($address->toArray(), $customerID, AddressRelationType::DELIVERY_ADDRESS);
            }
            // if the user is a guest, create a address and set the invoice address ID
            else
            {
                $createdAddress = $this->addressContract->createAddress($address->toArray());

                // set the customer invoice address ID
                $this->checkout->setCustomerInvoiceAddressId($createdAddress->id);
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

        $address->setAttribute('name2', $name[0]);
        $address->setAttribute('name3', $name[1]);
        $address->setAttribute('address1', $street[0]);
        $address->setAttribute('address2', $street[1]);
        $address->setAttribute('town', $ppShippingAddress['city']);
        $address->setAttribute('postalCode', $ppShippingAddress['postal_code']);
        $address->setAttribute('countryId', $country->id);

        $addressOptions = [];

        /** @var AddressOption $addressOption */
        $addressOption = pluginApp(\Plenty\Modules\Account\Address\Models\AddressOption::class);

        $addressOption->setAttribute('typeId', AddressOption::TYPE_EMAIL);
        $addressOption->setAttribute('value', $email);

        $addressOptions[] = $addressOption->toArray();

        $address->options = $addressOptions;

        return $address;
    }
}

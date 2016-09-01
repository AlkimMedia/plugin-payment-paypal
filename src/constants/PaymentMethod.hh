<?hh //strict

namespace PayPal\Constants;

enum PaymentMethod:string
{
    CREDIT_CARD        = 'credit_card';
    BANK               = 'bank';
    PAYPAL             = 'paypal';
    PAY_UPON_INVOICE   = 'pay_upon_invoice';
    CARRIER            = 'carrier';
    ALTERNATE_PAYMENT  = 'alternate_payment';
}

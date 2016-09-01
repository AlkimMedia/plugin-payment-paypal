<?hh //strict

namespace PayPal\Constants;

enum PaymentMode:string
{
    ECHECK               = 'ECHECK';
    DELAYED_TRANSFER     = 'DELAYED_TRANSFER';
    MANUAL_BANK_TRANSFER = 'ECHECK';
    INSTANT_TRANSFER     = 'INSTANT_TRANSFER';
}

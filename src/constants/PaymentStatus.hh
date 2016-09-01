<?hh //strict

namespace PayPal\Constants;

enum PaymentStatus:string
{
    CREATED                = 'created';
    APPROVED               = 'approved';
    FAILED                 = 'failed';
    PARTIALLY_COMPLETED    = 'partially_completed';
    COMPLETED              = 'completed';
    IN_PROGRESS            = 'in_progress';
    PENDING                = 'pending';
    REFUNDED               = 'refunded';
    DENIED                 = 'denied';
}

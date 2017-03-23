<?php //strict

namespace PayPal\Constants;

/**
 * Class NotificationEvents
 * @package PayPal\Constants
 */
class NotificationEvents
{
    const BILLING_PLAN_CREATED = 'BILLING.PLAN.CREATED';                            // A billing plan is created.
    const BILLING_PLAN_UPDATED = 'BILLING.PLAN.UPDATED';                            // A billing plan is updated.
    const BILLING_SUBSCRIPTION_CANCELLED = 'BILLING.SUBSCRIPTION.CANCELLED';        // A billing subscription is canceled.
    const BILLING_SUBSCRIPTION_CREATED = 'BILLING.SUBSCRIPTION.CREATED';            // A billing subscription is created.
    const BILLING_SUBSCRIPTION_REACTIVATED = 'BILLING.SUBSCRIPTION.RE-ACTIVATED';   // A billing subscription is re-activated.
    const BILLING_SUBSCRIPTION_SUSPENDED = 'BILLING.SUBSCRIPTION.SUSPENDED';        // A billing subscription is suspended.
    const BILLING_SUBSCRIPTION_UPDATED = 'BILLING.SUBSCRIPTION.UPDATED';            // A billing subscription is updated.

    const CUSTOMER_DISPUTE_CREATED = 'CUSTOMER.DISPUTE.CREATED';	// A customer dispute is created.
    const CUSTOMER_DISPUTE_RESOLVED = 'CUSTOMER.DISPUTE.RESOLVED';	// A customer dispute is resolved.
    const RISK_DISPUTE_CREATED = 'RISK.DISPUTE.CREATED';	        // A risk dispute is created. The CUSTOMER.DISPUTE.CREATED event type supersedes and deprecates the RISK.DISPUTE.CREATED event type.

    const IDENTITY_AUTHORIZATIONCONSENT_REVOKED = 'IDENTITY.AUTHORIZATION-CONSENT.REVOKED'; // A user's consent token is revoked.

    const INVOICING_INVOICE_CANCELLED = 'INVOICING.INVOICE.CANCELLED';	// An invoice is canceled.
    const INVOICING_INVOICE_PAID = 'INVOICING.INVOICE.PAID';	        // An invoice is paid.
    const INVOICING_INVOICE_REFUNDED = 'INVOICING.INVOICE.REFUNDED';	// An invoice is refunded.

    const PAYMENT_AUTHORIZATION_CREATED = 'PAYMENT.AUTHORIZATION.CREATED';	// A payment authorization is created, approved, executed, or a future payment authorization is created.
    const PAYMENT_AUTHORIZATION_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED';	// A payment authorization is voided.

    const PAYMENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';	// A payment capture is completed.
    const PAYMENT_CAPTURE_DENIED = 'PAYMENT.CAPTURE.DENIED';	    // A payment capture is denied.
    const PAYMENT_CAPTURE_PENDING = 'PAYMENT.CAPTURE.PENDING';	    // The state of a payment capture changes to pending.
    const PAYMENT_CAPTURE_REFUNDED = 'PAYMENT.CAPTURE.REFUNDED';	// Merchant refunds a payment capture.
    const PAYMENT_CAPTURE_REVERSED = 'PAYMENT.CAPTURE.REVERSED';	// PayPal reverses a payment capture.

    const PAYMENT_PAYOUTSBATCH_DENIED = 'PAYMENT.PAYOUTSBATCH.DENIED';	        // A batch payout payment is denied.
    const PAYMENT_PAYOUTSBATCH_PROCESSING = 'PAYMENT.PAYOUTSBATCH.PROCESSING';	// The state of a batch payout payment changes to processing.
    const PAYMENT_PAYOUTSBATCH_SUCCESS = 'PAYMENT.PAYOUTSBATCH.SUCCESS';	    // A batch payout payment successfully completes processing.
    const PAYMENT_PAYOUTSITEM_BLOCKED = 'PAYMENT.PAYOUTS-ITEM.BLOCKED';	        // A payouts item was blocked.
    const PAYMENT_PAYOUTSITEM_CANCELED = 'PAYMENT.PAYOUTS-ITEM.CANCELED';	    // A payouts item was cancelled.
    const PAYMENT_PAYOUTSITEM_DENIED = 'PAYMENT.PAYOUTS-ITEM.DENIED';	        // A payouts item was denied.
    const PAYMENT_PAYOUTSITEM_FAILED = 'PAYMENT.PAYOUTS-ITEM.FAILED';	        // A payouts item has failed.
    const PAYMENT_PAYOUTSITEM_HELD = 'PAYMENT.PAYOUTS-ITEM.HELD';	            // A payouts item is held.
    const PAYMENT_PAYOUTSITEM_REFUNDED = 'PAYMENT.PAYOUTS-ITEM.REFUNDED';	    // A payouts item was refunded.
    const PAYMENT_PAYOUTSITEM_RETURNED = 'PAYMENT.PAYOUTS-ITEM.RETURNED';	    // A payouts item is returned.
    const PAYMENT_PAYOUTSITEM_SUCCEEDED = 'PAYMENT.PAYOUTS-ITEM.SUCCEEDED';	    // A payouts item has succeeded.
    const PAYMENT_PAYOUTSITEM_UNCLAIMED = 'PAYMENT.PAYOUTS-ITEM.UNCLAIMED';	    // A payouts item is unclaimed.

    const PAYMENT_SALE_COMPLETED = 'PAYMENT.SALE.COMPLETED';	// A sale is completed.
    const PAYMENT_SALE_DENIED = 'PAYMENT.SALE.DENIED';	        // The state of a sale changes from pending to denied.
    const PAYMENT_SALE_PENDING = 'PAYMENT.SALE.PENDING';	    // The state of a sale changes to pending.
    const PAYMENT_SALE_REFUNDED = 'PAYMENT.SALE.REFUNDED';	    // Merchant refunds the sale.
    const PAYMENT_SALE_REVERSED = 'PAYMENT.SALE.REVERSED';	    // PayPal reverses a sale.

    const VAULT_CREDITCARD_CREATED = 'VAULT.CREDIT-CARD.CREATED';	// A credit card was created.
    const VAULT_CREDITCARD_DELETED = 'VAULT.CREDIT-CARD.DELETED';	// A credit card was deleted.
    const VAULT_CREDITCARD_UPDATED = 'VAULT.CREDIT-CARD.UPDATED';	// A credit card was updated.
}

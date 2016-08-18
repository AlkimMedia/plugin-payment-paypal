<?hh //strict

namespace PayPal\Services;

use Plenty\Modules\Payment\Contracts\PaymentRepositoryContract;
use Plenty\Modules\Payment\Models\Payment;

class PaymentService
{
    private PaymentRepositoryContract $paymentRepository;

    public function __construct(PaymentRepositoryContract $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function createPayment(array<string, mixed> $payment):void
    {
        $this->paymentRepository->createPayment($payment);
    }

    public function updatePayment(array<string, mixed> $payment):void
    {
        $this->paymentRepository->updatePayment($payment);
    }
}

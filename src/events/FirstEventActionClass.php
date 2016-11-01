<?php
use PayPal\Services\PaymentService;
use Plenty\Modules\Order\Models\Order;

/**
 * FirstEventActionClass.php
 *
 * @author  emmanouil.stafilarakis
 *
 * @package modules
 * @subpackage
 *
 * @since   plentymarkets version 7.00
 */

class FirstEventActionClass
{
	/**
	 * 
	 *
	 * @param Order          $order
	 * @param PaymentService $paymentService
	 */
	public function run(Order $order, PaymentService $paymentService)
	{
		//TODO
	}
}
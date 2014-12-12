<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

class PSPHipayValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		if ($this->module->active == false)
			die;

		if (Tools::getValue('xml'))
		{
			$xml = Tools::getValue('xml');
			$order = Tools::jsonDecode(Tools::jsonEncode((array)simplexml_load_string($xml)), 1);

			$cart_id = (int)$order['result']['merchantDatas']['_aKey_cart_id'];
			$customer_id = (int)$order['result']['merchantDatas']['_aKey_customer_id'];
			$secure_key = $order['result']['merchantDatas']['_aKey_secure_key'];

			$amount = (float)$order['result']['origAmount'];

			$this->context->cart = new Cart((int)$cart_id);
			$this->context->customer = new Customer((int)$customer_id);
			$this->context->currency = new Currency((int)$this->context->cart->id_currency);
			$this->context->language = new Language((int)$this->context->customer->id_lang);

			return $this->registerOrder($order, $cart_id, $amount, $secure_key);
		}

		return $this->displayError('An error occurred while processing payment');
	}

	protected function displayError($message)
	{
		$this->context->smarty->assign('path',
			'<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
			<span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

		$this->errors[] = $this->module->l($message);

		return $this->setTemplate('error.tpl');
	}

	protected function registerOrder($order, $cart_id, $amount, $secure_key)
	{
		if ($this->isValidOrder($order) === true)
		{
			$currency = $this->context->currency;

			switch ($status)
			{
				case 'ok':
					$id_order_state = (int)Configuration::get('PS_OS_PAYMENT');
					break;
				case 'nok':
					$id_order_state = (int)Configuration::get('PS_OS_ERROR');
					break;
				case 'cancel':
					$id_order_state = (int)Configuration::get('PS_OS_CANCELED');
					break;
				case 'waiting':
					$id_order_state = (int)Configuration::get('PSP_HIPAY_OS_WAITING');
					break;
				default:
					$id_order_state = (int)Configuration::get('PS_OS_ERROR');
					break;
			}

			return $this->placeOrder($order, $id_order_state, $cart_id, $currency, $amount, $secure_key);
		}
	}

	protected function isValidOrder($order)
	{
		if (isset($order['result']) == false)
			return false;
		elseif ((isset($order['result']['status']) == false) || (isset($order['result']['merchantDatas']) == false))
			return false;

		$sandbox_mode = (bool)Configuration::get('PSP_HIPAY_SANDBOX_MODE');

		if ($sandbox_mode)
			$ws_login = (int)Configuration::get('PSP_HIPAY_SANDBOX_WS_LOGIN');
		else
			$ws_login = (int)Configuration::get('PSP_HIPAY_WS_LOGIN');

		$valid_secure_key = ($this->context->customer->secure_key == $order['result']['merchantDatas']['_aKey_secure_key']);
		$valid_token = (Tools::encrypt($ws_login.$order['result']['merchantDatas']['_aKey_cart_id']) == $order['result']['merchantDatas']['_aKey_token']);

		return $valid_secure_key && $valid_token;
	}

	protected function placeOrder($order, $id_order_state, $cart_id, $currency, $amount, $secure_key)
	{
		$order_id = (int)Order::getOrderByCartId($cart_id);

		if ((bool)$order_id != false)
		{
			$order = new Order($order_id);

			if ((int)$order->getCurrentState() == (int)Configuration::get('PSP_HIPAY_OS_WAITING'))
			{
				$order_history = new OrderHistory();
				$order_history->id_order = $order_id;
				$order_history->changeIdOrderState($id_order_state, $order_id);

				return $order_history->addWithemail();
			}

			return $this->displayError('An error occurred while saving transaction details');
		}
		else
		{
			if ($id_order_state != (int)Configuration::get('PS_OS_ERROR'))
			{
				$payment_method = $order['result']['paymentMethod'];
				$transaction_id = $order['result']['transid'];
				$message = Tools::safeOutput("Payment method: $payment_method<br />");
				$message .= Tools::safeOutput("Transaction ID: $payment_method");
			}
			else
			{
				$error_code = $order['result']['returnCode'];
				$error_desc = $order['result']['returnDescriptionShort'];
				$message = Tools::safeOutput("Error: [$error_code] $error_desc");
			}

			return $this->module->validateOrder($cart_id, $id_order_state, $amount, $this->module->displayName, $message, array(), (int)$currency->id, false, $secure_key);
		}
	}
}

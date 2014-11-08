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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
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

			Context::getContext()->cart = new Cart((int)$cart_id);
			Context::getContext()->customer = new Customer((int)$customer_id);
			Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
			Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);

			return $this->registerOrder($order, $cart_id, $amount, $secure_key);
		}

		return $this->displayError('An error occurred while processing payment');
	}

	protected function displayError($message)
	{
		$this->context->smarty->assign('path', '
			<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
			<span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

		$this->errors[] = $this->module->l($message);

		return $this->setTemplate('error.tpl');
	}

	protected function registerOrder($order, $cart_id, $amount, $secure_key)
	{
		if ($this->isValidOrder($order) === true)
		{
			$operation = trim(strtolower($order['result']['operation']));
			$status = trim(strtolower($order['result']['status']));
			$currency = Context::getContext()->currency;

			switch ($status)
			{
				case 'ok':
					$id_order_state = (int)Configuration::get('PS_OS_PAYMENT');
					break;
				case 'waiting':
					$id_order_state = (int)Configuration::get('PSP_HIPAY_OS_WAITING');
					break;
				default:
					$id_order_state = (int)Configuration::get('PS_OS_ERROR');
					break;
			}

			return $this->{$operation}($order, $id_order_state, $cart_id, $currency, $amount, $secure_key);
		}
		else
		{
			$id_order_state = Configuration::get('PS_OS_ERROR');

			$error_code = $order['result']['returnCode'];
			$error_desc = $order['result']['returnDescriptionShort'];
			$message = Tools::safeOutput("Error: [$error_code] $error_desc");

			return $this->module->validateOrder($cart_id, $id_order_state, $amount, $this->module->displayName, $message, array(), (int)$currency->id, false, $secure_key);
		}
	}

	protected function isValidOrder($order)
	{
		if (isset($order['result']) == false)
			return false;
		elseif ((isset($order['result']['status']) == false) || (isset($order['result']['merchantDatas']) == false))
			return false;

		$valid_secure_key = (Context::getContext()->customer->secure_key == $order['result']['merchantDatas']['_aKey_secure_key']);
		$valid_token = (Tools::encrypt($order['result']['merchantDatas']['_aKey_cart_id']) == $order['result']['merchantDatas']['_aKey_token']);

		return $valid_secure_key && $valid_token;
	}

	protected function authorization($order, $id_order_state, $cart_id, $currency, $amount, $secure_key)
	{
		if ($id_order_state != (int)Configuration::get('PS_OS_ERROR'))
		{
			$payment_method = $order['result']['paymentMethod'];
			$message = Tools::safeOutput("Payment method: $payment_method");
		}
		else
		{
			$error_code = $order['result']['returnCode'];
			$error_desc = $order['result']['returnDescriptionShort'];
			$message = Tools::safeOutput("Error: [$error_code] $error_desc");
		}

		return $this->module->validateOrder($cart_id, $id_order_state, $amount, $this->module->displayName, $message, array(), (int)$currency->id, false, $secure_key);
	}

	protected function capture($order, $id_order_state, $cart_id, $currency, $amount, $secure_key)
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
		}
		else
		{
			if ($id_order_state != (int)Configuration::get('PS_OS_ERROR'))
			{
				$payment_method = $order['result']['paymentMethod'];
				$message = Tools::safeOutput("Payment method: $payment_method");
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

	protected function refund($order, $id_order_state, $cart_id, $currency, $amount, $secure_key)
	{
		$order_id = (int)Order::getOrderByCartId($cart_id);

		/* Payment refunded on Hipay */
		if ((bool)$order == false)
			return $this->displayError('An error occurred while processing refund');

		$order = new Order($order_id);

		if (($order->valid == false) || ($order->getCurrentState() === Configuration::get('PS_OS_REFUND')))
			return $this->displayError('An error occurred while processing refund');

		$order_history = new OrderHistory();
		$order_history->id_order = $order->id;
		$order_history->changeIdOrderState((int)(Configuration::get('PS_OS_REFUND')), $order_id);

		return $order_history->addWithemail();
	}
}

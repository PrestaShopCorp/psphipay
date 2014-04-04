<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class PSPHipayValidationModuleFrontController extends ModuleFrontController
{

	public function postProcess()
	{
		if (Tools::isSubmit('process'))
		{
			switch (Tools::getValue('process'))
			{
				case 'accept':
					return $this->accept();
				case 'callback':
					return $this->callback();
				case 'decline':
					return $this->decline();
				default:
					return false;
			}
		}
	}
	
	protected function accept()
	{
		$this->context->smarty->assign('path', $this->module->l('Order confirmation'));
		
		return $this->setTemplate('confirmation.tpl');
	}
	
	protected function callback()
	{
		if (Tools::getValue('xml'))
		{
			$xml = Tools::getValue('xml');
			$order = json_decode(json_encode((array)simplexml_load_string($xml)), 1);
			
			if ($this->isValidOrder($order) === true)
			{
				d('Order should be validated!');
			}
		}
	}
	
	protected function isValidOrder($order)
	{
		if ((isset($order['result']) === false) || (isset($order['result']['merchantDatas']) === false))
			return false;
		
		$cart_id = $order['result']['merchantDatas']['_aKey_cart_id'];
		$customer_id = $order['result']['merchantDatas']['_aKey_customer_id'];
		$secure_key = $order['result']['merchantDatas']['_aKey_secure_key'];
		
		Context::getContext()->cart = new Cart((int)$cart_id);
		Context::getContext()->customer = new Customer((int)$customer_id);
		Context::getContext()->currency = new Currency((int)Context::getContext()->cart->id_currency);
		Context::getContext()->language = new Language((int)Context::getContext()->customer->id_lang);
		
		if (Context::getContext()->customer->secure_key != $secure_key)
			return false;
		
		return true;
	}
	
	protected function decline()
	{
		return $this->displayError('An error occurred while processing payment.');
	}

	protected function displayError($message)
	{
		$this->context->smarty->assign('path', '
			<a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
			<span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));
		
		$this->errors[] = $this->module->l($message);
		return $this->setTemplate('error.tpl');
	}
}

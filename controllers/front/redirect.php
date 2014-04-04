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

class PSPHipayRedirectModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		$cart = $this->context->cart;
		$currency = $this->context->currency;
		$language = $this->context->language;
		$customer = $this->context->customer;

		if ($this->module->isSupportedCurrency($currency->iso_code) == false)
			return $this->displayError('The currency is not supported');

		$this->generatePayment();
	}

	protected function generatePayment()
	{
		require_once(dirname(__FILE__).'/../../classes/webservice/HipayPayment.php');

		$payment = new HipayPayment();
		
		if ($payment->generate() == false)
			$this->displayError('An error occurred while getting transaction informations');
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

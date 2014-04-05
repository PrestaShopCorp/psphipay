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

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayPayment extends HipayWS
{

	protected $categories_test_domain = 'https://test-payment.hipay.com/';
	protected $categories_domain = 'https://payment.hipay.com/';
	protected $categories_url = 'order/list-categories/id/';

	protected $client_url = '/soap/payment-v2';

	/* SOAP method: codes */
	public function generate(&$results)
	{
		if (Configuration::get('PSP_HIPAY_USER_EMAIL') == false)
			die(Tools::displayError('An error occurred while redirecting to the payment processor.'));
		
		$currency_id = Context::getContext()->cart->id_currency;
		$currency = new Currency($currency_id);
		$user = new HipayUserAccount();
		$wesbite_id = $user->getWebsiteIdByIsoCode($currency->iso_code);
		
		if ($wesbite_id == false)
			die(Tools::displayError('An error occurred while redirecting to the payment processor.'));
			
		$locale = new HipayLocale();
		$free_data = $this->getFreeData();

		$cart_id = Context::getContext()->cart->id;
		$secure_key = Context::getContext()->customer->secure_key;

		$accept_url = Context::getContext()->link->getModuleLink('psphipay', 'confirmation', array('cart_id' => $cart_id, 'secure_key' => $secure_key), true);
		$callback_url = Context::getContext()->link->getModuleLink('psphipay', 'validation', array(), true);
		$cancel_url = Context::getContext()->link->getPageLink('order', null, null, array('step' => '3'), true);
		$decline_url = Context::getContext()->link->getModuleLink('psphipay', 'confirmation', array('cart_id' => $cart_id, 'secure_key' => $secure_key), true);
		$logo_url = Context::getContext()->link->getMediaLink(_PS_IMG_.Configuration::get('PS_LOGO'));

		/* 
		 * @TODO
		 * Set the merchant details according to the currency
		 */
		$params = array(
			'websiteId' => (int)$wesbite_id,
			'amount' => Context::getContext()->cart->getOrderTotal(),
			'categoryId' => $this->getCategory(),
			'currency' => Context::getContext()->currency->iso_code,
			'customerEmail' => Context::getContext()->customer->email,
			'customerIpAddress' => Tools::getRemoteAddr(),
			'description' => Configuration::get('PS_SHOP_NAME'),
			'emailCallback' => Configuration::get('PSP_HIPAY_USER_EMAIL'),
			'executionDate' => date('Y-m-d\TH:i:s'),
			'locale' => $locale->getLocale(),
			'manualCapture' => (int)false,
			'rating' => 'ALL',
			'wsSubAccountLogin' => Configuration::get('PSP_HIPAY_USER_EMAIL'),

			// URLs
			'urlAccept' => $accept_url,
			'urlCallback' => $callback_url,
			'urlCancel' => $cancel_url,
			'urlDecline' => $decline_url,
			'urlLogo' => $logo_url,

			'freeData' => $free_data,
		);

		$results = $this->doQuery('generate', $params);

		if ($results->generateResult->code === 0)
			return Tools::redirect($results->generateResult->redirectUrl);
		return false;
	}

	protected function getFreeData()
	{
		return array(
			'item' => array(
				array('key' => 'cart_id', 'value' => Context::getContext()->cart->id),
				array('key' => 'customer_id', 'value' => Context::getContext()->customer->id),
				array('key' => 'secure_key', 'value' => Context::getContext()->customer->secure_key),
			),
		);
		
		return $this->buildXMLData($data, 'freeData');
	}

	protected function getCategory()
	{
		$live_mode = (bool)Configuration::get('PSP_HIPAY_LIVE_MODE');
		$website_id = (int)Configuration::get('PSP_HIPAY_WEBSITE_ID');

		if ($live_mode === true)
			$url = $this->categories_domain.$this->categories_url.$website_id;
		else
			$url = $this->categories_test_domain.$this->categories_url.$website_id;

		$categories_xml = Tools::file_get_contents($url);
		$categories = json_decode(json_encode((array)simplexml_load_string($categories_xml)), 1);
		
		if (isset($categories['result']['status']) && ($categories['result']['status'] == 'error'))
			die(Tools::displayError('Error occurred while getting categories list.'));

		if (isset($categories['categoriesList']['category']))
			return array_keys($categories['categoriesList']['category'])[0];
		return 0;
	}

	public function buildXMLData($data, $startElement = 'freeData')
	{
		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startElement($startElement);

		$this->writeXML($xml, $data);
		$xml->endElement();

		return $xml->outputMemory(true); 
	}

	public function writeXML(XMLWriter &$xml, &$data)
	{
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $sub_value)
				{
					$xml->startElement($key);
					$this->writeXML($xml, $sub_value);
					$xml->endElement();
				}
				continue;
			}
			$xml->writeElement($key, $value);
		}
	}

}

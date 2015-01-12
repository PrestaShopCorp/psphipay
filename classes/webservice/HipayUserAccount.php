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

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayUserAccount extends HipayWS
{
	protected $accounts_currencies = array();

	protected $client_url = '/soap/user-account-v2';

	protected $module = false;
	
	protected static $email_available = null;

	public function __construct($module_instance)
	{
		parent::__construct($module_instance);

		$this->accounts_currencies = array(
			'CHF' => $this->module->l('Swiss Franc'),
			'EUR' => $this->module->l('Euro'),
			'GBP' => $this->module->l('British Pound'),
			'SEK' => $this->module->l('Swedish Krona'),
		);
	}

	public function createAccount($email, $first_name, $last_name, $sandbox_mode = false)
	{
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency_code = Tools::strtoupper($currency->iso_code);
		
		if (in_array($currency_code, $this->module->limited_currencies) == false)
			$currency_code = Tools::strtoupper($this->module->default_currency);

		$country = new Country(Configuration::get('PS_COUNTRY_DEFAULT'));
		$country_code = Tools::strtolower($country->iso_code);

		$language = new Language(Configuration::get('PS_LANG_DEFAULT'));
		$language_code = Tools::strtoupper($language->iso_code);

		$data = array(
			'email' => $email,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'currency_code' => $currency_code,
			'iso_country' => $country_code,
			'iso_lang' => $language_code,
			'remote_addr' => Tools::getRemoteAddr(),
			'sandbox_mode' => (int)$sandbox_mode,
			'shop_email' => Configuration::get('PS_SHOP_EMAIL'),
			'shop_name' => Configuration::get('PS_SHOP_NAME'),
			'shop_domain' => Tools::getShopDomainSsl(true, true),
		);

		$result = $this->prestaShopWebservice('/account/create', $data);

		if ($result->code === 0)
		{
			Configuration::updateValue('PSP_HIPAY_USER_EMAIL', $email);

			if ($sandbox_mode == false)
			{
				Configuration::updateValue('PSP_HIPAY_USER_ACCOUNT_ID', $result->userAccountId);
				Configuration::updateValue('PSP_HIPAY_WEBSITE_ID', $result->websiteId);
				Configuration::updateValue('PSP_HIPAY_WS_LOGIN', $result->wsLogin);
				Configuration::updateValue('PSP_HIPAY_WS_PASSWORD', $result->wsPassword);
			}
			else
			{
				Configuration::updateValue('PSP_HIPAY_SANDBOX_USER_ACCOUNT_ID', $result->userAccountId);
				Configuration::updateValue('PSP_HIPAY_SANDBOX_WEBSITE_ID', $result->websiteId);
				Configuration::updateValue('PSP_HIPAY_SANDBOX_WS_LOGIN', $result->wsLogin);
				Configuration::updateValue('PSP_HIPAY_SANDBOX_WS_PASSWORD', $result->wsPassword);
			}

			return true;
		}

		return false;
	}

	public function isEmailAvailable($email, $sandbox_mode = false)
	{
		if (!is_bool(static::$email_available))
		{		
			$result = $this->prestaShopWebservice('/account/available', array(
				'email' => $email,
				'sandbox_mode' => (int)$sandbox_mode,
			));
			
			if (isset($result->isAvailable))
				static::$email_available = ! ($result->isAvailable === false);
			else
				return false;
		}
		
		return static::$email_available;
	}

	public function getAccountInfos()
	{
		$email = Configuration::get('PSP_HIPAY_USER_EMAIL');

		$params = array('accountLogin' => $email);
		$result = $this->executeQuery('getAccountInfos', $params);

		if ($result->getAccountInfosResult->code === 0)
			return $result->getAccountInfosResult;

		return false;
	}

	public function getBalances()
	{
		$email = Configuration::get('PSP_HIPAY_USER_EMAIL');
		$params = array('wsSubAccountLogin' => $email);
		$result = $this->executeQuery('getBalance', $params);
		
		if ($result->getBalanceResult->code === 0)
			return $result->getBalanceResult;

		return false;
	}

	public function getMainAccountBalance($balances)
	{
		foreach ($balances->balances->item as $balance)
		{
			if (isset($balance->userAccountType) == false)
				return false;
			elseif ($balance->userAccountType == 'main')
				return $balance;
		}

		return false;
	}

	public function getTransactions()
	{
		$psp_hipay_date_from = (isset($this->context->cookie->psp_hipay_date_from) ? $this->context->cookie->psp_hipay_date_from : date('Y-m-dT')).'00:00:00';
		$psp_hipay_date_to = (isset($this->context->cookie->psp_hipay_date_to) ? $this->context->cookie->psp_hipay_date_to : date('Y-m-dT')).'23:59:59';

		$params = array(
			'wsSubAccountLogin' => Configuration::get('PSP_HIPAY_USER_EMAIL'),
			'startDate' => date('Y-m-dTH:i:s', strtotime($psp_hipay_date_from)),
			'endDate' => date('Y-m-dTH:i:s', strtotime($psp_hipay_date_to)),
			'pageNumber' => 1,
		);

		$results = $this->executeQuery('getTransactions', $params);

		if (($results->getTransactionsResult->code === 0) && (isset($results->getTransactionsResult->transactions->item) == true))
		{
			if (is_array($results->getTransactionsResult->transactions->item) == true)
				return (array)$results->getTransactionsResult->transactions->item;
			else
				return array($results->getTransactionsResult->transactions->item);
		}
	}

	public function getWebsiteAccountIdByIsoCode($iso_code)
	{
		$account = $this->getAccountInfos();

		if ($iso_code == $account->currency)
			return $account->userAccountId;

		foreach ($account->subAccounts->item as $sub_account)
			if ($iso_code == $sub_account->currency)
				return $sub_account->userAccountId;

		return false;
	}

	public function getWebsiteIdByIsoCode($iso_code)
	{
		$account = $this->getAccountInfos();

		if ($iso_code == $account->currency)
			return $account->websites->item->websiteId;

		foreach ($account->subAccounts->item as $sub_account)
			if ($iso_code == $sub_account->currency)
				return $sub_account->websites->item->websiteId;

		return false;
	}

}

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

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayUserAccount extends HipayWS
{

	protected $client_url = '/soap/user-account-v2';
	protected static $psp = false;

	/* Merchant email */
	protected static $email = false;

	/* Availability object */
	protected static $availability = false;
	protected static $account_infos = false;

	/* Sub accounts */
	public static $accounts_currencies = array();

	public function __construct($email = false)
	{
		parent::__construct();

		if ($email == false)
			self::$email = Configuration::get('PSP_HIPAY_USER_EMAIL');
		else
			self::$email = $email;

		self::$psp = new PSPHipay();

		self::$accounts_currencies = array(
			'CHF' => self::$psp->l('Swiss Franc'),
			'EUR' => self::$psp->l('Euro'),
			'GBP' => self::$psp->l('British Pound'),
			'SEK' => self::$psp->l('Swedish Krona'),
		);
	}

	/* SOAP method: isAvailable */
	public function isEmailAvailable()
	{
		$params = array(
			'email' => self::$email,
			'entity' => $this->ws_entity,
		);

		$result = $this->doQuery('isAvailable', $params);

		if ($result->isAvailableResult->code === 0)
		{
			self::$availability = $result->isAvailableResult;

			if ((bool)$result->isAvailableResult->isAvailable === true)
				return true;
		}

		return false;
	}

	public function getAvailability()
	{
		return self::$availability;
	}

	public function isValidAccount()
	{
		return (bool)$this->getAccountInfos();
	}

	public function getAccountInfos($refresh = false)
	{
		if ((self::$account_infos === false) || ($refresh !== false))
		{
			$params = array('accountLogin' => self::$email);
			$result = $this->doQuery('getAccountInfos', $params);

			if ($result->getAccountInfosResult->code === 0)
				self::$account_infos = $result->getAccountInfosResult;
		}

		return self::$account_infos;
	}

	public function getWebsiteIdByIsoCode($iso_code)
	{
		$this->getAccountInfos();

		if ($iso_code == self::$account_infos->currency)
			return self::$account_infos->websites->item->websiteId;

		foreach (self::$account_infos->subAccounts->item as $sub_account)
			if ($iso_code == $sub_account->currency)
				return $sub_account->websites->item->websiteId;

		return false;
	}

	public function getWebsiteEmailByIsoCode($iso_code)
	{
		$this->getAccountInfos();

		if ($iso_code == self::$account_infos->currency)
			return self::$account_infos->websites->item->websiteEmail;

		foreach (self::$account_infos->subAccounts->item as $sub_account)
			if ($iso_code == $sub_account->currency)
				return $sub_account->websites->item->websiteEmail;

		return false;
	}

	public function getWebsiteAccountIdByIsoCode($iso_code)
	{
		$this->getAccountInfos();

		if ($iso_code == self::$account_infos->currency)
			return self::$account_infos->userAccountId;

		foreach (self::$account_infos->subAccounts->item as $sub_account)
			if ($iso_code == $sub_account->currency)
				return $sub_account->userAccountId;

		return false;
	}

	public function getBalances($email = false)
	{
		if (self::$account_infos == false)
			$this->getAccountInfos();

		if ($email == false)
			$email = self::$email;

		$params = array('wsSubAccountLogin' => $email);
		$result = $this->doQuery('getBalance', $params);

		if ($result->getBalanceResult->code === 0)
			return $result->getBalanceResult;

		return false;
	}

	public function getMainAccountBalance()
	{
		if (self::$account_infos == false)
			$this->getAccountInfos();

		$balances = $this->getBalances();

		foreach ($balances->balances->item as &$balance)
			if ($balance->userAccountType == 'main')
				return $balance;

		return false;
	}

	public function getSubAccountsBalances()
	{
		$balances = $this->getBalances();
		$sub_accounts = unserialize(Configuration::get('PSP_HIPAY_USER_SUBACCOUNTS'));

		if ($sub_accounts != false)
		{
			foreach ($sub_accounts as &$item)
			{
				$item->currency_label = self::$accounts_currencies[$item->currency];

				foreach ($balances->balances->item as $balance)
					if ($item->userAccountId == $balance->userAccountId)
						$item = (object)array_merge((array)$item, (array)$balance);
			}

			return $sub_accounts;
		}

		return false;
	}

	public function createUserAccount()
	{
		$currency = Currency::getDefaultCurrency();

		$business = new HipayBusiness();
		$locale = new HipayLocale();
		$topic = new HipayTopic();

		$locale_code = $locale->getLocale();
		$user_password = Tools::passwdGen(16);

		Configuration::updateValue('PSP_HIPAY_USER_PASSWORD', $user_password);

		$params = array(
			'websiteId' => $this->getWsId(),
			'email' => self::$email,
			'firstname' => Tools::getValue('install_user_firstname'),
			'lastname' => Tools::getValue('install_user_lastname'),
			'currency' => $currency->iso_code,
			'locale' => $locale_code,
			'ipAddress' => Tools::getRemoteAddr(),
			'websiteBusinessLineId' => $business->getBusinessId(),
			'websiteTopicId' => $topic->getTopicId(),
			'websiteContactEmail'=> Configuration::get('PS_SHOP_EMAIL'),
			'websiteName'=> Tools::getValue('install_user_shop_name'),
			'websiteUrl'=> Tools::getShopDomain(true, true),
			'websiteMerchantPassword' => $user_password,
			'entity' => $this->ws_entity,
		);

		$result = $this->doQuery('createWithWebsite', $params);

		if ($result->createWithWebsiteResult->code === 0)
		{
			self::$account_infos = $result->createWithWebsiteResult;

			$this->saveNewUserAccountInfos();
			$this->associateMerchantGroup();

			return true;
		}

		return false;
	}

	public function associateMerchantGroup()
	{
		$params = array(
			'accountLogin' => self::$email,
			'merchantGroupId' => $this->getWsMerchantGroup(),
			'entity' => $this->ws_entity,
		);

		$result = $this->doQuery('associateMerchantGroup', $params);

		if ($result->associateMerchantGroupResult->code === 0)
			return true;

		return false;
	}

	public function createSubAccounts()
	{
		$iso_codes = array_keys(self::$accounts_currencies);

		foreach ($iso_codes as $iso_code)
		{
			$sub_account_exists = $this->currencyAccountExists($iso_code);

			if ($sub_account_exists === false)
				$this->createSubAccount($iso_code);
		}
	}

	public function createSubAccount($currency)
	{
		$locale = new HipayLocale();

		$params = array(
			'accountLogin' => self::$email,
			'duplicateWebsite' => (int)true,
			'currency' => $currency,
			'locale' => $locale->getLocale()
		);

		$result = $this->doQuery('createSubAccount', $params);

		if ($result->createSubaccountResult->code === 0)
			return true;

		return false;
	}

	public function currencyAccountExists($currency)
	{
		$default_currency = Configuration::get('PSP_HIPAY_CURRENCY');

		if (strcmp($default_currency, $currency) === 0)
			return true;

		if (isset(self::$account_infos->subAccounts->item))
		{
			$sub_accounts = self::$account_infos->subAccounts->item;

			foreach ($sub_accounts as $sub_account)
				if (strcmp($sub_account->currency, $currency) === 0)
					return true;
		}

		return false;
	}

	public function saveNewUserAccountInfos()
	{
		Configuration::updateValue('PSP_HIPAY_SPACE_ID', self::$account_infos->userSpaceId);

		if ($this->getAccountInfos('refresh') !== false)
			return $this->saveUserAccountInfos();

		return false;
	}

	public function saveUserAccountInfos()
	{
		Configuration::updateValue('PSP_HIPAY_USER_ACCOUNT_ID', self::$account_infos->userAccountId);
		Configuration::updateValue('PSP_HIPAY_USER_EMAIL', self::$email);
		Configuration::updateValue('PSP_HIPAY_CURRENCY', self::$account_infos->currency);

		Configuration::updateValue('PSP_HIPAY_WEBSITE_EMAIL', self::$account_infos->websites->item->websiteEmail);
		Configuration::updateValue('PSP_HIPAY_WEBSITE_ID', self::$account_infos->websites->item->websiteId);
		Configuration::updateValue('PSP_HIPAY_WEBSITE_NAME', self::$account_infos->websites->item->websiteName);
		Configuration::updateValue('PSP_HIPAY_WEBSITE_URL', self::$account_infos->websites->item->websiteURL);

		$this->associateMerchantGroup();
		$this->saveUserSubAccountInfos();

		return true;
	}

	public function saveUserSubAccountInfos()
	{
		$this->createSubAccounts();
		$this->getAccountInfos('refresh');

		if (isset(self::$account_infos->subAccounts->item))
		{
			$sub_accounts = serialize(self::$account_infos->subAccounts->item);
			Configuration::updateValue('PSP_HIPAY_USER_SUBACCOUNTS', $sub_accounts);
		}

		return true;
	}

}

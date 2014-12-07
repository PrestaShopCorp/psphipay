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

require_once(dirname(__FILE__).'/classes/forms/PSPHipayForm.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayUserAccount.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayWS.php');

class PSPHipay extends PaymentModule
{
	protected $config_form = false;

	protected $_errors = array();
	protected $_successes = array();
	protected $_warnings = array();

	public function __construct()
	{
		$this->name = 'psphipay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->module_key = '';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->author = 'PrestaShop';

		$this->bootstrap = true;
		$this->display = 'view';

		parent::__construct();

		$this->displayName = $this->l('PrestaShop Payments by HiPay');
		$this->description = $this->l('PrestaShop’s Official Payment Solution, offering the most competitive rates in Europe.');

		// Compliancy
		$this->limited_countries = array('AT', 'BE', 'CH', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'IR', 'IT', 'LI', 'LU', 'LV', 'MC', 'MT', 'NL', 'PT', 'SE', 'SI', 'SK');
		$this->limited_currencies = array('CHF', 'EUR', 'GBP', 'SEK');

		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}

	public function install()
	{
		if (extension_loaded('soap') == false)
		{
			$this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
			return false;
		}

		$iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

		if (in_array($iso_code, $this->limited_countries) == false)
		{
			$this->_errors[] = $this->l('This module cannot work in your country');
			return false;
		}

		Configuration::updateValue('PSP_HIPAY_LIVE_MODE', false);

		return parent::install() &&
			$this->setCurrencies() &&
			$this->addWaitingOrderState() &&
			$this->registerHook('header') &&
			$this->registerHook('payment') &&
			$this->registerHook('paymentReturn') &&
			$this->registerHook('backOfficeHeader');
	}

	public function hookBackOfficeHeader()
	{
		if (Tools::getValue('configure') != 'psphipay')
			return false;

		$this->context->controller->addJS($this->_path.'views/js/back.js');
		$this->context->controller->addCSS($this->_path.'views/css/back.css');

		return '<script type="text/javascript">
			var email_error_message = "'.$this->l('Please, enter a valid email address').'.";
		</script>';
	}

	public function hookHeader()
	{
		return $this->context->controller->addCSS($this->_path.'/views/css/front.css');
	}

	/**
	 * Store the currencies list the module should work with
	 * @return boolean
	 */
	protected function setCurrencies()
	{
		$shops = Shop::getShops(true, null, true);

		foreach ($shops as $shop)
		{
			$sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)
					SELECT '.(int)$this->id.', "'.(int)$shop.'", `id_currency`
					FROM `'._DB_PREFIX_.'currency`
					WHERE `deleted` = \'0\' AND `iso_code` IN (\'CHF\', \'EUR\', \'GBP\', \'SEK\')';

			return (bool)Db::getInstance()->execute($sql);
		}
		return true;
	}

	/**
	 * Add waiting order state in database
	 * If it does not already exists
	 * @return boolean
	 */
	protected function addWaitingOrderState()
	{
		if ((bool)Configuration::get('PSP_HIPAY_OS_WAITING') == true)
			return true;

		$order_state = new OrderState();
		$order_state->name = array();

		foreach (Language::getLanguages(false) as $language)
		{
			if (Tools::strtolower($language['iso_code']) == 'fr')
				$order_state->name[(int)$language['id_lang']] = "En attente d'autorisation";
			else
				$order_state->name[(int)$language['id_lang']] = 'Waiting for authorization';
		}

		$order_state->color = '#4169E1';
		$order_state->hidden = false;
		$order_state->send_email = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add() == true)
		{
			Configuration::updateValue('PSP_HIPAY_OS_WAITING', $order_state->id);
			@copy($this->local_path.'/logo.gif', _PS_ORDER_STATE_IMG_DIR_.(int)$order_state->id.'.gif');

			return true;
		}

		return false;
	}

	/**
	 * Load configuration page
	 * @return string
	 */
	public function getContent()
	{
		$this->postProcess();
		$form = new PSPHipayForm($this);
		$user_account = new HipayUserAccount($this);

		// Generate configuration forms
		if (Configuration::get('PSP_HIPAY_USER_EMAIL'))
		{
			$this->context->smarty->assign(array(
				'is_logged' => true,
				'settings_form' => $form->getSettingsForm($user_account),
				'transactions_form' => $form->getTransactionsForm($user_account),
				'test_form' => $form->getTestForm(),
				'services_form' => $form->getCustomersServiceForm($user_account),
			));
		}
		else
		{
			$complete_form = $this->shouldDisplayCompleteLoginForm($user_account);

			$this->context->smarty->assign(array(
				'is_logged' => false,
				'login_form' => $form->getLoginForm($complete_form),
			));
		}

		// Set alert messages
		$this->context->smarty->assign(array(
			'form_errors' => $this->_errors,
			'form_successes' => $this->_successes,
			'form_infos' => $this->_warnings,
		));

		// Define templates paths
		$alerts = $this->local_path.'views/templates/admin/alerts.tpl';
		$configuration = $this->local_path.'views/templates/admin/configuration.tpl';

		$this->context->smarty->assign(array(
			'alerts' => $this->context->smarty->fetch($alerts),
			'module_dir' => $this->_path,
		));

		return $this->context->smarty->fetch($configuration);
	}

	protected function shouldDisplayCompleteLoginForm($user_account)
	{
		// If merchant tries to login / subscribe
		if (Tools::isSubmit('submitLogin') == true)
		{
			$email = Tools::getValue('install_user_email');

			if (Validate::isEmail($email))
			{
				$this->_warnings[] = $this->l('Please, enter account details');

				$available = $user_account->isEmailAvailable($email);
				return ($available == true) ? 'new_account': 'existing_account';
			}

			$this->module->_errors[] = $this->l('Invalid email address');
		}

		return false;
	}

	protected function postProcess()
	{
		if (Tools::isSubmit('submitReset') == true)
		{
			Configuration::deleteByName('PSP_HIPAY_USER_ACCOUNT_ID');
			Configuration::deleteByName('PSP_HIPAY_USER_SPACE_ID');
			Configuration::deleteByName('PSP_HIPAY_USER_EMAIL');
			Configuration::deleteByName('PSP_HIPAY_WEBSITE_ID');
			Configuration::deleteByName('PSP_HIPAY_WS_LOGIN');
			Configuration::deleteByName('PSP_HIPAY_WS_PASSWORD');

			return true;
		}
		elseif (Tools::isSubmit('submitLogin') == true)
		{
			$email = Tools::getValue('install_user_email');
			$firstname = Tools::getValue('install_user_firstname');
			$lastname = Tools::getValue('install_user_lastname');

			if ($email && $firstname && $lastname)
			{
				$is_email = (bool)Validate::isEmail($email);
				$is_firstname = (bool)Validate::isName($firstname);
				$is_lastname = (bool)Validate::isName($lastname);

				if ($is_email && $is_firstname && $is_lastname)
				{
					$user_account = new HipayUserAccount($this);
					return $user_account->createAccount($email, $firstname, $lastname);
				}
			}
		}
		elseif (Tools::isSubmit('submitTestMode') == true)
		{
			Configuration::updateValue('PSP_HIPAY_SANDBOX_MODE', (bool)Tools::getValue('test_account_mode'));

			return true;
		}


		return false;
	}

	/**
	 * Clear every single merchant account data
	 * @return boolean
	 */
	protected function clearAccountData()
	{
		Configuration::deleteByName('PSP_HIPAY_USER_ACCOUNT_ID', $result->userAccoundId);
		Configuration::deleteByName('PSP_HIPAY_USER_SPACE_ID', $result->userSpaceId);
		Configuration::deleteByName('PSP_HIPAY_USER_EMAIL', $email);
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_ID', $result->websiteId);
		Configuration::deleteByName('PSP_HIPAY_WS_LOGIN', $result->wsLogin);
		Configuration::deleteByName('PSP_HIPAY_WS_PASSWORD', $result->wsPassword);

		return true;
	}

	/**
	 * Display a payment button
	 * @param array $params
	 * @return string
	 */
	public function hookPayment($params)
	{
		$currency_id = $params['cart']->id_currency;
		$currency = new Currency((int)$currency_id);

		if (in_array($currency->iso_code, $this->limited_currencies) == false)
			return false;

		$this->smarty->assign(array(
			'domain' => Tools::getShopDomainSSL(true),
			'module_dir' => $this->_path,
			'payment_button' => $this->getPaymentButton(),
		));

		$this->smarty->assign('psphipay_prod', (bool)Configuration::get('PSP_HIPAY_LIVE_MODE'));

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	/**
	 * Display the payment confirmation page
	 * @param array $params
	 */
	public function hookPaymentReturn($params)
	{
		if ($this->active == false)
			return;

		$order = $params['objOrder'];

		if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR'))
			$this->smarty->assign('status', 'ok');

		$this->smarty->assign(array(
			'id_order' => $order->id,
			'reference' => $order->reference,
			'params' => $params,
			'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
		));

		return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
	}

	/**
	 * Get the appropriate payment button's image
	 * @return string
	 */
	protected function getPaymentButton()
	{
		$iso_code = Tools::strtolower($this->context->country->iso_code);

		if (file_exists(dirname(__FILE__).'/img/payment_buttons/'.$iso_code.'.png'))
			return $this->_path.'/img/payment_buttons/'.$iso_code.'.png';
		return $this->_path.'/img/payment_buttons/default.png';
	}

	/**
	 * Check if the given currency is supported by the provider
	 * @param string $iso_code currency iso code
	 * @return boolean
	 */
	public function isSupportedCurrency($iso_code)
	{
		return in_array(Tools::strtoupper($iso_code), $this->limited_currencies);
	}
}

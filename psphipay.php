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

require_once(dirname(__FILE__).'/classes/HipayConfigForm.php');
require_once(dirname(__FILE__).'/classes/HipayConfigFormAlerts.php');

require_once(dirname(__FILE__).'/classes/webservice/HipayBusiness.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayLocale.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayTopic.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayUserAccount.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayWS.php');

class PSPHipay extends PaymentModule
{
	protected $config_form = false;

	public function __construct()
	{
		$this->name = 'psphipay';
		$this->tab = 'payments_gateways';
		$this->version = '2.0';
		$this->module_key = '';

		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->author = 'PrestaShop';
		
		$this->bootstrap = true;
		$this->display = 'view';

		parent::__construct();

		$this->displayName = $this->l('PrestaShop Payment - By Hipay');
		$this->description = $this->l('Secure payement with Visa, Mastercard and European solutions.');

		// Compliancy
		$this->limited_countries = array('AT', 'BE', 'CH', 'CY', 'DE', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'IT', 'IR', 'LI', 'LU', 'LV', 'MC', 'MT', 'NL', 'PT', 'SE', 'SI', 'SK');
		$this->limited_currencies = array('CHF', 'EUR', 'GBP', 'SEK');

		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
	}
	
	public function install()
	{
		Configuration::updateValue('PSP_HIPAY_LIVE_MODE', false);

		return parent::install() &&
			$this->setCurrencies() &&
			$this->registerHook('header') &&
			$this->registerHook('payment') &&
			$this->registerHook('backOfficeHeader');
	}
	
	public function hookBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/configure.css');
		$this->context->controller->addJS($this->_path.'js/configure.js');

		return '<script type="text/javascript">
			var admin_psphipay_ajax_url = "'.$this->context->link->getAdminLink('AdminPSPHipay').'";
		</script>';
	}

	protected function setCurrencies()
	{
		if (!$shops)
			$shops = Shop::getShops(true, null, true);

		$currencies = implode(', ', $this->limited_currencies);

		foreach ($shops as $shop)
		{
			if (!Db::getInstance()->execute('
					INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)
					SELECT '.(int)$this->id.', "'.(int)$shop.'", `id_currency` FROM `'._DB_PREFIX_.'currency` WHERE `deleted` = \'0\' AND `iso_code` IN (\'CHF\', \'EUR\', \'GBP\', \'SEK\')'))
				return false;
		}
		return true;
	}
	
	public function getContent()
	{
		HipayConfigFormAlerts::getInstance();

		if (Tools::isSubmit('submitPSPHipay'))
			$this->_postProcess();

		$this->context->smarty->assign(
			array(
				'form_errors' => HipayConfigFormAlerts::getFormErrors(),
				'form_infos' => HipayConfigFormAlerts::getFormInfos(),
				'form_successes' => HipayConfigFormAlerts::getFormSuccesses(),
				'module_dir' => $this->_path,
				'module_local_dir' => $this->local_path
			)
		);
		
		$this->config_form = new HipayConfigForm();
		
		$output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
		return $output.$this->renderForm().'<hr />';
	}
	
	protected function renderForm()
	{
		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$helper->module = $this;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
	 
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitPSPHipay';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
		   .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$helper->tpl_vars = array(
			'fields_value' => $this->config_form->getFormsFieldsValues(),
			'id_language' => $this->context->language->id,
			'languages' => $this->context->controller->getLanguages(),
		);

		return $helper->generateForm($this->config_form->getForms());
	}

	protected function _postProcess()
	{
		$disconnect = Tools::getValue('submitOptionsmodule', false);
		
		if ($disconnect == true)
			return $this->disconnect();

		$email = Tools::getValue('install_user_email');
		$user = new HipayUserAccount($email);

		if (Validate::isEmail($email) === true)
		{
			if ($user->isEmailAvailable() === true)
				return $this->newUserAccount($user);
			elseif ($user->isValidAccount() === true)
			{
				$this->clearAccountData();
				$user->saveUserAccountInfos();
				$success = 'Configuration saved!';
				return HipayConfigFormAlerts::registerFormSuccess($success);
			}
			else
			{
				HipayConfigFormAlerts::registerFormError('A problem occured while trying to connect to your account.');
				HipayConfigFormAlerts::registerFormError('Please, try again later.');
				return false;
			}
		}
		elseif (empty($email) === false)
		{
			HipayConfigFormAlerts::registerFormError('The email address you entered is not valid!');
			return false;
		}
	}

	protected function clearAccountData()
	{
		Configuration::deleteByName('PSP_HIPAY_USER_EMAIL');
		Configuration::deleteByName('PSP_HIPAY_USER_ACCOUNT_ID');
		Configuration::deleteByName('PSP_HIPAY_USER_SUBACCOUNTS');
		Configuration::deleteByName('PSP_HIPAY_USER_PASSWORD');
		Configuration::deleteByName('PSP_HIPAY_SPACE_ID');
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_ID');
		Configuration::deleteByName('PSP_HIPAY_CURRENCY');
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_EMAIL');
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_ID');
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_NAME');
		Configuration::deleteByName('PSP_HIPAY_WEBSITE_URL');
		Configuration::deleteByName('PSP_HIPAY_BUSINESS_LINE_ID');
		Configuration::deleteByName('PSP_HIPAY_BUSINESS_LINE_LABEL');
		Configuration::deleteByName('PSP_HIPAY_TOPIC_ID');
		Configuration::deleteByName('PSP_HIPAY_TOPIC_LABEL');

		return true;
	}

	protected function disconnect($silent = false)
	{
		$this->clearAccountData();

		if ($silent === false)
			HipayConfigFormAlerts::registerFormInfo('You have been disconnected. Enter your merchant email again to use your module.');

		return true;
	}

	protected function newUserAccount($user)
	{
		$this->disconnect('silent');

		if ($this->isPostDataValid() == false)
			return false;

		$locale = new HipayLocale();

		if ($locale->getLocale() === false)
		{
			$error = 'Your default country is not compatible with the Hipay API';
			HipayConfigFormAlerts::registerFormError($error);
		}
		elseif ($user->createUserAccount() === false)
		{
			$error = 'The account creation failed. Please, contact the Hipay customers\' service.';
			HipayConfigFormAlerts::registerFormError($error);
		}
		else
		{
			HipayConfigFormAlerts::registerFormSuccess('Greatings! Your PrestaShop Payments (by Hipay) account has been associated to your shop!');
			HipayConfigFormAlerts::registerFormSuccess('You can see all the details associated to your account in the "Settings" tab.');
			return true;
		}

		return false;
	}

	protected function isPostDataValid()
	{
		$valid = true;

		if ((Tools::isSubmit('install_user_email') == true) &&
			((Tools::isSubmit('install_user_firstname') == true) &&
			(Tools::isSubmit('install_user_lastname') == true) &&
			(Tools::isSubmit('install_user_shop_name') == true)))
		{
			if (Validate::isName(Tools::getValue('install_user_firstname')) == false)
			{
				HipayConfigFormAlerts::registerFormError('The first name you entered is not valid!');
				$valid = false;
			}
			if (Validate::isName(Tools::getValue('install_user_lastname')) == false)
			{
				HipayConfigFormAlerts::registerFormError('The last name you entered is not valid!');
				$valid = false;
			}
		}
		elseif ((Tools::isSubmit('install_user_email') == true) &&
			((Tools::isSubmit('install_user_firstname') == false) &&
			(Tools::isSubmit('install_user_lastname') == false) &&
			(Tools::isSubmit('install_user_shop_name') == false)))
		{
			return false;
		}

		return $valid;
	}
	
	public function hookPayment($params)
	{
		$this->smarty->assign(array(
			'domain' => Tools::getShopDomainSSL(true),
			'module_dir' => $this->_path,
			'payment_button' => $this->getPaymentButton(),
		));

		return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
	}

	public function hookHeader()
	{
		return $this->context->controller->addCSS($this->_path.'/css/psp_hipay.css');
	}

	protected function getPaymentButton()
	{
		$iso_code = strtoupper($this->context->country->iso_code);

		if (file_exists(dirname(__FILE__).'/img/payment_buttons/'.$iso_code.'.png'))
			return $this->_path.'/img/payment_buttons/'.$iso_code.'.png';
		return $this->_path.'/img/payment_buttons/DEFAULT.png';
	}

	public function isSupportedCurrency($iso_code)
	{
		return (bool)in_array(strtoupper($iso_code), $this->limited_currencies);
	}
}

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

class HipayConfigForm
{
	/* PSP Hipay instance */
	protected $psp = false;
	protected $user = false;

	public function __construct()
	{
		$this->psp = new PSPHipay();

		if ($email = Configuration::get('PSP_HIPAY_USER_EMAIL'))
			$this->user = new HipayUserAccount($email);
	}

	public function getForms()
	{
		if (Configuration::get('PSP_HIPAY_USER_EMAIL'))
		{
			return array(
				$this->getInstallForm(),
				$this->getSettingsForm(),
				$this->getCustomersForm(),
			);
		}
		else
			return array($this->getInstallForm());
	}

	protected function getInstallForm()
	{
		$params = array(
			'class' => 'fixed-width-xxl',
			'desc' => $this->psp->l('Please, enter your email address in the field bellow'),
		);

		if ($this->user != false)
			if ($this->user->isValidAccount() === true)
				$params['prefix'] = '<i class="icon-check"></i>';

		$form = array(
			'form' => array(
				'legend' => $this->generateLegend('Install', 'icon-wrench'),
				'input' => array(
					$this->generateInputFree('install_desc', false, array('col' => 12, 'offset' => 0)),
					array(
						'type' => 'switch',
						'label' => $this->psp->l('Live mode'),
						'name' => 'install_live_mode',
						'is_bool' => true,
						'desc' => $this->psp->l('Use this module in live mode'),
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->psp->l('Enabled')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->psp->l('Disabled')
							)
						),
					),
					$this->generateInputText('install_user_email', 'Email', $params),
				),
				'submit' => $this->generateSubmitButton('Save'),
			),
		);

		$this->getAdditionnalInfos($form);
		$disconnect = Tools::getValue('submitOptionsmodule', false);

		/* If module is configured & connected */
		if (Configuration::get('PSP_HIPAY_USER_EMAIL'))
		{
			$form['form']['legend'] = $this->generateLegend('My account', 'icon-wrench');

			$form['form']['buttons'] = array(
				$this->generateSubmitButton('Disconnect', array(
					'icon' => 'process-icon-power',
					'value' => 'disconnect',
					'js' => 'javascript:return confirm(\''.$this->psp->l('Are you sure?').'\')'
				))
			);

			$form['form']['submit'] = $this->generateSubmitButton('Save');
		}
		elseif ((Tools::isSubmit('install_user_email') == true) && ($disconnect != true))
			$form['form']['submit'] = $this->generateSubmitButton('Subscribe');
		else
			$form['form']['submit'] = $this->generateSubmitButton('Log in');

		return $form;
	}

	protected function getAdditionnalInfos(&$form)
	{
		$disconnect = Tools::getValue('submitOptionsmodule', false);

		if ((Tools::isSubmit('install_user_email') == true) && ($disconnect != true))
		{
			$email = Tools::getValue('install_user_email');
			$user = new HipayUserAccount($email);

			if (($user != false) && ($user->isEmailAvailable() === true))
			{
				$form['form']['input'][] = $this->generateInputText('install_user_firstname', 'First name', array('class' => 'fixed-width-xxl'));
				$form['form']['input'][] = $this->generateInputText('install_user_lastname', 'Last name', array('class' => 'fixed-width-xxl'));
				$form['form']['input'][] = $this->generateInputText('install_user_shop_name', 'Shop name', array('class' => 'fixed-width-xxl'));
			}
		}
	}

	protected function getSettingsForm()
	{
		$form = array(
			'form' => array(
				'legend' => $this->generateLegend('Settings', 'icon-cog'),
				'input' => array(
					$this->generateInputFree('settings_main_account_desc', false, array('col' => 12, 'offset' => 0)),
					$this->generateInputFree('settings_email', 'Email'),
					$this->generateInputFree('settings_shop_name', 'Shop name'),
					$this->generateInputFree('settings_account_number', 'Account number'),
					$this->generateInputFree('settings_account_balance', 'Balance'),
					$this->generateFormSplit(),
					$this->generateInputFree('settings_sub_accounts_desc', false, array('col' => 12, 'offset' => 0)),
				),
			),
		);

		$sub_accounts = unserialize(Configuration::get('PSP_HIPAY_USER_SUBACCOUNTS'));

		if (count($sub_accounts) == 0)
			$form['form']['input'][] = $this->generateInputFree('settings_no_sub_accounts', false, array('col' => 9, 'offset' => 3));
		else
			$form['form']['input'][] = $this->generateInputFree('settings_sub_accounts_list', false, array('col' => 5, 'offset' => 0));

		return $form;
	}

	protected function getCustomersForm()
	{
		return array(
			'form' => array(
				'legend' => $this->generateLegend('Customer\'s service', 'icon-users'),
				'input' => array(
					$this->generateInputFree('customer_area_description', false, array('col' => 12, 'offset' => 0)),
					$this->generateInputFree('customer_area_email', 'Email'),
					$this->generateInputFree('customer_area_shop_name', 'Shop name'),
					$this->generateInputFree('customer_area_account_number', 'Account number'),
					$this->generateFormSplit(),
					$this->generateInputFree('customer_area_availability', false, array('col' => 12, 'offset' => 0)),
					$this->generateInputFree('customer_area_contact_email', 'By email'),
					$this->generateInputFree('customer_area_contact_phone', 'By phone'),
					$this->generateInputFree('customer_area_contact_postal', 'By postal services'),
				),
			),
		);
	}

	public function getFormsFieldsValues()
	{
		$locales_ws = new HipayLocale();

		$install_form_fields = $this->getInstallFormFields();

		if (Configuration::get('PSP_HIPAY_USER_EMAIL'))
			$settings_form_fields = $this->getSettingsFormFields();
		else
			$settings_form_fields = array();

		if (((bool)Configuration::get('PSP_HIPAY_LIVE_MODE')) == true)
			$domain = 'https://www.hipay.com';
		else
			$domain = 'https://test-www.hipaywallet.com';

		return array_merge($install_form_fields, $settings_form_fields, array(
				/* Form Split */
				'input_split' => '<hr />',

				/* Install */
				'install_desc' => '<h4 class="form-control-static">'.$this->psp->l('Account details').'</h4>',
				'install_locale' => $locales_ws->getLocales(),

				/* Settings */
				'settings_main_account_desc' => '<h4 class="form-control-static">'.$this->psp->l('Main account').'</h4>',
				'settings_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_WEBSITE_EMAIL').'</strong></p>',
				'settings_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_WEBSITE_NAME').'</strong></p>',
				'settings_account_number' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID').'</strong></p>',
				'settings_sub_accounts_desc' => '<h4 class="form-control-static">'.$this->psp->l('Sub-accounts').'</h4>',

				/* Customer area */
				'customer_area_description' => '<h4 class="form-control-static" style="margin-bottom: 0">'.$this->psp->l('You want to contact the Hipay customers\' service?').'</h4>',
				'customer_area_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_WEBSITE_EMAIL').'</strong></p>',
				'customer_area_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_WEBSITE_NAME').'</strong></p>',
				'customer_area_account_number' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID').'</strong></p>',
				'customer_area_availability' => '<h4 class="form-control-static">'.$this->psp->l('The Hipay customers\' service is available from monday to friday 10am to 6pm to answer to all of your questions.').'</h4>',
				'customer_area_contact_email' => '<p class="form-control-static"><a href="'.$domain.'/info/contact" target="_blank">'.$this->psp->l('Contact the customers\' service').'</a></p>',
				'customer_area_contact_phone' => '<p class="form-control-static">01 40 18 30 04</p>',
				'customer_area_contact_postal' => '<p class="form-control-static">55 rue Raspail, 92300 Levallois-Peret, Paris</p>',
			)
		);
	}

	public function getInstallFormFields()
	{
		$disconnect = Tools::getValue('submitOptionsmodule', false);

		if ($disconnect == true)
		{
			return array(
				'install_live_mode' => Configuration::get('PSP_HIPAY_LIVE_MODE'),
				'install_user_email' => Configuration::get('PSP_HIPAY_USER_EMAIL'),
			);
		}
		else
		{
			return array(
				'install_live_mode' => Tools::getValue('install_live_mode', Configuration::get('PSP_HIPAY_LIVE_MODE')),
				'install_user_email' => Tools::getValue('install_user_email', Configuration::get('PSP_HIPAY_USER_EMAIL')),

				'install_user_firstname' => Tools::getValue('install_user_firstname', Context::getContext()->employee->firstname),
				'install_user_lastname' => Tools::getValue('install_user_lastname', Context::getContext()->employee->lastname),
				'install_user_shop_name' => Tools::getValue('install_user_shop_name', Configuration::get('PS_SHOP_NAME')),
			);
		}
	}

	public function getSettingsFormFields()
	{
		$main_account = $this->user->getMainAccountBalance();
		$sub_accounts = $this->user->getSubAccountsBalances();

		if (is_array($sub_accounts) && (count($sub_accounts) > 0))
		{
			Context::getContext()->smarty->assign(array(
				'sub_accounts' => $sub_accounts,
				'user' => $this->user,
			));

			$template_path = _PS_MODULE_DIR_.$this->psp->name.'/views/templates/admin/sub_accounts.tpl';

			return array(
				'settings_account_balance' => '<p class="form-control-static">'.number_format($main_account->balance, '2', ',', '').' '.$main_account->currency.'</p>',
				'settings_sub_accounts_list' => Context::getContext()->smarty->fetch($template_path)
			);
		}
		else
		{
			return array(
				'settings_account_balance' => '<p class="form-control-static">'.number_format($main_account->balance, '2', ',', '').' '.$main_account->currency.'</p>',
				'settings_no_sub_accounts' => '<p class="form-control-static">'.$this->psp->l('Nos sub-accounts found.').'</p>',
			);
		}
	}

	/* Form items */
	public function generateLegend($title, $icon = false)
	{
		return array(
			'title' => $this->psp->l($title),
			'icon' => $icon,
		);
	}

	public function generateFormSplit()
	{
		$params = array(
			'col' => 12,
			'offset' => 0
		);

		return $this->generateInput('free', 'input_split', null, $params);
	}

	public function generateInput($type, $name, $label = false, $params = array())
	{
		$input = array(
			'type' => $type,
			'label' => $this->psp->l($label),
			'name' => $name,
		);

		if (is_array($params) === true)
			foreach ($params as $key => $value)
				$input[$key] = $value;

		return $input;
	}

	public function generateInputFree($name, $label = false, $params = array())
	{
		return $this->generateInput('free', $name, $label, $params);
	}

	public function generateInputText($name, $label = false, $params = array())
	{
		return $this->generateInput('text', $name, $label, $params);
	}

	public function generateSubmitButton($title, $params = array())
	{
		$input = array(
			'title' => $title,
			'type' => 'submit',
			'class' => 'btn btn-default pull-right'
		);

		if (is_array($params) === true)
			foreach ($params as $key => $value)
				$input[$key] = $value;

		return $input;
	}
}
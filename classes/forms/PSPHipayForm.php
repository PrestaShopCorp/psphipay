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

require_once(dirname(__FILE__).'/PSPHipayFormInputs.php');

class PSPHipayForm extends PSPHipayFormInputs {

	protected $context = false;
	protected $helper = false;
	protected $module = false;

	public $name = false;

	public function __construct($module_instance)
	{
		// Requirements
		$this->context = Context::getContext();
		$this->module = $module_instance;
		$this->name = $module_instance->name;

		// Form
		$this->helper = new HelperForm();

		$this->helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false);
		$this->helper->currentIndex .= '&'.http_build_query(array(
			'configure' => 'psphipay',
			'tab_module' => 'payments_gateways',
			'module_name' => 'psphipay',
		));

		$this->helper->module = $this;
		$this->helper->show_toolbar = false;
		$this->helper->token = Tools::getAdminTokenLite('AdminModules');

		$this->helper->tpl_vars = array(
			'id_language' => $this->context->language->id,
			'languages' => $this->context->controller->getLanguages()
		);

		return $this->helper;
	}

	public function generateForm($form)
	{
		return $this->helper->generateForm($form);
	}

	/**
	 * Customer's service form
	 */
	public function getCustomersServiceForm($user_account)
	{
		$this->helper->tpl_vars['fields_value'] = $this->getCustomersServiceFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

				$this->generateInputFree('customers_service_contact_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('customers_service_contact_form', 'By email'),
				$this->generateInputFree('customers_service_phone_number', 'By phone'),
				$this->generateInputFree('customers_service_address', 'Address'),

				$this->generateInputFree('customers_service_contact_info', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('customers_service_email', 'Email'),
				$this->generateInputFree('customers_service_shop_name', 'Shop name'),
				$this->generateInputFree('customers_service_account_id', 'Account number'),

				$this->generateInputFree('customers_service_q_a', false, array('col' => 12, 'offset' => 0)),
			),
		));

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Login form
	 */
	public function getLoginForm($complete_form = false)
	{
		$this->helper->tpl_vars['fields_value'] = $this->getLoginFormValues($complete_form);

		$email = Tools::getValue('install_user_email');
		$is_email = (bool)Validate::isEmail($email);
		$email_description = $is_email ? null : 'Please, enter your email address in the field above';

		$form = array('form' => array(
			'legend' => $this->generateLegend('Login', 'icon-user'),
			'input' => array(
				$this->generateInputEmail('install_user_email', 'Email', $email_description),
			),
		));

		if ($complete_form == 'new_account')
		{
			$form['form']['input'][] = $this->generateInputText('install_user_first_name', 'First name', array(
				'class' => 'fixed-width-xxl',
				'required' => true,
			));
			$form['form']['input'][] = $this->generateInputText('install_user_last_name', 'Last name', array(
				'class' => 'fixed-width-xxl',
				'required' => true,
			));

			$form['form']['buttons'][] = $this->generateSubmitButton('Reset', array(
				'class' => 'pull-left',
				'name' => 'submitReset',
				'icon' => 'process-icon-eraser',
			));
			$form['form']['buttons'][] = $this->generateSubmitButton('Subscribe', array(
				'name' => 'submitLogin',
				'icon' => 'process-icon-next',
			));
		}
		elseif ($complete_form == 'existing_account')
		{
			$form['form']['input'][] = $this->generateInputText('install_website_id', 'Website ID', array(
				'class' => 'fixed-width-lg',
				'hint' => $this->module->l('You can find it on your HiPay account, section "Merchant Tool Kit > API", under "Webservice access'),
				'required' => true,
			));
			$form['form']['input'][] = $this->generateInputText('install_ws_login', 'WS Login', array(
				'class' => 'fixed-width-xxl',
				'hint' => $this->module->l('You can find it on your HiPay account, section "Merchant Tool Kit > API", under "Webservice access'),
				'required' => true,
			));
			$form['form']['input'][] = $this->generateInputText('install_ws_password', 'WS Password', array(
				'class' => 'fixed-width-xxl',
				'hint' => $this->module->l('You can find it on your HiPay account, section "Creating a button" under the URL of your website'),
				'required' => true,
			));

			$form['form']['buttons'][] = $this->generateSubmitButton('Reset', array(
				'class' => 'pull-left',
				'name' => 'submitReset',
				'icon' => 'process-icon-eraser',
			));
			$form['form']['buttons'][] = $this->generateSubmitButton('Log in', array(
				'name' => 'submitLogin',
				'icon' => 'process-icon-next',
			));
		}
		else
		{
			$form['form']['input'][] = $this->generateInputFree('install_user_info', false, array('col' => 12, 'offset' => 0));

			$form['form']['buttons'][] = $this->generateSubmitButton('Log in / Subscribe', array(
				'name' => 'submitLogin',
				'icon' => 'process-icon-next',
			));
		}

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Settings form
	 */
	public function getSettingsForm($user_account)
	{
		$this->helper->tpl_vars['fields_value'] = $this->getSettingsFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

				$this->generateInputFree('main_account_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('main_account_email', 'Email'),
				$this->generateInputFree('main_account_shop_name', 'Shop name'),
				$this->generateInputFree('main_account_id', 'Account ID'),
				$this->generateInputFree('main_account_balance', 'Balance', array(
					'hint' => 'Your account balance is automatically updated after each new transaction',
				)),

				$this->generateInputFree('sub_accounts_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('sub_accounts_description', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('sub_accounts_values', false, array('col' => 12, 'offset' => 0)),
			),
			'buttons' => array(
				$this->generateSubmitButton('Disconnect', array(
					'name' => 'submitReset',
					'icon' => 'process-icon-power',
				)),
			),
		));

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Settings form
	 */
	public function getSandboxForm()
	{
		$this->helper->tpl_vars['fields_value'] = $this->getSandboxFormValues();

		$form = array('form' => array(
			'input' => array(
				$this->generateSwitchButton('sandbox_account_mode', 'Test mode'),
				$this->generateInputText('sandbox_website_id', 'Website ID', array('class' => 'fixed-width-lg')),
				$this->generateInputText('sandbox_ws_login', 'WS Login', array('class' => 'fixed-width-xxl')),
				$this->generateInputText('sandbox_ws_password', 'WS Password', array('class' => 'fixed-width-xxl')),
				$this->generateInputFree('sandbox_mode_info', false, array('col' => 12, 'offset' => 0)),
			),
			'buttons' => array(
				$this->generateSubmitButton('Save', array(
					'name' => 'submitSandboxMode',
					'icon' => 'process-icon-save',
				)),
			),
		));

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Transactions form
	 */
	public function getTransactionsForm($user_account)
	{
		$this->helper->tpl_vars['fields_value'] = $this->getTransactionsFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('info_sandbox_mode', false, array('col' => 12, 'offset' => 0)),

				$this->generateInputFree('transactions_account_id', 'Account ID'),
				$this->generateInputFree('transactions_current_date', 'Date'),
				$this->generateInputFree('transactions_dates_range', 'Range'),
				$this->generateInputFree('transactions_details', 'Transactions', array('col' => 9, 'offset' => 0)),
			),
			'buttons' => array(
				$this->generateSubmitButton('Refresh', array(
					'name' => 'submitDateRange',
					'icon' => 'process-icon-refresh',
					'value' => 'refresh',
				)),
			),
		));

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Customer's service form values
	 * @param string $complete_form
	 */
	public function getCustomersServiceFormValues()
	{
		$sandbox_mode = Configuration::get('PSP_HIPAY_SANDBOX_MODE');
		$user_account_id = $sandbox_mode ? Configuration::get('PSP_HIPAY_SANDBOX_USER_ACCOUNT_ID') : Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID');

		$template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/faq.tpl';

		return array(
			'info_sandbox_mode' => $sandbox_mode ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.').'</div>' : null,

			'customers_service_contact_details' =>  '<h4 class="form-control-static">'.$this->module->l('I need some help, who should I contact?').'</h4>',
			'customers_service_contact_form' => '<p class="form-control-static"><a href="https://Sandbox-www.hipaywallet.com/info/contact" target="_blank">'.$this->module->l('Send a message').'</a></strong></p>',
			'customers_service_phone_number' => '<p class="form-control-static">'.$this->module->l('XXXXXXXXXXXXX').'</strong></p>',
			'customers_service_address' => '<p class="form-control-static">'.sprintf($this->module->l('HiPay - Société HPME / HiMedia Group%1$sSeed Factory%1$s19 Avenue des Volontaires%1$s1160 Bruxelles - Belgium'), '<br />').'</strong></p>',

			'customers_service_contact_info' =>  '<h4 class="form-control-static">'.$this->module->l('Please remind them your account details').'</h4>',
			'customers_service_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_EMAIL').'</strong></p>',
			'customers_service_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
			'customers_service_account_id' => '<p class="form-control-static"><strong>'.$user_account_id.'</strong></p>',

			'customers_service_q_a' => $this->context->smarty->fetch($template_path),
		);
	}

	/**
	 * Login form values
	 * @param string $complete_form
	 */
	public function getLoginFormValues($complete_form = false)
	{
		$values = array(
			'install_user_email' => Tools::getValue('install_user_email', Configuration::get('PSP_HIPAY_USER_EMAIL')),
			'install_user_info' => $this->module->l('If you have any questions or need help creating a PrestaShop Payments by HiPay account, contact us at prestashop@hipay.com'),
		);

		if ($complete_form == 'new_account')
		{
			$values['install_user_first_name'] = Tools::getValue('install_user_first_name');
			$values['install_user_last_name'] = Tools::getValue('install_user_last_name');
		}
		elseif ($complete_form == 'existing_account')
		{
			$values['install_website_id'] = Tools::getValue('install_website_id', Configuration::get('PSP_HIPAY_WEBSITE_ID'));
			$values['install_ws_login'] = Tools::getValue('install_ws_login', Configuration::get('PSP_HIPAY_WS_LOGIN'));
			$values['install_ws_password'] = Tools::getValue('install_ws_password', Configuration::get('PSP_HIPAY_WS_PASSWORD'));
		}

		return $values;
	}

	/**
	* Sandbox form values
	*/
	public function getSandboxFormValues()
	{
		return array(
			'sandbox_account_mode' => Tools::getValue('sandbox_account_mode', Configuration::get('PSP_HIPAY_SANDBOX_MODE')),
			'sandbox_website_id' => Tools::getValue('sandbox_website_id', Configuration::get('PSP_HIPAY_SANDBOX_WEBSITE_ID')),
			'sandbox_ws_login' => Tools::getValue('sandbox_ws_login', Configuration::get('PSP_HIPAY_SANDBOX_WS_LOGIN')),
			'sandbox_ws_password' => Tools::getValue('sandbox_ws_password', Configuration::get('PSP_HIPAY_SANDBOX_WS_PASSWORD')),
			'sandbox_mode_info' => '<p class="form-control-static">'.
			$this->module->l('You have received two set of credentials when your account has been created: one for the main account, one for your test account.').'<br />'.
			$this->module->l('Use your test credentials to connect to HiPay and retrieve the needed info (Website ID, WS login and password). Enter it above, and then save.').'<br /> '.
			$this->module->l('You can now make some tests!').'<br />'.'<br />'.
			sprintf($this->module->l('For more info on how to conduct your tests, please %1$sclick here to read the FAQ%2$s.'), '<a href="#">', '</a>').
			'</p>',
		);
	}

	/**
	 * Settings form values
	 */
	public function getSettingsFormValues($user_account)
	{
		$sandbox_mode = Configuration::get('PSP_HIPAY_SANDBOX_MODE');
		$user_account_id = $sandbox_mode ? Configuration::get('PSP_HIPAY_SANDBOX_USER_ACCOUNT_ID') : Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID');

		$accounts = $user_account->getBalances();
		$account = $user_account->getMainAccountBalance($accounts);

		$main_account_values = array(
			'info_sandbox_mode' => Configuration::get('PSP_HIPAY_SANDBOX_MODE') ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.').'</div>' : null,

			'main_account_details' => '<h4 class="form-control-static">'.$this->module->l('Your main account').'</h4>',
			'main_account_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_EMAIL').'</strong></p>',
			'main_account_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
			'main_account_id' => '<p class="form-control-static"><strong>'.$user_account_id.'</strong></p>',
			'main_account_balance' => '<p class="form-control-static"><strong>'.number_format($account->balance, 2).' '.(string)$account->currency.'</strong></p>',
		);

		$details = null;

		foreach ($accounts->balances->item as $sub_account)
			if ($sub_account->userAccountId != $account->userAccountId)
				$details .= '<tr>
					<td>'.$sub_account->userAccountId.'</td>
					<td>'.$this->module->l($this->module->currencies_titles[(string)$sub_account->currency]).'</td>
					<td>'.number_format($sub_account->balance, 2).' '.(string)$sub_account->currency.'</td>
				</tr>';

		$sub_accounts_values = array(
			'sub_accounts_details' => '<h4 class="form-control-static">'.$this->module->l('Sub-accounts').'</h4>',
			'sub_accounts_description' => '<p class="form-control-static">'.
				$this->module->l('Thanks to the below sub-accounts, you can accept payments in several currencies on your store.').'<br />'.
				$this->module->l('To withdraw money from your sub-accounts, you should transfer their respective balances to your main account first.').' '.
				sprintf($this->module->l('Some fees might apply, please %1$sclick here for more info%2$s.'), '<a href="#">', '</a>').
			'</p>',
			'sub_accounts_values' => '<table class="form-control-static table table-bordered table-hover table-striped">
			<thead>
				<tr>
					<th><strong>'.$this->module->l('Account ID').'</strong></th>
					<th><strong>'.$this->module->l('Currency').'</strong></th>
					<th><strong>'.$this->module->l('Balance').'</strong></th>
				</tr>
			</thead>
			<tbody>'.$details.'</tbody>
			</table>',
		);

		return array_merge($main_account_values, $sub_accounts_values);
	}

	/**
	 * Transactions form values
	 */
	public function getTransactionsFormValues($user_account)
	{
		$calendar_helper = new HelperCalendar();

		$psp_hipay_date_from = isset($this->context->cookie->psp_hipay_date_from)? $this->context->cookie->psp_hipay_date_from : date('Y-m-dT').'00:00:00';
		$psp_hipay_date_to = isset($this->context->cookie->psp_hipay_date_to) ? $this->context->cookie->psp_hipay_date_to : date('Y-m-dT').'23:59:59';

		$calendar_helper->setDateFrom(Tools::getValue('date_from', date('Y-m-d', strtotime($psp_hipay_date_from))));
		$calendar_helper->setDateTo(Tools::getValue('date_to', date('Y-m-d', strtotime($psp_hipay_date_to))));

		$user_account_id = Configuration::get('PSP_HIPAY_SANDBOX_MODE') ? Configuration::get('PSP_HIPAY_SANDBOX_USER_ACCOUNT_ID') : Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID');

		$template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/transactions.tpl';

		$this->context->smarty->assign(array(
			'date_from' => date('Y-m-d 00:00', strtotime($psp_hipay_date_from)),
			'date_to' => date('Y-m-d 23:59', strtotime($psp_hipay_date_to)),
			'transactions_dates_form' => $calendar_helper->generate(),
		));

		$calendar_template = $this->context->smarty->fetch($template_path);

		$transactions_values = array(
			'info_sandbox_mode' => Configuration::get('PSP_HIPAY_SANDBOX_MODE') ? '<div class="alert alert-warning">'.$this->module->l('The module is running in test mode.').'</div>' : null,

			'transactions_account_id' => '<p class="form-control-static">N&deg;'.$user_account_id.'</p>',
			'transactions_current_date' => '<p class="form-control-static">'.date('Y-m-d H:i:s').'</p>',
			'transactions_dates_range' => $calendar_template,
		);

		$details = null;
		$transactions = $user_account->getTransactions();
        
		if ((is_array($transactions) == true) && (count($transactions) > 0))
		{
			foreach ($transactions as $transaction)
			{
                switch ($transaction->transactionStatus)
                {
                    case 'CAPTURED':
                        $icon = 'check';
                        break;
                    case 'UNAUTHED':
                        $icon = 'remove';
                        break;
                    default:
                        $icon = 'clock-o';
                        break;
                }
                
				$details .= '<tr>
					<td>'.$transaction->createdAt.'</td>
					<td>'.number_format($transaction->amount, 2).' '.(string)$transaction->currency.'</td>
					<td>'.number_format($transaction->fees, 2).' '.(string)$transaction->currencyFees.'</td>
					<td class="text-center"><i class="icon icon-'.$icon.'"></i></td>
				</tr>';
			}
		}
		else
			$details = '<tr><td colspan="4" class="text-center"><em>'.$this->module->l('You have no transaction for the selected period').'.</em></td></tr>';


			$transactions_values['transactions_details'] = '<table class="form-control-static table table-bordered table-hover table-striped">
				<thead>
					<tr>
						<th><strong>'.$this->module->l('Created at').'</strong></th>
						<th><strong>'.$this->module->l('Amount').'</strong></th>
						<th><strong>'.$this->module->l('Fees').'</strong></th>
						<th><strong>'.$this->module->l('Captured').'</strong></th>
					</tr>
				</thead>
				<tbody>'.$details.'</tbody>
			</table>';

		return $transactions_values;
	}

}

<?php

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

	protected function form_lang()
	{
		$this->helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
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
		$this->helper->submit_action = 'submitPSPHipayConfiguration';
		$this->helper->tpl_vars['fields_value'] = $this->getCustomersServiceFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('customers_service_contact_info', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('customers_service_email', 'Email'),
				$this->generateInputFree('customers_service_shop_name', 'Shop name'),
				$this->generateInputFree('customers_service_account_number', 'Account number'),

				$this->generateInputFree('customers_service_contact_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('customers_service_contact_form', 'By email'),
				$this->generateInputFree('customers_service_phone_number', 'By phone'),
				$this->generateInputFree('customers_service_address', 'Address'),
			),
		));

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Login form
	 */
	public function getLoginForm($complete_form = false)
	{
		$this->helper->submit_action = 'submitPSPHipayConfiguration';
		$this->helper->tpl_vars['fields_value'] = $this->getLoginFormValues($complete_form);

		$form = array('form' => array(
			'legend' => $this->generateLegend('Login', 'icon-user'),
			'input' => array(
				$this->generateInputEmail('install_user_email', 'Email', 'Please, enter your email address in the field bellow'),
			),
			'buttons' => array(
				$this->generateSubmitButton('Login / Subscribe', array(
					'name' => 'submitLogin',
					'icon' => 'process-icon-update',
				)),
			),
		));

		if ($complete_form == 'new_account')
		{
			$form['form']['input'][] = $this->generateInputText('install_user_firstname', 'Firstname', array('class' => 'fixed-width-xxl'));
			$form['form']['input'][] = $this->generateInputText('install_user_lastname', 'Lastname', array('class' => 'fixed-width-xxl'));
		}
		elseif ($complete_form == 'existing_account')
		{
			$form['form']['input'][] = $this->generateInputText('install_ws_login', 'WS Login', array('class' => 'fixed-width-xxl'));
			$form['form']['input'][] = $this->generateInputText('install_ws_password', 'WS Password', array('class' => 'fixed-width-xxl'));

			$form['form']['buttons'][] = $this->generateSubmitButton('Reset', array(
				'class' => 'pull-left',
				'name' => 'submitReset',
				'icon' => 'process-icon-eraser',
			));
		}

		return $this->helper->generateForm(array($form));
	}

	/**
	 * Settings form
	 */
	public function getSettingsForm($user_account)
	{
		$this->helper->submit_action = 'submitPSPHipayConfiguration';
		$this->helper->tpl_vars['fields_value'] = $this->getSettingsFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('main_account_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('main_account_email', 'Email'),
				$this->generateInputFree('main_account_shop_name', 'Shop name'),
				$this->generateInputFree('main_account_number', 'Account number'),
				$this->generateInputFree('main_account_balance', 'Balance'),

				$this->generateInputFree('sub_accounts_details', false, array('col' => 12, 'offset' => 0)),
				$this->generateInputFree('sub_accounts_values', false),
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
	public function getTestForm()
	{
		$this->helper->submit_action = 'submitPSPHipayConfiguration';
		$this->helper->tpl_vars['fields_value'] = $this->getTestFormValues();

		$form = array('form' => array(
			'input' => array(
				$this->generateSwitchButton('test_account_mode', 'Test mode'),
			),
			'buttons' => array(
				$this->generateSubmitButton('Save', array(
					'name' => 'submitTestMode',
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
		$this->helper->submit_action = 'submitPSPHipayConfiguration';
		$this->helper->tpl_vars['fields_value'] = $this->getTransactionsFormValues($user_account);

		$form = array('form' => array(
			'input' => array(
				$this->generateInputFree('transactions_account_statement', 'Account statement'),
				$this->generateInputFree('transactions_current_date', 'Date'),
				$this->generateInputFree('transactions_dates_range', 'Range'),
				$this->generateInputFree('transactions_details', 'Transactions', array('col' => 9, 'offset' => 0)),
			),
			'buttons' => array(
				$this->generateSubmitButton('Refresh', array(
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
		return array(
			'customers_service_contact_info' =>  '<h4 class="form-control-static">'.$this->module->l('You want to contact the Hipay customers\' service?').'</h4>',
			'customers_service_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_EMAIL').'</strong></p>',
			'customers_service_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
			'customers_service_account_number' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID').'</strong></p>',

			'customers_service_contact_details' =>  '<h4 class="form-control-static">'.$this->module->l('You want to contact the Hipay customers\' service?').'</h4>',
			'customers_service_contact_form' => '<p class="form-control-static"><a href="https://test-www.hipaywallet.com/info/contact" target="_blank">'.$this->module->l('Contact the customers\' service').'</a></strong></p>',
			'customers_service_phone_number' => '<p class="form-control-static">'.$this->module->l('XXXXXXXXXXXXX').'</strong></p>',
			'customers_service_address' => '<p class="form-control-static">'.$this->module->l('XXXXXXXXXXXXX').'</strong></p>',
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
		);

		if ($complete_form == 'new_account')
		{
			$values['install_user_firstname'] = Tools::getValue('install_user_firstname');
			$values['install_user_lastname'] = Tools::getValue('install_user_lastname');
		}
		elseif ($complete_form == 'existing_account')
		{
			$values['install_ws_login'] = Tools::getValue('install_ws_login', Configuration::get('PSP_HIPAY_WS_LOGIN'));
			$values['install_ws_password'] = Tools::getValue('install_ws_password', Configuration::get('PSP_HIPAY_WS_PASSWORD'));
		}

		return $values;
	}

	/**
	 * Settings form values
	 */
	public function getSettingsFormValues($user_account)
	{
		$accounts = $user_account->getBalances();
		$account = $user_account->getMainAccountBalance($accounts);

		$main_account_values = array(
			'main_account_details' => '<h4 class="form-control-static">'.$this->module->l('Main account').'</h4>',
			'main_account_email' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_EMAIL').'</strong></p>',
			'main_account_shop_name' => '<p class="form-control-static"><strong>'.Configuration::get('PS_SHOP_NAME').'</strong></p>',
			'main_account_number' => '<p class="form-control-static"><strong>'.Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID').'</strong></p>',
			'main_account_balance' => '<p class="form-control-static"><strong>'.(int)$account->balance.' '.(string)$account->currency.'</strong></p>',
		);

		$details = null;

		foreach ($accounts->balances->item as $sub_account)
			if ($sub_account->userAccountId != $account->userAccountId)
				$details .= '<tr>
					<td>'.$sub_account->userAccountId.'</td>
					<td>'.(int)$sub_account->balance.' '.(string)$sub_account->currency.'</td>
				</tr>';

		$sub_accounts_values = array(
			'sub_accounts_details' => '<h4 class="form-control-static">'.$this->module->l('Sub-accounts').'</h4>',
			'sub_accounts_values' => '<table class="form-control-static table table-bordered table-hover table-striped">
			<thead>
				<tr>
					<th><strong>'.$this->module->l('Account ID').'</strong></th>
					<th><strong>'.$this->module->l('Balance').'</strong></th>
				</tr>
			</thead>
			<tbody>'.$details.'</tbody>
			</table>',
		);

		return array_merge($main_account_values, $sub_accounts_values);
	}

	/**
	 * Settings form values
	 */
	public function getTestFormValues()
	{
		return array(
			'test_account_mode' => Tools::getValue('test_account_mode', Configuration::get('PSP_HIPAY_SANDBOX_MODE')),
		);
	}

	/**
	 * Transactions form values
	 */
	public function getTransactionsFormValues($user_account)
	{
		$calendar_helper = new HelperCalendar();

		$employee = $this->context->employee;

		$default_date_from = isset($employee->psp_hipay_date_from) ? $employee->psp_hipay_date_from : date('Y-m-dT').'00:00:00';
		$default_date_to = isset($employee->psp_hipay_date_to) ? $employee->psp_hipay_date_to : date('Y-m-dT').'23:59:59';

		$calendar_helper->setDateFrom(Tools::getValue('date_from', $default_date_from));
		$calendar_helper->setDateTo(Tools::getValue('date_to', $default_date_to));

		$template_path = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/transactions.tpl';

		$this->context->smarty->assign(array(
			'date_from' => $default_date_from,
			'date_to' => $default_date_to,
			'transactions_dates_form' => $calendar_helper->generate(),
		));

		$transactions_values = array(
			'transactions_account_statement' => '<p class="form-control-static">N&deg;'.Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID').'</p>',
			'transactions_current_date' => '<p class="form-control-static">'.date('Y-m-d H:i:s').'</p>',
			'transactions_dates_range' => $this->context->smarty->fetch($template_path),
		);

		$details = null;
		$transactions = $user_account->getTransactions();

		if ((is_array($transactions) == true) && (count($transactions) > 0))
		{
			foreach ($transactions as $transaction)
				$details .= '<tr>
					<td>'.$transaction->userAccountId.'</td>
					<td>'.(int)$transaction->balance.' '.(string)$transaction->currency.'</td>
				</tr>';
		}

		$transactions_values['transactions_details'] = '<table class="form-control-static table table-bordered table-hover table-striped">
			<thead>
				<tr>
					<th><strong>'.$this->module->l('Account ID').'</strong></th>
					<th><strong>'.$this->module->l('Balance').'</strong></th>
				</tr>
			</thead>
			<tbody>'.$details.'</tbody>
		</table>';

		return $transactions_values;
	}

}

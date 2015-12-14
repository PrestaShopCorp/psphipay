<?php
/**
* 2007-2015 PrestaShop
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
* @copyright 2007-2015 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
    exit;

require_once(dirname(__FILE__).'/classes/forms/PSPHipayForm.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayUserAccount.php');
require_once(dirname(__FILE__).'/classes/webservice/HipayWS.php');

class PSPHipay extends PaymentModule
{
    protected $config_form = false;

    protected $_errors = [];
    protected $_successes = [];
    protected $_warnings = [];

    public $currencies_titles = [];
    public $limited_countries = [];
    public $limited_currencies = [];

    const PAYMENT_FEED_BASE_LINK = 'https://www.prestashop.com/download/pdf/pspayments/Fees_PSpayments_';

    public static $available_rates_links = [
        'EN', 'FR', 'ES', 'DE',
        'IT', 'NL', 'PL', 'PT'
    ];

    public static $refund_available = ['CB', 'VISA', 'MASTERCARD'];

    public function __construct()
    {
        $this->name = 'psphipay';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.0';
        $this->module_key = '';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->author = 'PrestaShop';

        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Payments by HiPay');
        $this->description = $this->l('Accept payments by credit card and other local methods with PrestaShop\'s official payment solution. Very competitive rates, no configuration required!');

        // Compliancy
        $this->limited_countries = [
            'AT', 'BE', 'CH', 'CY', 'CZ', 'DE', 'DK',
            'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HK',
            'HR', 'HU', 'IE', 'IT', 'LI', 'LT', 'LU',
            'LV', 'MC', 'MT', 'NL', 'NO', 'PL', 'PT',
            'RO', 'RU', 'SE', 'SI', 'SK', 'TR'
        ];

        $this->currencies_titles = [
            'AUD' => $this->l('Australian dollar'),
            'CAD' => $this->l('Canadian dollar'),
            'CHF' => $this->l('Swiss franc'),
            'EUR' => $this->l('Euro'),
            'GBP' => $this->l('Pound sterling'),
            'PLN' => $this->l('Polish złoty'),
            'SEK' => $this->l('Swedish krona'),
            'USD' => $this->l('United States dollar'),
        ];

        $this->limited_currencies = array_keys($this->currencies_titles);

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];

        if (!Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID') || !Configuration::get('PSP_HIPAY_WEBSITE_ID') ||
            !Configuration::get('PSP_HIPAY_WS_LOGIN') || !Configuration::get('PSP_HIPAY_WS_PASSWORD'))
            $this->warning = $this->l('Please, do not forget to configure your module');
    }

    public function install()
    {
        if (extension_loaded('soap') == false) {
            $this->_errors[] = $this->l('You have to enable the SOAP extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module cannot work in your country');
            return false;
        }

        return parent::install() &&
            $this->setCurrencies() &&
            $this->installAdminTab() &&
            $this->addPSPHiPayOrderStates() &&
            $this->registerHook('header') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentTop') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminOrderLeft');
    }

    public function uninstall()
    {
        return $this->uninstallAdminTab() &&
            parent::uninstall();

    }

    public function installAdminTab()
    {
        $class_name = 'AdminPSPHiPayRefund';

        $tab = new Tab();

        $tab->active = 1;
        $tab->module = $this->name;
        $tab->class_name = $class_name;
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminAdmin');

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->name;
        }

        return $tab->add();
    }

    public function uninstallAdminTab()
    {
        $class_name = 'AdminPSPHiPayRefund';

        $id_tab = (int)Tab::getIdFromClassName($class_name);

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return false;
    }

    public function addPSPHiPayOrderStates()
    {
        $waiting_state_config   = 'PSP_HIPAY_OS_WAITING';
        $waiting_state_color    = '#4169E1';
        $waiting_state_names    = [];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $waiting_state_names[(int)$language['id_lang']] = 'En attente d\'autorisation';
            } else {
                $waiting_state_names[(int)$language['id_lang']] = 'Waiting for authorization';
            }
        }

        $this->addOrderState($waiting_state_config, $waiting_state_color, $waiting_state_names);

        $partial_state_config   = 'PSP_HIPAY_OS_PARTIALLY_REFUNDED';
        $partial_state_color    = '#EC2E15';
        $partial_state_names    = [];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $partial_state_names[(int)$language['id_lang']] = 'Partiellement remboursé';
            } else {
                $partial_state_names[(int)$language['id_lang']] = 'Partially refunded';
            }
        }

        $this->addOrderState($partial_state_config, $partial_state_color, $partial_state_names);

        $total_state_config   = 'PSP_HIPAY_OS_TOTALLY_REFUNDED';
        $total_state_color    = '#EC2E15';
        $total_state_names    = [];

        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) == 'fr') {
                $total_state_names[(int)$language['id_lang']] = 'Totalement remboursé';
            } else {
                $total_state_names[(int)$language['id_lang']] = 'Totally refunded';
            }
        }

        $this->addOrderState($total_state_config, $total_state_color, $total_state_names);
    }

    /**
     * Load configuration page
     * @return string
     */
    public function getContent()
    {
        $form = new PSPHipayForm($this);
        $user_account = new HipayUserAccount($this);

        $this->postProcess($user_account);

        // Generate configuration forms
        if (Configuration::get('PSP_HIPAY_USER_EMAIL')) {
            $amount_limit = 1000;

            $accounts = $user_account->getBalances();
            $account = $user_account->getMainAccountBalance($accounts);

            if (isset($account->balance)) {
                $balance_warning = (int)$account->balance > $amount_limit;
            } else {
                $balance_warning = false;
            }

            $this->context->smarty->assign(array(
                'is_logged' => true,
                'amount_limit' => Tools::displayPrice($amount_limit, $this->context->currency),
                'balance_warning' => $balance_warning,
                'sandbox_form' => $form->getSandboxForm(),
                'services_form' => $form->getCustomersServiceForm($user_account),
                'settings_form' => $form->getSettingsForm($user_account),
                'transactions_form' => $form->getTransactionsForm($user_account),
            ));

            if (Configuration::get('PSP_HIPAY_WELCOME_MESSAGE_SHOWN') == false) {
                Configuration::updateValue('PSP_HIPAY_WELCOME_MESSAGE_SHOWN', true);
                $this->context->smarty->assign('welcome_message', true);
            }
        } else {
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
            'localized_rates_pdf_link' => $this->getLocalizedRatesPDFLink()
        ));

        return $this->context->smarty->fetch($configuration);
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

    public function hookDisplayAdminOrderLeft($params)
    {
        $order = new Order((int)$params['id_order']);

        if ((! $order->id) || ($order->module != $this->name)) {
            return false;
        }

        $details = $this->getAdminOrderRefundBlockDetails($order);

        if (! $this->isRefundAvailable($details)) {
            return $this->display(__FILE__, 'views/templates/hook/cannot_be_refunded.tpl');
        }

        $min_date = date('Y-m-d H:i:s', strtotime($order->date_add . ' +1 day'));

        if ($min_date > date('Y-m-d H:i:s')) {
            return $this->display(__FILE__, 'views/templates/hook/cannot_refund_yet.tpl');
        }

        $this->context->controller->addJS($this->_path.'views/js/order.js');
        $this->context->controller->addCSS($this->_path.'views/css/refund.css');

        return $this->display(__FILE__, 'views/templates/hook/refund.tpl');

        return ;
    }

    public function hookHeader()
    {
        return $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    /**
     * Display a payment button
     * @param array $params
     * @return string
     */
    public function hookPayment($params)
    {
        if (Configuration::get('PSP_HIPAY_USER_ACCOUNT_ID')) {
            $currency_id = $params['cart']->id_currency;
            $currency = new Currency((int)$currency_id);

            if (in_array($currency->iso_code, $this->limited_currencies) == false) {
                return false;
            }

            $this->smarty->assign(array(
                'domain' => Tools::getShopDomainSSL(true),
                'module_dir' => $this->_path,
                'payment_button' => $this->getPaymentButton(),
            ));

            $this->smarty->assign('psphipay_prod', !(bool)Configuration::get('PSP_HIPAY_SANDBOX_MODE'));

            return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
        }

        return false;
    }

    /**
     * Display the payment confirmation page
     * @param array $params
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookPaymentTop()
    {
        $this->context->controller->addJS($this->_path.'views/js/front.js');
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

    protected function postProcess($user_account)
    {
        $this->context->smarty->assign('sandbox', Configuration::get('PSP_HIPAY_SANDBOX_MODE'));

        if (Tools::isSubmit('submitSandboxMode')) {
            $this->context->smarty->assign('active_tab', 'sandbox');
            return $this->switchSandboxMode();
        } elseif (Tools::isSubmit('submitReset')) {
            return $this->clearAccountData();
        } elseif (Tools::isSubmit('submitLogin')) {
            return $this->login($user_account);
        } elseif (Tools::isSubmit('submitDateRange')) {
            $this->context->smarty->assign('active_tab', 'transactions');
            return $this->saveTransactionsDateRange();
        }
    }

    public function getLocalizedRatesPDFLink()
    {
        $shop_iso_country_id = Configuration::get('PS_COUNTRY_DEFAULT');
        $shop_iso_country = Country::getIsoById((int)$shop_iso_country_id);
        $shop_iso_country = Tools::strtoupper($shop_iso_country);

        if (!$shop_iso_country || !in_array($shop_iso_country, PSPHipay::$available_rates_links)) {
            $shop_iso_country = 'EN';
        }

        $localized_link = PSPHipay::PAYMENT_FEED_BASE_LINK.$shop_iso_country.'.pdf';

        return $localized_link;
    }

    public function getAdminOrderRefundBlockDetails($order)
    {
        $currency       = new Currency($order->id_currency);
        $messages       = Message::getMessagesByOrderId($order->id, true);
        $message        = array_pop($messages);
        $details        = json_decode($message['message']);
        $id_transaction = $this->getTransactionId($details);

        $form = new PSPHipayForm($this);

        $params = http_build_query([
            'id_order'          => $order->id,
            'id_transaction'    => $id_transaction,
        ]);

        $this->smarty->assign([
            'currency'          => $currency,
            'details'           => $details,
            'order'             => $order,
            'transaction_id'    => $id_transaction,
            'refund_link'       => $this->context->link->getAdminLink('AdminPSPHiPayRefund&' . $params, true),
        ]);

        return $details;
    }

    /**
     * Add waiting order state in database
     * If it does not already exists
     * @return boolean
     */
    protected function addOrderState($config, $color, $names)
    {
        if ((bool)Configuration::get($config) == true) {
            return true;
        }

        $order_state = new OrderState();
        $order_state->name = $names;

        $order_state->color         = $color;
        $order_state->hidden        = false;
        $order_state->send_email    = false;
        $order_state->delivery      = false;
        $order_state->logable       = false;
        $order_state->invoice       = false;

        if ($order_state->add() == true) {
            Configuration::updateValue($config, $order_state->id);
            @copy($this->local_path.'logo.gif', _PS_ORDER_STATE_IMG_DIR_.(int)$order_state->id.'.gif');

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
        Configuration::deleteByName('PSP_HIPAY_USER_EMAIL');
        Configuration::deleteByName('PSP_HIPAY_SANDBOX_MODE');

        // Live mode
        Configuration::deleteByName('PSP_HIPAY_USER_ACCOUNT_ID');
        Configuration::deleteByName('PSP_HIPAY_WEBSITE_ID');
        Configuration::deleteByName('PSP_HIPAY_WS_LOGIN');
        Configuration::deleteByName('PSP_HIPAY_WS_PASSWORD');

        // Sandbox mode
        Configuration::deleteByName('PSP_HIPAY_SANDBOX_USER_ACCOUNT_ID');
        Configuration::deleteByName('PSP_HIPAY_SANDBOX_WEBSITE_ID');
        Configuration::deleteByName('PSP_HIPAY_SANDBOX_WS_LOGIN');
        Configuration::deleteByName('PSP_HIPAY_SANDBOX_WS_PASSWORD');

        return true;
    }

    protected function createMerchantAccount($email, $first_name, $last_name)
    {
        $is_valid_name  = (bool)Validate::isName($first_name);
        $is_valid_name  &= (bool)Validate::isName($last_name);

        if ($is_valid_name) {
            $user_account = new HipayUserAccount($this);

            // Live mode
            if ($user_account->isEmailAvailable($email, false) == true) {
                $user_account->createAccount($email, $first_name, $last_name, false);
            }

            // Sandbox mode
            if ($user_account->isEmailAvailable($email, true) == true) {
                $user_account->createAccount($email, $first_name, $last_name, true);
            }
        }
    }

    /**
     * Get the appropriate payment button's image
     * @return string
     */
    protected function getPaymentButton()
    {
        $id_address = $this->context->cart->id_address_invoice;

        if ($id_address) {
            $address = new Address((int)$id_address);
            $country = new Country((int)$address->id_country);
            $iso_code = Tools::strtolower($country->iso_code);

            if (file_exists(dirname(__FILE__).'/views/img/payment_buttons/'.$iso_code.'.png')) {
                return $this->_path.'views/img/payment_buttons/'.$iso_code.'.png';
            }
        }

        return $this->_path.'views/img/payment_buttons/default.png';
    }

    protected function getTransactionId($details)
    {
        foreach ($details as $key => $value) {
            $tmp_key = strtolower(str_replace(' ', false, $key));

            if (in_array($tmp_key, ['transactionid', 'idtransaction'])) {
                return $value;
            }
        }

        return false;
    }

    protected function isRefundAvailable($details)
    {
        $refund_available   = array_uintersect((array)$details, static::$refund_available, function ($a, $b) {
            return (int)(Tools::strtolower($a) !== Tools::strtolower($b));
        });

        return ! empty($refund_available);
    }

    protected function login($user_account)
    {
        $email = Tools::getValue('install_user_email');
        $is_email = (bool)Validate::isEmail($email);

        $first_name = Tools::getValue('install_user_first_name');
        $last_name = Tools::getValue('install_user_last_name');

        $website_id = Tools::getValue('install_website_id');
        $ws_login = Tools::getValue('install_ws_login');
        $ws_password = Tools::getValue('install_ws_password');

        if ($is_email == false) {
            return false;
        } elseif ($first_name && $last_name) {
            return $this->createMerchantAccount($email, $first_name, $last_name);
        } elseif ($website_id && $ws_login && $ws_password) {
            $is_valid_website_id = (bool)Validate::isInt($website_id);
            $is_valid_login = (bool)Validate::isMd5($ws_login);
            $is_valid_password = (bool)Validate::isMd5($ws_password);

            Configuration::updateValue('PSP_HIPAY_SANDBOX_MODE', false);

            if ($is_valid_website_id && $is_valid_login && $is_valid_password) {
                return $this->registerExistingAccount($email, $website_id, $ws_login, $ws_password);
            }

            $this->_warnings[] = $this->l('The credentials you have entered are invalid. Please try again.');
            $this->_warnings[] = $this->l('If you have lost these details, please log in to your HiPay account to retrieve it');

            return false;
        }


        if ($user_account->isEmailAvailable($email)) {
            // Email available
            $this->_warnings[] = $this->l('To create your PrestaShop Payments by Hipay account, please enter your name and click on Subscribe');
        } else {
            // Email not available
            $this->_warnings[] = $this->l('You already have an account, please fill the fields below');
        }

        return true;
    }

    protected function registerExistingAccount($email, $website_id, $ws_login, $ws_password, $sandbox = false)
    {
        $prefix = $sandbox ? 'PSP_HIPAY_SANDBOX' : 'PSP_HIPAY';

        $details = [
            'PSP_HIPAY_USER_EMAIL' => $email,
            $prefix.'_WEBSITE_ID' => $website_id,
            $prefix.'_WS_LOGIN' => $ws_login,
            $prefix.'_WS_PASSWORD' => $ws_password,
        ];

        $this->saveConfigurationDetails($details);

        $user_account = new HipayUserAccount($this);
        $account = $user_account->getAccountInfos();

        if (isset($account->code) && ($account->code == 0)) {
            Configuration::updateValue($prefix.'_USER_ACCOUNT_ID', $account->userAccountId);
        } else {
            $this->_errors[] = $this->l('Authentication failed!');
            $this->clearAccountData();

            return false;
        }

        return true;
    }

    protected function saveConfigurationDetails($details)
    {
        foreach ($details as $name => $value) {
            Configuration::updateValue($name, $value);
        }
    }

    protected function saveTransactionsDateRange()
    {
        if (Tools::isSubmit('date_from') && Tools::isSubmit('date_to')) {
            $this->context->cookie->psp_hipay_date_from = Tools::getValue('date_from');
            $this->context->cookie->psp_hipay_date_to = Tools::getValue('date_to');
        }
    }

    /**
     * Store the currencies list the module should work with
     * @return boolean
     */
    protected function setCurrencies()
    {
        $shops = Shop::getShops(true, null, true);

        foreach ($shops as $shop) {
            $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'module_currency` (`id_module`, `id_shop`, `id_currency`)
                    SELECT '.(int)$this->id.', "'.(int)$shop.'", `id_currency`
                    FROM `'._DB_PREFIX_.'currency`
                    WHERE `deleted` = \'0\' AND `iso_code` IN (\''.implode($this->limited_currencies, '\',\'').'\')';

            return (bool)Db::getInstance()->execute($sql);
        }

        return true;
    }

    protected function shouldDisplayCompleteLoginForm($user_account)
    {
        // If merchant tries to login / subscribe
        if (Tools::isSubmit('submitLogin') == true) {
            $email = Tools::getValue('install_user_email');

            if (Validate::isEmail($email)) {
                return $user_account->isEmailAvailable($email) ? 'new_account' : 'existing_account';
            }

            $this->module->_errors[] = $this->l('Invalid email address');
        }

        return false;
    }

    protected function switchSandboxMode()
    {
        $email = Configuration::get('PSP_HIPAY_USER_EMAIL');

        $sandbox_mode = (bool)Tools::getValue('sandbox_account_mode');
        Configuration::updateValue('PSP_HIPAY_SANDBOX_MODE', $sandbox_mode);
        $this->context->smarty->assign('sandbox', $sandbox_mode);

        if ($sandbox_mode) {
            $sandbox_website_id = Tools::getValue('sandbox_website_id');
            $sandbox_ws_login = Tools::getValue('sandbox_ws_login');
            $sandbox_ws_password = Tools::getValue('sandbox_ws_password');

            $is_valid_sandbox_website_id = (bool)Validate::isInt($sandbox_website_id);
            $is_valid_sandbox_login = (bool)Validate::isMd5($sandbox_ws_login);
            $is_valid_sandbox_password = (bool)Validate::isMd5($sandbox_ws_password);

            if ($sandbox_mode && $is_valid_sandbox_website_id && $is_valid_sandbox_login && $is_valid_sandbox_password) {
                $this->registerExistingAccount($email, $sandbox_website_id, $sandbox_ws_login, $sandbox_ws_password, $sandbox_mode);
            }
        }

        $website_id = Tools::getValue('website_id');
        $ws_login = Tools::getValue('ws_login');
        $ws_password = Tools::getValue('ws_password');

        $is_valid_website_id = (bool)Validate::isInt($website_id);
        $is_valid_login = (bool)Validate::isMd5($ws_login);
        $is_valid_password = (bool)Validate::isMd5($ws_password);

        if ($is_valid_website_id && $is_valid_login && $is_valid_password) {
            $this->registerExistingAccount($email, $website_id, $ws_login, $ws_password);
        }

        return true;
    }
}

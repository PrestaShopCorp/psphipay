<?php

class AdminPSPHiPayRefundController extends ModuleAdminController
{
    protected $ws_url			= 'https://test-ws.hipay.com/soap/refund-v2?wsdl';

    protected $ws_login			= false;
    protected $ws_password		= false;
    protected $website_id		= false;

    protected $type				= false;
    protected $id_transaction	= false;
    protected $id_order         = false;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';

        parent::__construct();

        if (!$this->module->active) {
            $this->sendErrorRequest('Invalid request.');
        }
    }

    public function init()
    {
        $result = false;
        $this->getRefundValues();
        $order = new Order($this->id_order);

        if (! $order) {
            $this->sendErrorRequest('Invalid request.');
        }

        switch ($this->type) {
            case 'partial':
                $amount	= Tools::getValue('amount');
                break;

            case 'total':
                $amount = $order->total_paid_tax_incl;
                break;

            default:
                break;
        }

        $result = $this->processRefund($order, $amount);
        
        if ($amount == 0) {
            $this->sendErrorRequest('Invalid parameters.');
        }

        if ($result->cardResult->code != 0) {
            $this->sendErrorRequest($result->cardResult->description);
        } else {
            $this->saveRefundDetails($order, $amount, $result);
            $this->sendSuccessRequest($result);
        }

        $this->sendErrorRequest('Invalid request.');
    }

    public function getRefundValues()
    {
        $prefix = Configuration::get('PSP_HIPAY_SANDBOX_MODE') ? 'PSP_HIPAY_SANDBOX_' : 'PSP_HIPAY_';

        $this->ws_login			= Configuration::get($prefix . 'WS_LOGIN');
        $this->ws_password		= Configuration::get($prefix . 'WS_PASSWORD');

        $this->website_id		= Configuration::get($prefix . 'WEBSITE_ID');


        $this->id_order			= Tools::getValue('id_order');
        $this->id_transaction	= Tools::getValue('id_transaction');

        $this->type				= Tools::getValue('type');

        if ((! $this->id_order) || (! $this->id_transaction)) {
            $this->sendErrorRequest('Invalid parameters.');
        }
    }

    protected function processRefund($order, $amount)
    {
        $options = [
            'compression'	=> SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'cache_wsdl'	=> WSDL_CACHE_NONE,
            'soap_version'	=> SOAP_1_1,
            'encoding'		=> 'UTF-8'
         ];

        $client = new SoapClient($this->ws_url, $options);

        $result = $client->card([
            'parameters' => [
                'wsLogin'				=> $this->ws_login,
                'wsPassword'			=> $this->ws_password,
                'websiteId'				=> $this->website_id,
                'transactionPublicId'	=> $this->id_transaction,
                'amount'				=> $amount,
            ],
        ]);

        return $result;
    }

    private function saveRefundDetails($order, $amount, $result)
    {
        $currency = new Currency($order->id_currency);
        $refunded = $amount.$currency->iso_code;

        switch ($this->type) {
            case 'partial':
                $id_order_state	= (int)Configuration::get('PSP_HIPAY_OS_PARTIALLY_REFUNDED');
                
                $details = $this->module->l('This order has been partially refunded');
				$details .= ': '.Tools::displayNumber($order->total_paid_tax_incl, $currency).' ('.$currency->iso_code.').';
                break;

            case 'total':
                $id_order_state	= (int)Configuration::get('PSP_HIPAY_OS_TOTALLY_REFUNDED');
                $details = $this->module->l('This order has been totally refunded');
                break;

            default:
                break;
        }
        
        $details .= ' '.json_encode($result->cardResult);

        $this->addRefundMessage($order, $details);

        $order_history = new OrderHistory();
        $order_history->id_order = (int)$order->id;
        $order_history->id_employee = (int)$this->context->employee->id;
        $order_history->id_order_state = $id_order_state;
        $order_history->addWithemail();
    }

    protected function addRefundMessage($order, $details)
    {
	    $message = new Message();
		
        $message->message = $details;
        $message->id_order = (int)$order->id;
        $message->private = 1;

        $status = $message->add();
    }

    protected function sendSuccessRequest($result)
    {
        $output = json_encode($result->cardResult);

        die($output);
    }

    protected function sendErrorRequest($response)
    {
        http_response_code(406);

        $output = json_encode($response);

        die($output);
    }
}

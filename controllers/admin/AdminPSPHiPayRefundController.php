<?php

class AdminPSPHiPayRefundController extends ModuleAdminController
{
    protected $amount			= false;
    protected $sandbox			= false;

    protected $id_transaction	= false;
    protected $id_order         = false;

    public function __construct()
    {
        parent::__construct();

        if (!$this->module->active) {
            $this->sendErrorRequest('Invalid request.');
        }

        require_once(PS_MODULE_DIR.$this->module->name.'/classes/webservice/HipayRefund.php');
    }

    public function init()
    {
        $this->getRefundValues();

        if ($this->amount == 0) {
            $this->sendErrorRequest('Invalid parameters.');
        }

        $params = [
            'amount'                => $this->amount,
            'transactionPublicId'   => $this->id_transaction,
        ];

        $refund = new HipayRefund();
        $result = $refund->process($params, $this->sandbox);

        if ($result->cardResult->code != 0) {
            $this->sendErrorRequest($result->cardResult->description);
        } else {
            $this->saveRefundDetails($order, $this->amount, $result);
            $this->sendSuccessRequest($result);
        }

        $this->sendErrorRequest('Invalid request.');
    }

    public function getRefundValues()
    {
        $this->sandbox			= Tools::getValue('sandbox');

        $this->id_order			= Tools::getValue('id_order');
        $this->id_transaction	= Tools::getValue('id_transaction');

        $order = new Order($this->id_order);

        if ($order->id && $this->id_transaction) {
            $this->amount = Tools::getValue('amount', $order->total_paid_tax_incl);

            return true;
        }

        $this->sendErrorRequest('Invalid parameters.');

        return false;
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

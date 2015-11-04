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

class PSPHipayRedirectModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $currency = $this->context->currency;

        if ($this->module->isSupportedCurrency($currency->iso_code) == false) {
            return $this->displayError('The currency is not supported');
        }

        $this->generatePayment();
    }

    protected function generatePayment()
    {
        require_once(dirname(__FILE__).'/../../classes/webservice/HipayPayment.php');

        $results = null;
        $payment = new HipayPayment($this->module);

        if ($payment->generate($results) == false) {
            $description = $results->generateResult->description;
            $this->displayError('An error occurred while getting transaction informations', $description);
        }
    }

    protected function displayError($message, $description = false)
    {
        $this->context->smarty->assign('path', '
            <a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Order').'</a>
            <span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

        $this->errors[] = $this->module->l($message);

        if ($description != false) {
            $this->errors[] = $description;
        }

        return $this->setTemplate('error.tpl');
    }
}

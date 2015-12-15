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

class PSPHipayConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false)) {
            return $this->displayConfirmationError();
        }
        
        $failure	= Tools::getIsset('failure');

        $cart_id	= Tools::getValue('cart_id');
        $secure_key	= Tools::getValue('secure_key');
        
        $cart = new Cart((int)$cart_id);
        
        if ($failure) {
	        return $this->displayConfirmationError();
        } elseif ($cart->id) {
	        return $this->displayConfirmation($cart, $secure_key);
        } else {
	        return $this->waitForConfirmation($cart_id, $secure_key);
        }
    }
    
    protected function displayConfirmation($cart, $secure_key)
    {
        
        $customer = new Customer((int)$cart->id_customer);
        $order_id = Order::getOrderByCartId((int)$cart->id);

        if (($order_id) && ($secure_key == $customer->secure_key)) {
	        $params = http_build_query([
		        'id_cart'	=> $cart->id,
		        'id_module'	=> $this->module->id,
		        'id_order'	=> $order_id,
		        'key'		=> $customer->secure_key,
	        ]);
	        
            return Tools::redirect('index.php?controller=order-confirmation&'.$params);
        }
    }
    
    protected function displayConfirmationError()
    {
        $this->errors[] = $this->module->l('An error occurred. Please contact the merchant for more details.');
        
        return $this->setTemplate('error.tpl');
    }
    
    protected function waitForConfirmation($cart_id, $secure_key)
    {
		$params = ['cart_id' => $cart_id, 'secure_key' => $secure_key];
		
	    $this->context->controller->addJS(_MODULE_DIR_.'/'.$this->module->name.'/views/js/confirmation.js');
	    
        $this->context->smarty->assign([
        	'img_dir' 	=> _MODULE_DIR_.'/'.$this->module->name.'/views/img',
        	'ajax_url'	=> $this->context->link->getModuleLink('psphipay', 'check', $params, true),
        ]);
        
        return $this->setTemplate('waiting_validation.tpl');
    }
}

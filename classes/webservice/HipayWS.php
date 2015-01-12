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

if (!defined('_PS_VERSION_'))
	exit;

abstract class HipayWS
{
	protected $context = false;
	protected $client = false;
	protected $client_url = false;
	protected $module = false;

	protected $prestashop_api = 'http://payments.prestashop.com/psphipay';

	protected $ws_url = 'https://ws.hipay.com';
	protected $ws_test_url = 'https://test-ws.hipay.com';

	public function __construct($module_instance)
	{
		$this->context = Context::getContext();
		$this->module = $module_instance;
	}

	public function getWsId()
	{
		return $this->ws_id;
	}

	public function getWsLogin()
	{
		return $this->ws_login;
	}

	public function getWsPassword()
	{
		return $this->ws_password;
	}

	public function getWsMerchantGroup()
	{
		return $this->ws_merchant_group;
	}

	public function getWsURL()
	{
		return $this->ws_url;
	}

	public function getWsClientURL()
	{
		if ((bool)Configuration::get('PSP_HIPAY_SANDBOX_MODE') == false)
			return $this->ws_url.$this->client_url.'?wsdl';

		return $this->ws_test_url.$this->client_url.'?wsdl';
	}

	public function getClient()
	{
		try
		{
			$ws_options = array(
				'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
				'cache_wsdl' => WSDL_CACHE_NONE,
				'connection_timeout' => 20,
				'soap_version' => SOAP_1_1,
				'encoding' => 'UTF-8'
			);

			return new SoapClient($this->getWsClientURL(), $ws_options);
		}
		catch (SoapFault $exception)
		{
			return false;
		}
	}

	public function executeQuery($function, $params = array())
	{
		try
		{
			if ((isset($this->client) == false) || ($this->client === false))
				$this->client = $this->getClient();
			
			if ($this->client == false)
			{
				$this->module->_errors[] = $this->module->l('An error occurred while trying to contact the web service');
				return false;
			}

			if (Configuration::get('PSP_HIPAY_SANDBOX_MODE'))
			{
				$params = $params + array(
					'websiteId' => Configuration::get('PSP_HIPAY_SANDBOX_WEBSITE_ID'),
					'wsLogin' => Configuration::get('PSP_HIPAY_SANDBOX_WS_LOGIN'),
					'wsPassword' => Configuration::get('PSP_HIPAY_SANDBOX_WS_PASSWORD'),
				);
			}
			else
			{
				$params = $params + array(
					'websiteId' => Configuration::get('PSP_HIPAY_WEBSITE_ID'),
					'wsLogin' => Configuration::get('PSP_HIPAY_WS_LOGIN'),
					'wsPassword' => Configuration::get('PSP_HIPAY_WS_PASSWORD'),
				);
			}

			$output = $this->client->__call($function, array(array('parameters' => $params)));

			unset($this->client);

			return $output;
		}
		catch (Exception $exception)
		{
			$this->module->_errors[] = $this->module->l('An error occurred while trying to contact the web service');
			return false;
		}
	}

	public function prestaShopWebservice($method, $data)
	{
		$options = array('http' => array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => http_build_query($data)
		));

		$context  = stream_context_create($options);
		$result = Tools::file_get_contents($this->prestashop_api.$method, false, $context);
		$values = Tools::jsonDecode($result);
		
		if (isset($values->data))
			return $values->data;
		
		return false;
	}
}

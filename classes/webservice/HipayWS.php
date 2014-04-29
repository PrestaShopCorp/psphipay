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

abstract class HipayWS
{
	protected $client = false;
	protected $client_url = false;

	protected $ws_id = false;
	protected $ws_url = false;
	protected $ws_login = false;
	protected $ws_password = false;
	protected $ws_merchant_group = false;

	public function __construct()
	{
		$this->context = Context::getContext();

		$this->init();
	}

	public function init()
	{
		$live_mode = (bool)Configuration::get('PSP_HIPAY_LIVE_MODE');

		if ($live_mode === true)
		{
			$this->ws_id = '19853';
			$this->ws_url = 'https://ws.hipay.com';
			$this->ws_login = 'f4908a6ab46c717f03e91a8b59ce7dd6';
			$this->ws_password = '65f4d8c1304ef10bbd056649def4ab9b';
			$this->ws_merchant_group = 5;
		}
		else
		{
			$this->ws_id = '6979';
			$this->ws_url = 'https://test-ws.hipay.com';
			$this->ws_login = 'd2b4aa9077d3fe036a26317b4284f86e';
			$this->ws_password = '9ac116a24fb82b12ad353c35922e8797';
			$this->ws_merchant_group = 4;
		}

		$this->client = $this->getClient();
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
		return $this->ws_url.$this->client_url.'?wsdl';
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

	public function doQuery($function, $params = array())
	{
		$cache_classes = array('HipayBusiness', 'HipayTopic', 'HipayUserAccount');
		$need_cache = in_array(get_class($this), $cache_classes) == true;

		if ($need_cache == true)
		{
			$cache_key = get_class($this).$function;

			if (Cache::getInstance()->exists($cache_key) == true)
			{
				$value = Cache::getInstance()->get($cache_key);
				if (empty($value) == false)
					return $value;
			}
				
		}
		
		try
		{
			$params = $params + array(
				'websiteId' => $this->getWsId(),
				'wsLogin' => $this->getWsLogin(),
				'wsPassword' => $this->getWsPassword()
			);
			
			$result = $this->client->__call($function, array(array('parameters' => $params)));
			
			if ($need_cache == true)
				Cache::getInstance()->set($cache_key, $result, 3600);
			
			return $result;
		}
		catch (Exception $exception)
		{
			return false;
		}
	}
}
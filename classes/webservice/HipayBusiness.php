<?php
/*
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayBusiness extends HipayWS
{

	protected $client_url = '/soap/business-lines-v2';

	/* Businesses list */
	protected static $businesses = array();

	/* SOAP method: codes */
	public function getBusinesses()
	{
		if (count(self::$businesses) === 0)
		{
			$locale = new HipayLocale();

			$params = array(
				'locale' => $locale->getLocale()
			);

			$results = $this->doQuery('get', $params);

			if ($results->getResult->code === 0)
				self::$businesses = (array)$results->getResult->businessLines->item;
		}

		return self::$businesses;
	}

	public function getBusiness()
	{
		$business = new StdClass();

		$business->id = $this->getBusinessId();
		$business->label = $this->getBusinessLabel();

		return $business;
	}

	public function getBusinessId()
	{
		$business_id = (int)Configuration::get('PSP_HIPAY_BUSINESS_LINE_ID');

		if ($business_id == 0)
		{
			$businesses = $this->getBusinesses();

			if ((is_array($businesses) === true) && (count($businesses) > 0))
			{
				$default_business = array_pop($businesses);
				Configuration::updateValue('PSP_HIPAY_BUSINESS_LINE_ID', $default_business->id);
				Configuration::updateValue('PSP_HIPAY_BUSINESS_LINE_LABEL', $default_business->label);
			}
		}

		return (int)Configuration::get('PSP_HIPAY_BUSINESS_LINE_ID');
	}

	public function getBusinessLabel()
	{
		$business_label = (int)Configuration::get('PSP_HIPAY_BUSINESS_LINE_LABEL');

		if ($business_label == 0)
		{
			$businesses = $this->getBusinesses();

			if ((is_array($businesses) === true) && (count($businesses) > 0))
			{
				$default_business = array_pop($businesses);
				Configuration::updateValue('PSP_HIPAY_BUSINESS_LINE_ID', $default_business->id);
				Configuration::updateValue('PSP_HIPAY_BUSINESS_LINE_LABEL', $default_business->label);
			}
		}

		return (string)Configuration::get('PSP_HIPAY_BUSINESS_LINE_LABEL');
	}

}

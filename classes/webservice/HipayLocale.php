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

class HipayLocale extends HipayWS
{

	protected $client_url = '/soap/locale';

	/* Locales list */
	protected static $locales = array();

	/* SOAP method: codes */
	public function getLocales()
	{
		if (count(self::$locales) === 0)
		{
			$results = $this->doQuery('codes');

			if ($results->codesResult->code === 0)
				self::$locales = (array)$results->codesResult->locales->item;
		}

		return self::$locales;
	}

	public function getLocale()
	{
		$locale_exists = $this->currentLocaleExists();
		
		if ($locale_exists === true)
			return $this->getCurrentLocaleCode();

		$locales = $this->getLocales();
		$country_iso_code = Tools::strtoupper($this->context->country->iso_code);

		foreach ($locales as $locale)
			if (strpos($locale->code, $country_iso_code) !== false)
				return $locale->code;

		return false;
	}

	public function getCurrentLocaleCode()
	{
		$language_iso_code = Tools::strtolower($this->context->language->iso_code);
		$country_iso_code = Tools::strtoupper($this->context->country->iso_code);

		return $language_iso_code.'_'.$country_iso_code;
	}

	public function currentLocaleExists()
	{
		$this->context = context::getContext();
		$locale_code = $this->getCurrentLocaleCode();

		$locales = $this->getLocales();

		foreach ($locales as $locale)
			if (strcmp($locale->code, $locale_code) === 0)
				return true;

		return false;
	}

}

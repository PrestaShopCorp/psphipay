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

class HipayConfigFormAlerts
{
	private static $_instance = false;

	/* Configuration form errors array */
	protected static $errors = array();
	protected static $infos = array();
	protected static $successes = array();

	protected static $psp = array();

	public static function getInstance()
	{
		if (self::$_instance === false)
		{
			self::$_instance = new self();
			self::$psp = new PSPHipay();
		}

		return self::$_instance;
	}

	/* Messages */
	public static function getFormErrors()
	{
		return self::$errors;
	}

	public static function registerFormError($error_message)
	{
		self::$errors[] = self::$psp->l($error_message);
	}

	public static function getFormInfos()
	{
		return self::$infos;
	}

	public static function registerFormInfo($info_message)
	{
		self::$infos[] = self::$psp->l($info_message);
	}

	public static function getFormSuccesses()
	{
		return self::$successes;
	}

	public static function registerFormSuccess($success_message)
	{
		self::$successes[] = self::$psp->l($success_message);
	}
}
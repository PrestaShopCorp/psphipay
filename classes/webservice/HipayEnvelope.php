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

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayEnvelope extends HipayWS
{

	protected $client_url = '/soap/user-account-v2';
	
	protected static $transactions = array();

	/* SOAP method: codes
	 * 
	 * Parameters :
	 * wsLogin
	 * wsPassword
	 * wsSubAccountLogin
	 * startDate
	 * endDate
	 * pageNumber
	 */
	public function getTransactions()
	{
		if (count(self::$transactions) === 0)
		{
			$user = new HipayUserAccount();
			Context::getContext()->employee->psp_hipay_date_from = isset(Context::getContext()->employee->psp_hipay_date_from) ? Context::getContext()->employee->psp_hipay_date_from : date('Y-m-dT').'00:00:00';
			Context::getContext()->employee->psp_hipay_date_to = isset(Context::getContext()->employee->psp_hipay_date_to) ? Context::getContext()->employee->psp_hipay_date_to : date('Y-m-dT').'23:59:59';
			
			$params = array(
				'wsSubAccountLogin' => Configuration::get('PSP_HIPAY_USER_EMAIL'),
				'startDate' => date('Y-m-dTH:i:s', strtotime(Context::getContext()->employee->psp_hipay_date_from)),
				'endDate' => date('Y-m-dTH:i:s', strtotime(Context::getContext()->employee->psp_hipay_date_to)),
				'pageNumber' => 1,
			);
			
			$results = $this->doQuery('getTransactions', $params);

			if (($results->getTransactionsResult->code === 0) && (isset($results->getTransactionsResult->transactions->item) == true))
				self::$transactions = (array)$results->getTransactionsResult->transactions->item;
		}
		
		return self::$transactions;
	}

}

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

$(document).ready(function() {
	$('.actions li a[href*="uninstall=psphipay"]').removeAttr('onclick');
	$('.actions li a[href*="module_name=psphipay&enable=0"]').removeAttr('onclick');
	$('.actions li a[href*="module_name=psphipay&disable_device="]').removeAttr('onclick');

	$(document).on('click', 'a[href*="uninstall=psphipay"], a[href*="module_name=psphipay&enable=0"], a[href*="module_name=psphipay&disable_device="]', function() {
		displayPSPHiPayPoll();
		return false;
	});
});

function displayPSPHiPayPoll() {
	return $('#psphipay-poll-modal').modal('show');
}

{*
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
*}

<button class="btn btn-default" data-toggle="modal" data-target="#transactions_dates_range" id="transactions_dates_range_button">
	<i class="icon-calendar"></i>
	{l s='From' mod='psphipay'} <strong class="text-info">{$date_from|escape:'htmlall':'UTF-8'}</strong>
	{l s='To' mod='psphipay'} <strong class="text-info">{$date_to|escape:'htmlall':'UTF-8'}</strong>
	<i class="icon-caret-down"></i>
</button>
<p class="form-control-static"><em>({l s='You can view transactions over the past 3 months only. To see more, please log into your HiPay account.' mod='psphipay'})</em></p>
<div class="modal fade" id="transactions_dates_range" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{l s='Choose a date range' mod='psphipay'}</h3>
			</div>
			<div class="modal-body">
				{$transactions_dates_form}
			</div>
		</div>
	</div>
</div>

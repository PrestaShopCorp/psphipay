{*
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
*}

<div class="form-control-static">
	{if count($transactions) > 0}

	{assign var="total_fees" value=0}
	{assign var="total_amount" value=0}

	<table class="table table-bordered" id="psp_hipay_transactions">
		<thead>
			<tr>
				<th width="30%"><strong>Transaction N&deg;</strong></th>
				<th width="20%"><strong>Operation date</strong></th>
				<th width="20%"><strong>Value date</strong></th>
				<th width="15%"><strong>Debit</strong></th>
				<th width="15%"><strong>Credit</strong></th>
			</tr>
		</thead>
		<tbody>

			{foreach from=$transactions item='transaction'}
				<tr>
					<td>{$transaction->transactionId|escape:'htmlall':'UTF-8'}</td>
					<td>{$transaction->createdAt|date_format:'%Y-%m-%d %H:%I:%S'}</td>
					<td>{$transaction->captureDate|escape:'htmlall':'UTF-8'}</td>
					<td class="text-right">{$transaction->fees|string_format:"%.2f"} {$transaction->currency|escape:'htmlall':'UTF-8'}</td>
					<td class="text-right">{$transaction->amount|string_format:"%.2f"} {$transaction->currency|escape:'htmlall':'UTF-8'}</td>

					{$total_fees = (float)($total_fees + $transaction->fees)}
					{$total_amount = (float)($total_amount + $transaction->amount)}
					{assign var="currency" value=$transaction->currency}
				</tr>
			{/foreach}
		</tbody>
	</table>

	<table class="table">
		<tbody>
			<tr>
				<td width="30%"></td>
				<td width="20%"></td>
				<td width="20%" class="text-right"><strong>{l s='Total' mod='psphipay'}</strong></td>
				<td width="15%" class="text-right">{$total_fees|string_format:"%.2f"} {$currency|escape:'htmlall':'UTF-8'}</td>
				<td width="15%" class="text-right">{$total_amount|string_format:"%.2f"} {$currency|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</tbody>
	</table>
	{else}
		<em>{l s='No operations found' mod='psphipay'}</em>
	{/if}
</div>

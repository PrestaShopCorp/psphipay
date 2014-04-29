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
					<td class="text-right"></td>
					<td class="text-right">{$transaction->amount|escape:'htmlall':'UTF-8'} {$transaction->currency|escape:'htmlall':'UTF-8'}</td>
				</tr>
				<tr>
					<td>{$transaction->transactionId|escape:'htmlall':'UTF-8'}</td>
					<td>{$transaction->createdAt|date_format:'%Y-%m-%d %H:%I:%S'}</td>
					<td>{$transaction->captureDate|escape:'htmlall':'UTF-8'}</td>
					<td class="text-right">{$transaction->fees|escape:'htmlall':'UTF-8'} {$transaction->currency|escape:'htmlall':'UTF-8'}</td>
					<td class="text-right"></td>
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
				<td width="15%" class="text-right">XXX</td>
				<td width="15%" class="text-right">XXX</td>
			</tr>
		</tbody>
	</table>
	{else}
		<em>{l s='No operations found' mod='psphipay'}</em>
	{/if}
</div>

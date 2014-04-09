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
	<table class="table table-bordered">
		<thead>
			<tr>
				<th><strong>Account ID</strong></th>
				<th><strong>Website email</strong></th>
				<th><strong>Devise</strong></th>
				<th><strong>Solde</strong></th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$sub_accounts item='sub_account'}

				<tr>
					<td>{$sub_account->userAccountId|intval}</td>
					<td>{$sub_account->websites->item->websiteEmail|escape:'htmlall':'UTF-8'}</td>
					<td>{$sub_account->currency_label|escape:'htmlall':'UTF-8'} ({$sub_account->currency|escape:'htmlall':'UTF-8'})</td>
					<td>{$sub_account->balance|number_format:2:',':''|floatval} {$sub_account->currency|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>

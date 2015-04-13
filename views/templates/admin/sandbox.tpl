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

<dl id="sandbox_info">
	<dt data-toggle="collapse" data-parent="#sandbox_info" href="#sanbox1" aria-expanded="true" aria-controls="sanbox1"><i class="icon icon-angle-right"></i> {l s='How does it work?' mod='psphipay'}</dt>
	<dd id="sanbox1" class="panel-collapse">
		<p>
			{l s='To conduct these tests, you need a payment method, which can be either:' mod='psphipay'}<br />
			<ul>
			<li>{l s='[1]A test card[/1] provided by HiPay. To get this card number, please [2]contact HiPay[/2] while mentioning your test account number and matching email address.' tags=['<strong>','<a href="mailto:prestashop@hipay.com">'] mod='psphipay'}</li>
			<li>{l s='[1]Your own credit card[/1]. Don’t worry, no money will not actually be debited from your card' tags=['<strong>'] mod='psphipay'}</li>
		</ul>
		</p>
		<p>
			<i>{l s='While test mode is active, real payments on your shop are disabled.' mod='psphipay'}</i><br />
			{l s='It is important you should [1]test the payments during off-peak hours[/1] to avoid any inconvenience for your customers.' tags=['<strong>'] mod='psphipay'}<br />
			{l s='Once in Test mode the whole module and payment system will switch to your test account. Instead of displaying your main account’s data, it will display test data.' mod='psphipay'}<br />
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#sandbox_info" href="#sanbox2" aria-expanded="false" aria-controls="sanbox2"><i class="icon icon-angle-right"></i> {l s='What should I do?' mod='psphipay'}</dt>
	<dd id="sanbox2" class="panel-collapse collapse">
		<p>
			{l s='Follow these few steps to test your payments:' mod='psphipay'}<br />
			<ol>
				<li>{l s='Enable test mode above' mod='psphipay'}</li>
				<li>{l s='Make a test order on your shop' mod='psphipay'}</li>
				<li>{l s='Select PrestaShop Payments by HiPay as a method of payment' mod='psphipay'}</li>
				<li>{l s='Enter the test card number (or your own card number if you chose this option), and confirm the order' mod='psphipay'}</li>
				<li>{l s='Check if the order is showing in the back office of your shop. You should receive a confirmation email as well.' mod='psphipay'}</li>
				<li>{l s='Check the balance of your test account: it should have increased by the amount of your test order' mod='psphipay'}</li>
			</ol>
		</p>
		<p>
			{l s='The account balance is updated frequently, however there could be a slight delay between the order confirmation and the balance update.' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#sandbox_info" href="#sanbox3" aria-expanded="false" aria-controls="sanbox3"><i class="icon icon-angle-right"></i> {l s='It’s all good, what’s next?' mod='psphipay'}</dt>
	<dd id="sanbox3" class="panel-collapse collapse">
		<p>
			{l s='You should by all means [1]disable the test mode[/1], otherwise your customers won’t be able to pay on your shop (they wouldn’t be debited!).' tags=['<strong>'] mod='psphipay'}
		</p>
	</dd>
	
	<dt data-toggle="collapse" data-parent="#sandbox_info" href="#sanbox4" aria-expanded="false" aria-controls="sanbox4"><i class="icon icon-angle-right"></i> {l s='It doesn’t seem to be working, what to do?' mod='psphipay'}</dt>
	<dd id="sanbox4" class="panel-collapse collapse">
		<p>
			{l s='You can conduct another test to see how it goes. If it is still not working, you should [1]contact HiPay support team[/1].' tags=['<a href="mailto:prestashop@hipay.com">'] mod='psphipay'}<br />
		</p>
	</dd>
</dl>
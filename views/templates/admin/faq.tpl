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
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2014 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<hr />

<dl id="faq_q_a">
	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse1" aria-expanded="true" aria-controls="collapse1">{l s='In which countries can I use PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="collapse1" class="panel-collapse collapse">
		<p>
			{l s='PrestaShop Payments by HiPay will be available to your customers in the following countries:' mod='psphipay'}<br />
			{l s='Austria, Belgium, Cyprus, Czech Republic, Denmark, Estonia, Finland, France, Germany, Greece, Ireland, Italy, Latvia, Liechtenstein, Lithuania, Luxembourg, Malta, Monaco, Netherlands, Norway, Poland, Portugal, Romania, Russia, Slovakia, Slovenia, Spain, Sweden, Swiss, Turkey and the United Kingdom.' mod='psphipay'}<br />
			{l s='If your customer’s delivery address is in one of these countries, then PrestaShop Payments by HiPay will be automatically offered as a payment method.' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse2" aria-expanded="true" aria-controls="collapse1">{l s='Which currencies can I accept with PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="collapse2" class="panel-collapse collapse">
		<p>
			{l s='PrestaShop Payments by HiPay works with the following currencies:'}<br />
			{l s='Australian Dollar, Canadian Dollar, Swiss Franc, Euro, British Pound, Swedish Krona, US Dollar.' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse3" aria-expanded="true" aria-controls="collapse1">{l s='I want to have PrestaShop Payments by HiPay currencies available in my shop, how can I do?' mod='psphipay'}</dt>
	<dd id="collapse3" class="panel-collapse collapse">
		{l s='If you want to offer new currencies on your shop, please follow these steps:' mod='psphipay'}<br />
		<ol>
			<li>{l s='In Localization > Currencies, add the new currency you want to activate' mod='psphipay'}</li>
			<li>{l s='In Modules > Payment, activate the new currency for PrestaShop Payments by Hipay. If you don’t want to accept this currency through other payment solutions, please make sure it is activated only for PrestaShop Payments by HiPay.' mod='psphipay'}</li>
		</ol>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse4" aria-expanded="true" aria-controls="collapse1">{l s='Which payment solutions are available with PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="collapse4" class="panel-collapse collapse">
		<p>
			{l s='The payment solutions available on your shop with PrestaShop Payments by HiPay depend on your customer’s delivery address: each country has a different set of payment solutions' mod='psphipay'}<br />
			{l s='To see which payment solutions are available in each country, please click here.' mod='psphipay'}
			{l s='If you wish to remove some payment solutions from your offer, please contact prestashop@hipay.com' mod='psphipay'}
		</p>
	</dd>
	
	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse5" aria-expanded="true" aria-controls="collapse1">{l s='How can I transfer my account balance to my own bank account?' mod='psphipay'}</dt>
	<dd id="collapse5" class="panel-collapse collapse">
		<p>
			{l s='You can transfer your account balance from your main account only. If you wish to withdraw your sub-accounts balances too (if any), you should transfer these balances to your main account first. You can only withdraw money from your main account.' mod='psphipay'}<br />
			{l s='Before requesting a transfer, please make sure you have sent your bank details to HiPay. HiPay won’t be able to transfer the money unless your bank details have been validated.' mod='psphipay'}<br />
			{l s='To do so, please go to your HiPay account, “Withdrawal > Enter your bank details” section.' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse6" aria-expanded="true" aria-controls="collapse1">{l s='How much does it cost?' mod='psphipay'}</dt>
	<dd id="collapse6" class="panel-collapse collapse">
		<p>
			{l s='Click here to view the full rates.' mod='psphipay'}<br />
			{l s='Some fees might apply on some operations (withdrawal, transfer, etc), please click here for more info.' mod='psphipay'}
		</p>
	</dd>
	
	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse7" aria-expanded="true" aria-controls="collapse1">{l s='I want more info, where should I look?' mod='psphipay'}</dt>
	<dd id="collapse7" class="panel-collapse collapse">
		<p>
			{l s='Don’t hesitate to log in to your HiPay account to have more detailed information on your account and your transactions.' mod='psphipay'}
		</p>
	</dd>
	
	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse8" aria-expanded="true" aria-controls="collapse1">{l s='Why do I have some sub-accounts?' mod='psphipay'}</dt>
	<dd id="collapse8" class="panel-collapse collapse">
		<p>
			{l s='Your account is created based on your shop’s main currency. To handle other available currencies, some sub-accounts have been created. Each sub-account handles a different currency.' mod='psphipay'}<br />
			{l s='To withdraw money from your sub-account, you should transfer their respective balances to your main account first' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse9" aria-expanded="true" aria-controls="collapse1">{l s='I want to make a test payment, how should I do?' mod='psphipay'}</dt>
	<dd id="collapse9" class="panel-collapse collapse">
		<p>
			{l s='You have received two set of credentials when your account has been created: one for the main account, one for your test account.' mod='psphipay'}<br />
			{l s='Go to the “Test mode” section above, then enter your test account credentials. Save it, and turn the test mode by switching to Yes.' mod='psphipay'}<br />

			<br />

			{l s='To run some test, you can either:' mod='psphipay'}<br />
			<ol>
				<li>{l s='use your own credit card (but it will make a pre-authorization on it, so make sure you run some test with small amounts)' mod='psphipay'}</li>
				<li>{l s='or request a test card number to HiPay. For this, contact them at prestashop@hipay.com and give them your test account number.' mod='psphipay'}</li>
			</ol>
			
			<br />
			
			{l s='And then, proceed with a test order of a small amount. Your should check whether:' mod='psphipay'}<br />
			<ol>
				<li>{l s='the order is complete (you have received a confirmation email),' mod='psphipay'}</li>
				<li>{l s='it shows in your back-office' mod='psphipay'}</li>
				<li>{l s='your test balance and transactions have been updated with the good amount'}</li>
			</ol>
			{l s='Do not forget to switch back to the normal mode once you have finished your tests!' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#collapse10" aria-expanded="true" aria-controls="collapse1">{l s='What is “identification” and why my account should be “identified”?' mod='psphipay'}</dt>
	<dd id="collapse10" class="panel-collapse collapse">
		<p>
			{l s='blabla' mod='psphipay'}
		</p>
	</dd>
</dl>

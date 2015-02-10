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

<hr />

<dl id="faq_q_a">
	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq1" aria-expanded="true" aria-controls="faq1"><i class="icon icon-question-circle"></i> {l s='In which countries can I use PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="faq1" class="panel-collapse collapse">
		<p>
			{l s='PrestaShop Payments by HiPay is available to all your customers, wherever they are.' mod='psphipay'}<br />
			{l s='Depending on a customer\'s country, some local and leading payment solutions will be available as well.' mod='psphipay'}<br />
			{l s='To see which payment methods are available per country, please [1]click here[/1].' tags=['<a href="https://www.prestashop.com/download/pdf/pspayments/PrestaShop_Payments-metodos-paises.pdf" target="_blank">'] mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq2" aria-expanded="true" aria-controls="faq2"><i class="icon icon-question-circle"></i> {l s='Which payment solutions are available with PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="faq2" class="panel-collapse collapse">
		<p>
			{l s='The payment solutions available on your shop with PrestaShop Payments by HiPay depend on your customer’s delivery address: each country has a different set of payment solutions' mod='psphipay'}<br />
			{l s='To see which payment solutions are available in each country, please [1]click here[/1].' tags=['<a href="https://www.prestashop.com/download/pdf/pspayments/PrestaShop_Payments-metodos-paises.pdf" target="_blank">'] mod='psphipay'}
			{l s='If you wish to remove some payment solutions from your offer, please contact [1]prestashop@hipay.com[/1]' tags=['<a href="mailto:prestashop@hipay.com">'] mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq3" aria-expanded="true" aria-controls="faq3"><i class="icon icon-question-circle"></i> {l s='Which currencies can I accept with PrestaShop Payments by HiPay?' mod='psphipay'}</dt>
	<dd id="faq3" class="panel-collapse collapse">
		<p>
			{l s='PrestaShop Payments by HiPay works with the following currencies:' mod='psphipay'}<br />
			{l s='Australian Dollar, Canadian Dollar, Swiss Franc, Euro, British Pound, Swedish Krona, US Dollar, Polish Sloty.' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq4" aria-expanded="true" aria-controls="faq4"><i class="icon icon-question-circle"></i> {l s='I want to have PrestaShop Payments by HiPay currencies available in my shop, how can I do?' mod='psphipay'}</dt>
	<dd id="faq4" class="panel-collapse collapse">
		{l s='If you want to offer new currencies on your shop, please follow these steps:' mod='psphipay'}<br />
		<ol>
			<li>{l s='In Localization > Currencies, add the new currency you want to activate' mod='psphipay'}</li>
			<li>{l s='In Modules > Payment, activate the new currency for PrestaShop Payments by HiPay. If you don’t want to accept this currency through other payment solutions, please make sure it is activated only for PrestaShop Payments by HiPay.' mod='psphipay'}</li>
		</ol>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq5" aria-expanded="true" aria-controls="faq5"><i class="icon icon-question-circle"></i> {l s='How much does it cost?' mod='psphipay'}</dt>
	<dd id="faq5" class="panel-collapse collapse">
		<p>
			{l s='[1]Click here[/1] to view the full rates.' tags=['<a href="http://www.prestashop.com/download/pdf/pspayments/PrestaShop_Payments-detalles_de_precios.pdf" target="_blank">'] mod='psphipay'}<br />
			{l s='Some fees might apply on some operations (withdrawal, transfer, etc), please [1]click here for more info[/1].' tags=['<a href="#" target="_blank">'] mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq6" aria-expanded="true" aria-controls="faq6"><i class="icon icon-question-circle"></i> {l s='Why do I have some sub-accounts?' mod='psphipay'}</dt>
	<dd id="faq6" class="panel-collapse collapse">
		<p>
			{l s='Your account is created based on your shop’s main currency. To handle other available currencies, some sub-accounts have been created. Each sub-account handles a different currency.' mod='psphipay'}<br />
			{l s='To withdraw money from your sub-account, you should transfer their respective balances to your main account first' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq7" aria-expanded="true" aria-controls="faq7"><i class="icon icon-question-circle"></i> {l s='How can I transfer my account balance to my own bank account?' mod='psphipay'}</dt>
	<dd id="faq7" class="panel-collapse collapse">
		<p>
			{l s='You can transfer your account balance from your main account only. If you wish to withdraw your sub-accounts balances too (if any), you should transfer these balances to your main account first. You can only withdraw money from your main account.' mod='psphipay'}<br />
			{l s='Before requesting a transfer, please make sure you have sent your bank details to HiPay. HiPay won’t be able to transfer the money unless your bank details have been validated.' mod='psphipay'}<br />
			{l s='To do so, please go to your [1]HiPay account[/1], “Withdrawal > Enter your bank details” section.' tags=['<a href="https://www.hipaydirect.com/prestashop-payments/" target="_blank">'] mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq8" aria-expanded="true" aria-controls="faq8"><i class="icon icon-question-circle"></i> {l s='What is “identification” and why my account should be “identified”?' mod='psphipay'}</dt>
	<dd id="faq8" class="panel-collapse collapse">
		<p>
			{l s='An unidentified account is an account which we have not checked yet, nor confirmed its identification details upon signing up. The balance of an unidentified account cannot exceed €2500.' mod='psphipay'}<br />
			<br />
			{l s='Any transaction which, if completed, would exceed this maximum balance, will be refused by our system.' mod='psphipay'}<br />
			{l s='For an account to the registered as «Identified» by HiPay’s Verification Department, you must send HiPay the legal and administrative documents we need to positively establish your identity (i.e. the information you entered when you first signed up).' mod='psphipay'}				<br />
			{l s='In keeping with our General Conditions of Use, you must inform HiPay of any changes to your personal data, and submit suitable items of written proof.' mod='psphipay'}<br />
			<br />
			{l s='Please click here for more info' mod='psphipay'}
		</p>
	</dd>

	<dt data-toggle="collapse" data-parent="#faq_q_a" href="#faq9" aria-expanded="true" aria-controls="faq9"><i class="icon icon-question-circle"></i> {l s='I want more info, where should I look?' mod='psphipay'}</dt>
	<dd id="faq9" class="panel-collapse collapse">
		<p>
			{l s='Don’t hesitate to [1]log in to your HiPay account[/1] to have more detailed information on your account and your transactions.' tags=['<a href="https://www.hipaydirect.com/prestashop-payments/" target="_blank">'] mod='psphipay'}
		</p>
	</dd>
</dl>

<hr />

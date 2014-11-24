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

<div class="panel">
	<div class="row psphipay-header">
		<div class="col-xs-6 col-md-6 text-center">
			<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/hipay.png" id="payment-logo" />
		</div>
		<div class="col-xs-6 col-md-6 text-center">
			<h4>{l s='Online payment processing' mod='psphipay'}</h4>
			<h4>{l s='Fast - Secure - Reliable' mod='psphipay'}</h4>
		</div>
	</div>

	<hr />

	<div class="psphipay-content">
		<div class="row">
			<div class="col-md-6">
				<h5>{l s='The fast, simple multimedia payment solution for everyone in France and Europe!' mod='psphipay'}</h5>
				<p>
					{l s='Thanks to its adaptability and performance, Hipay has already won over 12,000 merchants and a million users.' mod='psphipay'}<br />
					{l s='Its array of 15 of the most effective payment solutions in Europe offers your customers instant recognition and a reassuring guarantee for their consumer habits.' mod='psphipay'}<br />
					<br />
					{l s='Once your account is activated you will receive more details by email.' mod='psphipay'}<br />
					{l s='All merchant using Prestashop can benefit from special price by contacting the following email' mod='psphipay'}: <a href="mailto:prestashop@hipay.com" class="alert-link">prestashop@hipay.com</a><br />
					<br />
					{l s='Do not hesitate to contact us. The fees can decrease by 50%.' mod='psphipay'}</dd>
				</p>
			</div>

			<div class="col-md-6">
				<h5>{l s='Hipay boosts your sales Europe-wide thanks to' mod='psphipay'}</h5>
				<ul>
					<li>{l s='Payment solutions specific to each European country' mod='psphipay'}</li>
					<li>{l s='No subscription or installation charges' mod='psphipay'}</li>
					<li>{l s='Contacts with extensive experience of technical and financial issues' mod='psphipay'}</li>
					<li>{l s='Dedicated customer service' mod='psphipay'}</li>
					<li>{l s='Anti-fraud system and permanent monitoring for high-risk behaviour' mod='psphipay'}.</li>
					<li>{l s='Hipay is part of the Hi-Media Group (Allopass).' mod='psphipay'}</li>
				</ul>
				<br />
				<em class="text-muted small">
					&raquo; {l s='You can get a PDF documentation to configure HiPay in Prestashop' mod='psphipay'} :
					<ul>
						<li><a href="https://www.hipay.com/dl/HiPay_Wallet_Prestashop_Configuration_Guide_EN.pdf" target="_blank">{l s='English' mod='psphipay'}</a></li>
						<li><a href="https://www.hipay.com/dl/HiPay_Wallet_Configuration_Module_Prestashop_FR.pdf" target="_blank">{l s='Français' mod='psphipay'}</a></li>
					</ul>
				</em>
			</div>
		</div>

		<hr />

		<div class="row">
			<div class="col-md-6 col-xs-12 text-center">
				<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/cards.png" id="cart-logo" />
			</div>
			<div class="col-md-6 col-xs-12 text-center">
				<h6 class="text-branded">{l s='For transactions in US Dollars (USD) only' mod='psphipay'}</h6>
				<p class="text-branded">{l s='Call 888-888-1234 if you have any questions or need more information!' mod='psphipay'}</p>
			</div>
		</div>
	</div>
</div>

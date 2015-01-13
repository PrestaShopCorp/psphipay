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

<div class="panel">
	<div class="row" id="psphipay-header">
		<div class="col-xs-12 col-sm-12 col-md-6 text-center">
			<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/logo.png" id="payment-logo" />
		</div>
		<div class="col-xs-12 col-sm-12 col-md-6 text-center">
			<h4>{l s='PrestaShop\'s Official Payment Solution' mod='psphipay'}</h4>
		</div>
	</div>

	<hr />

	<div id="psphipay-content">
		{if isset($welcome_message)}
			<div class="row">
				<div class="col-md-12 col-xs-12">
					<p>
						<span id="welcome-message">{l s='Welcome to PrestaShop Payments by HiPay!' mod='psphipay'}</span>
						<br />
						{l s='Your store can now accept payments in 7 currencies.' mod='psphipay'}<br />
						{l s='You should have received by email your credentials to connect to your HiPay account. You also have received some test credentials to run payment tests before going live.' mod='psphipay'}<br />
						{l s='If you have any question, please contact us at prestashop@hipay.com.' mod='psphipay'}<br />
						<br />
						{l s='Happy selling!' mod='psphipay'}
					</p>
				</div>
			</div>
		{else}
			{if isset($balance_warning) && ($balance_warning == true)}
				<div class="modal fade" id="psphipay_warning_modal" tabindex="-1" role="dialog" aria-labelledby="psphipay_warning_modal_label" aria-hidden="true" data-show="true" onload="$(this).modal('show')">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title" id="psphipay_warning_modal_label">{l s='Warning!' mod='psphipay'}</h4>
							</div>
							<div class="modal-body">
								<p>
									{l s='Your account balance is getting close to %1$s. Please make sure your account has been identified to be able to trasnfer money from your HiPay account to your own bank account.' mod='psphipay' sprintf=[$amount_limit]}
								</p>
								<br />
								<a href="#">{l s='Click here for more information.' mod='psphipay'}</a>
							</div>
						</div>
					</div>
				</div>
			{/if}

			<div class="row">
				<div class="col-md-12 col-xs-12">
					{if ($is_logged == true)}
						<p class="text-center">
							<a class="btn btn-primary" data-toggle="collapse" href="#psphipay-marketing-content" aria-expanded="false" aria-controls="psphipay-marketing-content">
								{l s='More info' mod='psphipay'}
							</a>
						</p>
					{/if}
					<div {if ($is_logged == true)}class="collapse"{/if} id="psphipay-marketing-content">
						<div class="row">
							{if ($is_logged == true)}
								<hr />
							{/if}
							<div class="col-md-6">
								<h4>{l s='A complete solution at the best price' mod='psphipay'}</h4>
								<ul class="ul-spaced">
									<li>{l s='Start instantly, no contract required to start' mod='psphipay'}</li>
									<li>{l s='Accept several currencies with 10+ local payment solutions in Europe' mod='psphipay'}</li>
									<li>{l s='No registration or installation fee' mod='psphipay'}</li>
									<li>{l s='Anti-fraud system and full-time monitoring of high-risk behavior' mod='psphipay'}</li>
								</ul>
							</div>

							<div class="col-md-6">
								<h4>{l s='From 1.05% + 0.25€ per transaction!' mod='psphipay'}</h4>
								<ul class="ul-spaced">
									<li>{l s='A rate that adapts to your volume of activity' mod='psphipay'}</li>
									<li>{l s='25% less expensive than leading solutions in the market*' mod='psphipay'}</li>
								</ul>
								<br />
								<a href="#">{l s='See the complete list of rates for PrestaShop Payments by HiPay' mod='psphipay'}</a>
								<br />
								<em class="text-muted small">
									* {l s='Source' mod='psphipay'}
								</em>
							</div>
						</div>

						<hr />

						<div class="row">
							<div class="col-md-12 col-xs-12">
								<h4>{l s='Accept payments from all over Europe in just a few clicks' mod='psphipay'}</h4>
							</div>
						</div>

						<div class="row">
							<div class="col-md-12 col-xs-12 text-center">
								<img src="{$module_dir|escape:'html':'UTF-8'}/views/img/cards.png" id="cards-logo" />
							</div>
						</div>

						<hr />

						<div class="row">
							<div class="col-md-12 col-xs-12">
								<h4>{l s='3 simple steps:' mod='psphipay'}</h4>
								<ol>
									<li>{l s='Your account is pre-approved when you create your PrestaShop store so that you can start accepting online payments right away.' mod='psphipay'}</li>
									<li>{l s='Finalize your PrestaShop Payments by HiPay registration before you reach 2,500€ on your account.' mod='psphipay'}</li>
									<li>{l s='Easily collect and transfer your money from your PrestaShop Payments by HiPay account to your own bank account.' mod='psphipay'}</li>
								</ol>
							</div>
						</div>
					</div>
				</div>
			</div>
		{/if}
	</div>
</div>

{$alerts}

{if ($is_logged == false)}
	{$login_form}
{else}
	<div role="tabpanel">
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} class="active"{/if}><a href="#psp_settings_form" aria-controls="psp_settings_form" role="tab" data-toggle="tab">
				<span class="icon icon-cogs"></span> {l s='Settings' mod='psphipay'}</a>
			</li>
			<li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'transactions'))} class="active"{/if}><a href="#psp_transactions_form" aria-controls="psp_transactions_form" role="tab" data-toggle="tab">
				<span class="icon icon-money"></span> {l s='Transactions' mod='psphipay'}</a>
			</li>
			<li role="presentation"{if ((isset($active_tab) == true) && ($active_tab == 'sandbox'))} class="active"{/if}><a href="#psp_sandbox_form" aria-controls="psp_sandbox_form" role="tab" data-toggle="tab">
				<span class="icon icon-check-square-o"></span> {l s='Test mode' mod='psphipay'}</a>
			</li>
			<li role="presentation"><a href="#psp_services_form" aria-controls="psp_services_form" role="tab" data-toggle="tab">
				<span class="icon icon-users"></span> {l s='FAQ' mod='psphipay'}</a>
			</li>
		</ul>

		<div class="tab-content">
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == false) || ($active_tab == 'settings'))} active{/if}" id="psp_settings_form">{$settings_form}</div>
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'transactions'))} active{/if}" id="psp_transactions_form">{$transactions_form}</div>
			<div role="tabpanel" class="tab-pane{if ((isset($active_tab) == true) && ($active_tab == 'sandbox'))} active{/if}" id="psp_sandbox_form">{$sandbox_form}</div>
			<div role="tabpanel" class="tab-pane" id="psp_services_form">{$services_form}</div>
		</div>
	</div>
{/if}

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

<div id="refund" class="panel">
    <div class="panel-heading">
        <i class="icon-undo"></i> {l s='Refund' mod='psphipay'}
    </div>

    <input type="hidden" name="refund_amount_max" id="refund-amount-max" value="{$order->total_paid_tax_incl|string_format:'%.2f'}">

    <span class="hidden" id="refund-link">{$refund_link|urlencode}</span>

    <span class="hidden" id="partial-refund-confirmation-msg">{l s='You are about to refund this order partially. Are you sure?' mod='psphipay'}</span>
    <span class="hidden" id="total-refund-confirmation-msg">{l s='You are about to refund this order totally. Are you sure?' mod='psphipay'}</span>

    <span class="hidden" id="refund-amount-max-alert-msg">{l s='The refund amount cannot be greater than the total amount of the order.' mod='psphipay'}</span>
    <span class="hidden" id="refund-amount-empty-msg">{l s='The amount cannot be empty or equal to "0".' mod='psphipay'}</span>

    {if ($details)}
        <div class="panel-body well well-sm">
            <dl class="dl-horizontal">
                {foreach from=$details key=key item=value}
                    <dt>{$key}:</dt><dd>{$value}</dd>
                {/foreach}
            </dl>
        </div>
    {/if}

    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-warning">
                {l s='Please, be careful, only one refund is possible per order.' mod='psphipay'}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-5 text-left">
            <a href="javascript:;" class="btn btn-primary" id="partial-refund-button">
                <i class="icon icon-undo"></i> {l s='Partial refund' mod='psphipay'}
            </a>
        </div>

        <div id="partial-refund-details" class="col-xs-6 col-xs-offset-1 collapse">
            <div class="col-xs-8">
                <div class="input-group">
                    <input type="text" name="patial_refund_amount" id="partial-refund-amount">
                    <span class="input-group-addon">
                        {$currency->sign} ({$currency->iso_code})
                    </span>
                </div>
            </div>

            <div class="col-xs-4">
                <div class="input-group">
                    <a href="javascript:;" class="btn btn-default" id="partial-refund-process">
                        <i class="icon icon-check"></i> Process
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <br />
    </div>

    <div class="row">
        <div class="col-xs-5 text-left">
            <a href="javascript:;" class="btn btn-primary" id="total-refund-button">
                <i class="icon icon-exchange"></i> {l s='Total refund' mod='psphipay'}
            </a>
        </div>
    </div>
</div>

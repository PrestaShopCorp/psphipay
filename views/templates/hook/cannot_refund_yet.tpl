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

    <input type="hidden" name="refund_link" id="refund-link" value="{$refund_link|urlencode}">
    <input type="hidden" name="refund_amount_max" id="refund-amount-max" value="{$order->total_paid_tax_incl|string_format:'%.2f'}">
    <input type="hidden" name="refund_amount_max_alert_msg" id="refund-amount-max-alert-msg" value="{l s='The refund amount cannot be greater than the total amount of the order' mod='psphipay'}">

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
        <div class="col-md-12">
            <div class="alert alert-warning">
                {l s='The refund will be available 24h hours after payment validation, thank you for your patience.' mod='psphipay'}
            </div>
        </div>
    </div>
</div>

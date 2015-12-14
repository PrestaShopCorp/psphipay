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
            <div class="alert alert-success">
	            {if ($state == 'PSP_HIPAY_OS_PARTIALLY_REFUNDED')}
                	{l s='Order already partially refunded.' mod='psphipay'}
                {else}
                	{l s='Order already totally refunded.' mod='psphipay'}
                {/if}
            </div>
        </div>
    </div>
</div>

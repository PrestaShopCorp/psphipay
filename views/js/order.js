/**
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
*/

$(document).ready(function() {

    // Partial refunds
    $(this).on('click', '#partial-refund-button', function () {
        $('#partial-refund-details').fadeIn();
    });

    $(this).on('click', '#partial-refund-process', function () {
        var amount = $('#partial-refund-amount').val();
        var amount_max = $('#refund-amount-max').val();

        if (isNaN(parseFloat(amount)) || (parseFloat(amount) == 0.00)) {
            return alert($('#refund-amount-empty-msg').text());
        }

        if (parseFloat(amount) > parseFloat(amount_max)) {
            return alert($('#refund-amount-max-alert-msg').text());
        }

        var confirmation = $('#partial-refund-confirmation-msg').text();

        if (confirm(confirmation) == false) {
            return false;
        }

        processRefund({
            amount: amount,
        });
    });

    $(this).on('keyup', '#partial-refund-amount', function (event) {
        return (event.keyCode === 13) ? $('#partial-refund-process').trigger('click') : false;
    });

    // Total refunds
    $(this).on('click', '#total-refund-button', function () {
        var confirmation = $('#total-refund-confirmation-msg').text();

        if (confirm(confirmation) == false) {
            return false;
        }

        processRefund();
    });

});

function getRefundControllerLink() {
    var link = $('#refund-link').text();

    return decodeURIComponent(link);
}

function processRefund(data) {
    $.ajax({
        url: getRefundControllerLink(),
        data: data
       }).success(function (result) {
        location.reload();
    }).fail(function (error) {
        alert(error.responseText);
    });
}

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
    iterations = 0;
    timer = false;

    checkOrder();

});

function checkOrder() {
    if (timer == false) {
        timer = setInterval(processCheck, 1000);
    }
}

function processCheck() {
    iterations += 1;

    if (iterations == 10) {
        return redirectError();
    }

    return $.ajax({
        url: ajax_url
       }).success(function (result) {
           if (result != undefined) {
               clearInterval(timer);

               location.reload();
           }
    });
}

function redirectError() {
    clearInterval(timer);

    var url = window.location.href;
    window.location.href = url + '&failure=true';
}

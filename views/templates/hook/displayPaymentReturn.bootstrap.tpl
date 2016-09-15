{**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license GNU GENERAL PUBLIC LICENSE
 *}

<div id="oyst-confirmation">
    <div class="alert alert-success">
        <div class="container-icon-success">
            <i class="success-confirm-payment"></i>
        </div>
        <p>{l s='Your order %s is complete.' sprintf=$oyst.order_reference mod='oyst'}</p>
        <p>{l s='Payment transaction ID: %s' sprintf=$oyst.transaction_id mod='oyst'}</p><br><br>
        <p>{l s='If you have questions, comments or concerns, please contact our' mod='oyst'} <a style="color:white" href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team. ' mod='oyst'}</a>.</p>
    </div>
</div>

<style>
    #oyst-confirmation .alert.alert-success:before {
        content: "";
        padding-right: 0px!important;
    }
    #oyst-confirmation .alert.alert-success > p {
        font-size: 14px;
    }
    #oyst-confirmation .alert.alert-success .container-icon-success {
        text-align: center;
        height: 80px;
        line-height: 80px;
    }
    .success-confirm-payment:before {
        content: "";
        font-family: "FontAwesome";
        font-size: 80px;
        text-align: center;
    }
</style>
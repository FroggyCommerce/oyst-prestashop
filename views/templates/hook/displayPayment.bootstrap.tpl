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

<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a href="{$link->getModuleLink('oyst', 'payment')|escape:'html'}" class="oyst">
                {l s='Pay by Credit Card' mod='oyst'}
            </a>
        </p>
    </div>
</div>

<style>
    p.payment_module a.oyst::after {
        color: #777;
        content: "";
        display: block;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        margin-top: -11px;
        position: absolute;
        right: 15px;
        top: 50%;
        width: 14px;
    }
    p.payment_module a.oyst {
        background: #fbfbfb url("{$oyst.module_dir}views/img/logo-credit-card.png") no-repeat scroll 15px 15px;
        padding-left: 125px;
    }
</style>
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
    <p class="conf">
        {l s='Your order %s is complete.' sprintf=$oyst.order_reference mod='oyst'}<br>
        {l s='Payment transaction ID: %s' sprintf=$oyst.transaction_id mod='oyst'}<br><br>
        {l s='If you have questions, comments or concerns, please contact our' mod='oyst'} <a style="color:white" href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='expert customer support team. ' mod='oyst'}</a>.
    </p>
</div>

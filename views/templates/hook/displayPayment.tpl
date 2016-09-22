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

<p class="payment_module">
    <a href="{$link->getModuleLink('oyst', 'payment')|escape:'htmlall':'UTF-8'}">
        <img src="{$oyst.module_dir|escape:'htmlall':'UTF-8'}views/img/logo-horizontal-credit-card.png" height="49" />
        {l s='Pay by Credit Card' mod='oyst'}
    </a>
</p>
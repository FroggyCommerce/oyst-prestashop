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

{if isset($oyst.result) && $oyst.result eq 'ok'}
    <p class="conf"><strong>{l s='The new configuration has been saved!' mod='oyst'}</strong></p>
{/if}

{if !$oyst.allow_url_fopen_check}
    <p class="error"><strong>{l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}</strong></p>
{/if}
{if !$oyst.curl_check}
    <p class="error"><strong>{l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}</strong></p>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}


    <form id="module_form" class="defaultForm form-horizontal" method="POST" action="#">

        <p align="center">
            <img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /><br>
            Module version : {$oyst.module_version}
        </p>

        <fieldset id="oyst_fieldset">
            <legend><img src="{$oyst.module_dir|escape:'html':'UTF-8'}logo.png" alt="" width="16" />{l s='Enable payment feature:' mod='oyst'}</legend>

            <div class="margin-form" style="padding-left:15px">

                <p><b>{l s='Set your Oyst API key:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_PAYMENT_KEY" name="FC_OYST_API_PAYMENT_KEY" value="{$oyst.FC_OYST_API_PAYMENT_KEY|escape:'htmlall':'UTF-8'}" style="width:400px" /></div>
                <p class="help-block">{l s='You need this key to use Oyst payment.' mod='oyst'}</p>

                {if isset($oyst.oyst_payment_connection_test.result)}
                    {if $oyst.oyst_payment_connection_test.result}
                        <p class="conf"><strong>{l s='Your key is valid!' mod='oyst'}</strong></p>
                    {else}
                        <p class="error"><strong>
                            {l s='Your key seems invalid!' mod='oyst'}
                            <br>
                            <input type="checkbox" id="oyst_payment_connection_debug" name="oyst_payment_connection_debug" value="1"{if $smarty.post.oyst_payment_connection_debug} checked="checked"{/if} /> Debug
                            {if isset($smarty.post.oyst_payment_connection_debug) && $smarty.post.oyst_payment_connection_debug}
                                <br><pre>{$oyst.oyst_payment_connection_test.values|print_r}</pre>
                            {/if}
                        </strong></p>
                    {/if}
                {/if}

                <p><b>{l s='Enable payment feature:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="checkbox" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} /></div>
                <p class="help-block">{l s='Enable payment feature on your website' mod='oyst'}</p>

                <p><b>{l s='Set the Oyst payment endpoint:' mod='oyst'}</label></b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}" style="width:400px" /></div>
                {* https://api.staging.uptain.eu/payment *}

                <br><p><input type="submit" name="submitOystConfiguration" value="{l s='Save' mod='oyst'}" name="oyst_ft_form" class="button" /></p>
            </div>
        </fieldset>

        <br><br>

        <fieldset id="oyst_fieldset">
            <legend><img src="{$oyst.module_dir|escape:'html':'UTF-8'}logo.png" alt="" width="16" />{l s='Enable catalog feature:' mod='oyst'}</legend>

            <div class="margin-form" style="padding-left:15px">

                <p><b>{l s='Set your Oyst API key:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_CATALOG_KEY" name="FC_OYST_API_CATALOG_KEY" value="{$oyst.FC_OYST_API_CATALOG_KEY|escape:'htmlall':'UTF-8'}" style="width:400px" /></div>
                <p class="help-block">{l s='You need this key to use export your catalog and import orders' mod='oyst'}</p>
                {if isset($oyst.oyst_catalog_connection_test.result)}
                    {if $oyst.oyst_catalog_connection_test.result}
                        <p class="conf"><strong>{l s='Your key is valid!' mod='oyst'}</strong></p>
                    {else}
                        <p class="error"><strong>
                            {l s='Your key seems invalid!' mod='oyst'}
                            <br>
                            <input type="checkbox" id="oyst_catalog_connection_debug" name="oyst_catalog_connection_debug" value="1"{if $smarty.post.oyst_catalog_connection_debug} checked="checked"{/if} /> Debug
                            {if isset($smarty.post.oyst_catalog_connection_debug) && $smarty.post.oyst_catalog_connection_debug}
                                <br><pre>{$oyst.oyst_catalog_connection_test.values|print_r}</pre>
                            {/if}
                        </strong></p>
                    {/if}
                {/if}

                <p><b>{l s='Enable catalog feature:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="checkbox" id="FC_OYST_CATALOG_EXPORT_FEATURE" name="FC_OYST_CATALOG_EXPORT_FEATURE" value="1"{if $oyst.FC_OYST_CATALOG_EXPORT_FEATURE} checked="checked"{/if} /></div>
                <p class="help-block">{l s='Export your catalog to Oyst to increase the number of orders!' mod='oyst'}</p>

                <p><b>{l s='Set the Oyst catalog endpoint:' mod='oyst'}</label></b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_CATALOG_ENDPOINT" name="FC_OYST_API_CATALOG_ENDPOINT" value="{$oyst.FC_OYST_API_CATALOG_ENDPOINT|escape:'htmlall':'UTF-8'}" style="width:400px" /></div>
                {* https://api.staging.uptain.eu/catalog *}
                <br><p><input type="submit" name="submitOystConfiguration" value="{l s='Save' mod='oyst'}" name="oyst_ft_form" class="button" /></p>
                </div>
        </fieldset>
    </form>
{/if}


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

<h2>{l s='Oyst' mod='oyst'}</h2>

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

<form method="POST" action="#">
    <fieldset id="oyst_fieldset">
        <legend><img src="{$oyst.module_dir|escape:'html':'UTF-8'}logo.png" alt="" width="16" />{l s='Oyst configuration' mod='oyst'}</legend>

            <div class="margin-form" style="padding-left:15px">

                <p><b>{l s='Set your Oyst API key:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_KEY" name="FC_OYST_API_KEY" value="{$oyst.FC_OYST_API_KEY|escape:'htmlall':'UTF-8'}" size="128" /></div>
                <p class="help-block">{l s='You need this key to use Oyst payment but also so export your catalog and import orders' mod='oyst'}</p>
                {if isset($oyst.oyst_connection_test)}
                    {if $oyst.oyst_connection_test}
                        <p class="conf"><strong>{l s='Your key is valid!' mod='oyst'}</strong></p>
                    {else}
                        <p class="error"><strong>{l s='Your key seems invalid!' mod='oyst'}</strong></p>
                    {/if}
                {/if}
                <br>

                <p><b>{l s='Enable payment feature:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="checkbox" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} /></div>
                <p class="help-block">{l s='Enable Oyst payment for your shop!' mod='oyst'}</p>
                <br>

                <p><b>{l s='Set the Oyst payment endpoint:' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px"><input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}" size="128" /></div>
                <p class="help-block">
                    {l s='If you\'re not sure what to set, do not change it.' mod='oyst'}<br>
                    {l s='Test URL:' mod='oyst'} http://payment.staging.oyst.eu
                </p>
                <br>

                <p><b>{l s='Payment notification URL :' mod='oyst'}</b></p>
                <div class="margin-form" style="padding-left:15px">{$oyst.payment_notification_url|escape:'htmlall':'UTF-8'}</div>
                <p class="help-block">{l s='Give this url to Oyst.' mod='oyst'}</p>
                <br>


                    <div style="display:none">
                    <hr />

                    <div class="form-group clearfix">
                        <label class="col-lg-3">{l s='Enable export catalog feature:' mod='oyst'}</label>
                        <div class="col-lg-9">
                            <input type="checkbox" id="FC_OYST_CATALOG_EXPORT_FEATURE" name="FC_OYST_CATALOG_EXPORT_FEATURE" value="1"{if $oyst.FC_OYST_CATALOG_EXPORT_FEATURE} checked="checked"{/if} />
                            <p class="help-block">{l s='Export your catalog to Oyst to increase the number of orders!' mod='oyst'}</p>
                        </div>
                    </div>

                    <div class="form-group clearfix">
                        <label class="col-lg-3">{l s='Enable import orders feature:' mod='oyst'}</label>
                        <div class="col-lg-9">
                            <input type="checkbox" id="FC_OYST_IMPORT_ORDERS_FEATURE" name="FC_OYST_IMPORT_ORDERS_FEATURE" value="1"{if $oyst.FC_OYST_IMPORT_ORDERS_FEATURE} checked="checked"{/if} />
                            <p class="help-block">{l s='Import orders made on Oyst website!' mod='oyst'}</p>
                        </div>
                    </div>

                    <div class="form-group clearfix">
                        <label class="col-lg-3">{l s='Set the Oyst catalog endpoint:' mod='oyst'}</label>
                        <div class="col-lg-9">
                            <input type="text" id="FC_OYST_API_EXPORT_ENDPOINT" name="FC_OYST_API_EXPORT_ENDPOINT" value="{$oyst.FC_OYST_API_EXPORT_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                            <p class="help-block">
                                {l s='If you\'re not sure what to set, do not change it.' mod='oyst'}<br>
                                {l s='Test URL:' mod='oyst'} http://catalog.staging.oyst.eu
                            </p>
                        </div>
                    </div>

                        {*
                    <div class="form-group clearfix">
                        <label class="col-lg-3">{l s='Notification URL :' mod='oyst'}</label>
                        <div class="col-lg-9">
                            {$oyst.notification_url|escape:'htmlall':'UTF-8'}
                            <p class="help-block">{l s='Give this url to Oyst.' mod='oyst'}</p>
                        </div>
                    </div>
    *}

                    </div>

                <br><p><input type="submit" name="submitOystConfiguration" value="{l s='Save' mod='oyst'}" name="oyst_ft_form" class="button" /></p>

            </div>
    </fieldset>
</form>

{/if}
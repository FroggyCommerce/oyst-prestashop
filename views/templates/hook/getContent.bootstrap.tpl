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
<div class="bootstrap">
    <div class="alert alert-success">
        <button data-dismiss="alert" class="close" type="button">×</button>
        {l s='The new configuration has been saved!' mod='oyst'}
    </div>
</div>
{/if}

{if !$oyst.allow_url_fopen_check}
    <div class="bootstrap">
        <div class="alert alert-danger">
            {l s='You have to enable "allow_url_fopen" on your server to use this module!' mod='oyst'}
        </div>
    </div>
{/if}
{if !$oyst.curl_check}
    <div class="bootstrap">
        <div class="alert alert-danger">
            {l s='You have to enable "curl" extension on your server to use this module!' mod='oyst'}
        </div>
    </div>
{/if}

{if $oyst.allow_url_fopen_check && $oyst.curl_check}


<form id="module_form" class="defaultForm form-horizontal" method="POST" action="#">

    <p align="center">
        <img src="{$oyst.module_dir|escape:'html':'UTF-8'}views/img/logo-oyst.png" /><br>
        Module version : {$oyst.module_version}
    </p>

    <div class="panel" class="oyst_fieldset">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Enable payment feature:' mod='oyst'}
        </div>
        <div class="oyst-admin-tab">
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Set your Oyst API key:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="text" id="FC_OYST_API_PAYMENT_KEY" name="FC_OYST_API_PAYMENT_KEY" value="{$oyst.FC_OYST_API_PAYMENT_KEY|escape:'htmlall':'UTF-8'}" />
                    <p class="help-block">{l s='You need this key to use Oyst payment.' mod='oyst'}</p>
                    {if isset($oyst.oyst_payment_connection_test.result)}
                        {if $oyst.oyst_payment_connection_test.result}
                            <div class="alert alert-success">{l s='Your key is valid!' mod='oyst'}</div>
                        {else}
                            <div class="alert alert-danger">
                                {l s='Your key seems invalid!' mod='oyst'}
                                <br>
                                <input type="checkbox" id="oyst_payment_connection_debug" name="oyst_payment_connection_debug" value="1"{if $smarty.post.oyst_payment_connection_debug} checked="checked"{/if} /> Debug
                                {if isset($smarty.post.oyst_payment_connection_debug) && $smarty.post.oyst_payment_connection_debug}
                                    <br><pre>{$oyst.oyst_payment_connection_test.values|print_r}</pre>
                                {/if}
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Enable payment feature:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="checkbox" id="FC_OYST_PAYMENT_FEATURE" name="FC_OYST_PAYMENT_FEATURE" value="1"{if $oyst.FC_OYST_PAYMENT_FEATURE} checked="checked"{/if} />
                    <p class="help-block">{l s='Enable payment feature on your website' mod='oyst'}</p>
                </div>
            </div>
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Set the Oyst payment endpoint:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="text" id="FC_OYST_API_PAYMENT_ENDPOINT" name="FC_OYST_API_PAYMENT_ENDPOINT" value="{$oyst.FC_OYST_API_PAYMENT_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                    {* https://api.staging.uptain.eu/payment *}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='oyst'}
            </button>
        </div>
    </div>


    <div class="panel" class="oyst_fieldset">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='Enable catalog feature:' mod='oyst'}
        </div>
        <div class="oyst-admin-tab">
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Set your Oyst API key:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="text" id="FC_OYST_API_CATALOG_KEY" name="FC_OYST_API_CATALOG_KEY" value="{$oyst.FC_OYST_API_CATALOG_KEY|escape:'htmlall':'UTF-8'}" />
                    <p class="help-block">{l s='You need this key to use export your catalog and import orders' mod='oyst'}</p>
                    {if isset($oyst.oyst_catalog_connection_test.result)}
                        {if $oyst.oyst_catalog_connection_test.result}
                            <div class="alert alert-success">{l s='Your key is valid!' mod='oyst'}</div>
                        {else}
                            <div class="alert alert-danger">
                                {l s='Your key seems invalid!' mod='oyst'}
                                <br>
                                <input type="checkbox" id="oyst_catalog_connection_debug" name="oyst_catalog_connection_debug" value="1"{if $smarty.post.oyst_catalog_connection_debug} checked="checked"{/if} /> Debug
                                {if isset($smarty.post.oyst_catalog_connection_debug) && $smarty.post.oyst_catalog_connection_debug}
                                    <br><pre>{$oyst.oyst_catalog_connection_test.values|print_r}</pre>
                                {/if}
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Enable export catalog feature:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="checkbox" id="FC_OYST_CATALOG_EXPORT_FEATURE" name="FC_OYST_CATALOG_EXPORT_FEATURE" value="1"{if $oyst.FC_OYST_CATALOG_EXPORT_FEATURE} checked="checked"{/if} />
                    <p class="help-block">{l s='Export your catalog to Oyst to increase the number of orders!' mod='oyst'}</p>
                </div>
            </div>
            <div class="form-group clearfix">
                <label class="control-label col-lg-3 ">{l s='Set the Oyst catalog endpoint:' mod='oyst'}</label>
                <div class="col-lg-9">
                    <input type="text" id="FC_OYST_API_CATALOG_ENDPOINT" name="FC_OYST_API_CATALOG_ENDPOINT" value="{$oyst.FC_OYST_API_CATALOG_ENDPOINT|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="module_form_submit_btn" name="submitOystConfiguration" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='oyst'}
            </button>
        </div>

    </div>
</form>

{/if}
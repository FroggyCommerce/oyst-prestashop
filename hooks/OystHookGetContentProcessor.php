<?php
/**
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
 * @license   GNU GENERAL PUBLIC LICENSE
 */

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystHookGetContentProcessor extends FroggyHookProcessor
{
    public $configuration_result = '';
    public $configurations = array(

        'FC_OYST_PAYMENT_FEATURE' => 'int',
        'FC_OYST_API_PAYMENT_KEY' => 'string',
        'FC_OYST_API_PAYMENT_ENDPOINT' => 'string',

        'FC_OYST_CATALOG_FEATURE' => 'int',
        'FC_OYST_API_CATALOG_KEY' => 'string',
        'FC_OYST_API_CATALOG_ENDPOINT' => 'string',
    );

    public function init()
    {
        if (Configuration::get('FC_OYST_HASH_KEY') == '') {
            Configuration::updateValue('FC_OYST_HASH_KEY', md5(rand()._RIJNDAEL_IV_).'-'.date('YmdHis'));
        }
    }

    public function saveModuleConfiguration()
    {
        if (Tools::isSubmit('submitOystConfiguration')) {
            foreach ($this->configurations as $conf => $format) {
                if (is_array($format)) {
                    $value = '';
                    if ($format['type'] == 'multiple') {
                        $values = Tools::getIsset($format['field']) ? Tools::getValue($format['field']) : '';
                        if (is_array($values)) {
                            $values = array_map('intval', $values);
                            $value = implode(',', $values);
                        }
                    }
                } else {
                    $value = Tools::getValue($conf);
                    if ($format == 'int') {
                        $value = (int)$value;
                    } else if ($format == 'float') {
                        $value = (float)$value;
                    }
                }
                Configuration::updateValue($conf, $value);
            }
            $this->configuration_result = 'ok';
        }
    }

    public function displayModuleConfiguration()
    {
        $assign = array();
        $assign['module_dir'] = $this->path;
        foreach ($this->configurations as $conf => $format) {
            $assign[$conf] = Configuration::get($conf);
        }

        $assign['result'] = $this->configuration_result;
        $assign['ps_version'] = Tools::substr(_PS_VERSION_, 0, 3);
        $assign['module_version'] = $this->module->version;

        $assign['allow_url_fopen_check'] = ini_get('allow_url_fopen');
        $assign['curl_check'] = function_exists('curl_version');

        $assign['payment_notification_url'] = $this->context->link->getModuleLink('oyst', 'paymentNotification').'?key='.Configuration::get('FC_OYST_HASH_KEY');
        $assign['notification_url'] = $this->context->link->getModuleLink('oyst', 'notification').'?key='.Configuration::get('FC_OYST_HASH_KEY');

        if (Configuration::get('FC_OYST_API_PAYMENT_KEY') != '') {
            $oyst_api = new OystSDK();
            $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_PAYMENT_KEY'));
            $assign['oyst_payment_connection_test'] = $oyst_api->testRequest();
        }

        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'getContent.tpl');
    }

    public function run()
    {
        $this->init();
        $this->saveModuleConfiguration();
        return $this->displayModuleConfiguration();
    }
}

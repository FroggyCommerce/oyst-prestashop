<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't buy this module on Froggy-Commerce.com, ThemeForest.net
 * or Addons.PrestaShop.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod
 * @license   Unauthorized copying of this file, via any medium is strictly prohibited
 */

/*
 * Include Oyst SDK
 */
if (!class_exists('OystSDK', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystSDK.php';
}

class OystHookGetContentProcessor extends FroggyHookProcessor
{
    public $configuration_result = '';
    public $configurations = array(

        'FC_OYST_PAYMENT_FEATURE' => 'int',
        'FC_OYST_CATALOG_EXPORT_FEATURE' => 'int',
        'FC_OYST_IMPORT_ORDERS_FEATURE' => 'int',

        'FC_OYST_API_KEY' => 'string',
        'FC_OYST_API_PAYMENT_ENDPOINT' => 'string',
        'FC_OYST_API_EXPORT_ENDPOINT' => 'string',

        'FC_OYST_EXPORT_CATS' => array('type' => 'multiple', 'field' => 'categoryBox'),
    );

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

        $assign['allow_url_fopen_check'] = ini_get('allow_url_fopen');
        $assign['curl_check'] = function_exists('curl_version');

        if (Configuration::get('FC_OYST_PAYMENT_FEATURE') == 1) {
            $oyst_api = new OystSDK();
            $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $assign['oyst_connection_test'] = $oyst_api->testRequest();
        }

        $this->smarty->assign($this->module->name, $assign);

        return $this->module->fcdisplay(__FILE__, 'getContent.tpl');
    }

    public function run()
    {
        $this->saveModuleConfiguration();
        return $this->displayModuleConfiguration();
    }
}

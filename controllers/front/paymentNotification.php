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
 * @license GNU GENERAL PUBLIC LICENSE
 */

class OystPaymentNotificationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        if (Tools::getValue('key') != Configuration::get('FC_OYST_HASH_KEY')) {
            die('Secure key is invalid');
        }

        file_put_contents(dirname(__FILE__).'/../../logs/log-payment.txt', "<!----Start notification-->", FILE_APPEND);
        $data = date('Y-m-d H:i:s')."\n".var_export($_GET, true)."\n".var_export($_POST, true)."\n\n";
        $data .= var_export(file_get_contents('php://input', true));
        file_put_contents(dirname(__FILE__).'/../../logs/log-payment.txt', $data, FILE_APPEND);
        file_put_contents(dirname(__FILE__).'/../../logs/log-payment.txt', "<!----End notification-->", FILE_APPEND);
        die('OK!');
    }
}

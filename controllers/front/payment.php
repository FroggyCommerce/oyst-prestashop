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

/*
 * Include Oyst SDK
 */
if (!class_exists('OystSDK', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystSDK.php';
}

class OystPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $urls = array(
            'notification' => $this->context->link->getModuleLink('oyst', 'notification'),
            'cancel' => $this->context->link->getModuleLink('oyst', 'cancel'),
            'error' => $this->context->link->getModuleLink('oyst', 'error'),
            'return' => $this->context->link->getModuleLink('oyst', 'return'),
        );
        $currency = new CurrencyCore($this->context->cart->id_currency);
        $total_amount = (int)ceil($this->context->cart->getOrderTotal() * 100);
        $customer_email = $this->context->customer->email;

        $oyst_api = new OystSDK();
        $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
        $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
        $result = $oyst_api->paymentRequest($total_amount, $currency->iso_code, $this->context->cart->id, $urls, true, $customer_email);

        $result = json_decode($result, true);
        if (isset($result['url']) && !empty($result['url'])) {
            header('location:'.$result['url']);
            exit;
        }

        die('An error occured with Oyst Payment. Please contact support.');
    }
}

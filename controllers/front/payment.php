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
        // Build hash
        $cart_hash = md5(json_encode(array($this->context->cart->id, $this->context->cart->nbProducts())));

        // Build urls and amount
        $glue = '&';
        if (Configuration::get('PS_REWRITING_SETTINGS') == 1) {
            $glue = '?';
        }
        $urls = array(
            'notification' => $this->context->link->getModuleLink('oyst', 'paymentNotification').$glue.'key='.Configuration::get('FC_OYST_HASH_KEY').'&ch='.$cart_hash,
            'cancel' => $this->context->link->getModuleLink('oyst', 'paymentCancel'),
            'error' => $this->context->link->getModuleLink('oyst', 'paymentError'),
            'return' => $this->context->link->getModuleLink('oyst', 'paymentReturn'),
        );
        $currency = new CurrencyCore($this->context->cart->id_currency);
        $total_amount = (int)ceil($this->context->cart->getOrderTotal() * 100);

        // Build user variables
        $addresses_oyst = array();
        $addresses = array(
            new Address($this->context->cart->id_address_invoice),
            new Address($this->context->cart->id_address_delivery),
        );
        $main_phone = '';
        if (isset($addresses[0]->phone) && !empty($addresses[0]->phone)) {
            $main_phone = $addresses[0]->phone_mobile;
        }
        if (empty($main_phone) && isset($addresses[0]->phone) && !empty($addresses[0]->phone)) {
            $main_phone = $addresses[0]->phone;
        }

        foreach ($addresses as $ka => $address) {
            $country = new Country($address->id_country, $this->context->language->id);
            $addresses_oyst[] = array(
                'first_name' => $address->firstname,
                'last_name' => $address->lastname,
                'country' => $country->name,
                'city' => $address->city,
                'label' => $address->alias,
                'postcode' => $address->postcode,
                'street' => $address->address1,
            );
        }
        $user = array(
            'addresses' => $addresses_oyst,
            'email' => $this->context->customer->email,
            'first_name' => $this->context->customer->firstname,
            'language' => $this->context->language->iso_code,
            'last_name' => $this->context->customer->lastname,
            'phone' => $main_phone,
        );

        // Make Oyst api call
        $oyst_api = new OystSDK();
        $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_PAYMENT_ENDPOINT'));
        $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
        $result = $oyst_api->paymentRequest($total_amount, $currency->iso_code, $this->context->cart->id, $urls, true, $user);

        // Redirect to payment
        $result = json_decode($result, true);
        if (isset($result['url']) && !empty($result['url'])) {
            header('location:'.$result['url']);
            exit;
        }

        // Redirect to error page, save data in
        $this->context->cookie->oyst_debug = json_encode(array_merge(
            $user,
            $result,
            array($total_amount, $currency->iso_code, $this->context->cart->id, $urls, true))
        );
        header('location:'.$urls['error']);
        exit;
    }
}

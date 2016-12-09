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

class OystSDK
{
    private $_api_key;
    private $_api_payment_endpoint;
    private $_api_catalog_endpoint;

    public function testRequest()
    {
        $data = array(
            'amount' => array(
                'value' => 100,
                'currency' => 'EUR',
            ),
            'is_3d' => true,
            'label' => 'ConnectionTest',
            'notification_url' => 'http://localhost.test',
            'order_id' => 'ConnectionTest',
            'redirects' => array(
                'cancel_url' => 'http://localhost.test',
                'error_url' => 'http://localhost.test',
                'return_url' => 'http://localhost.test',
            ),
            'user' => array(
                'addresses' => array(),
                'billing_addresses' => array(),
                'email' => Configuration::get('PS_SHOP_EMAIL'),
                'first_name' => 'Test',
                'language' => 'fr',
                'last_name' => 'Test',
                'phone' => '0100000000',
            ),
        );

        $result = $this->_apiPostRequest($this->getApiPaymentEndpoint().'/payments', $data);
        $result = Tools::jsonDecode($result, true);
        if (isset($result['url']) && !empty($result['url'])) {
            return true;
        }

        return false;
    }

    public function paymentRequest($amount, $currency, $id_cart, $urls, $is_3d, $user)
    {
        $data = array(
            'amount' => array(
                'value' => (float)$amount,
                'currency' => (string)$currency,
            ),
            'is_3d' => $is_3d,
            'notification_url' => $urls['notification'],
            'order_id' => (string)$id_cart,
            'redirects' => array(
                'cancel_url' => $urls['cancel'],
                'error_url' => $urls['error'],
                'return_url' => $urls['return'],
            ),
            'user' => $user,
        );
        return $this->_apiPostRequest($this->getApiPaymentEndpoint().'/payments', $data);
    }

    public function productPostRequest($products)
    {
        $data = array('products' => $products);
        return $this->_apiPostRequest($this->getApiPaymentEndpoint(), $data);
    }

    private function _apiPostRequest($endpoint, $data)
    {
        $data_string = Tools::jsonEncode($data);

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: '.Tools::strlen($data_string),
            'User-Agent: OystPrestashop/'._PS_OYST_VERSION_.' (Prestashop '._PS_VERSION_.')',
            'Authorization: bearer '.$this->getApiKey(),
        ));
        curl_setopt($ch, CURLOPT_TIMEOUT, 2000);

        return curl_exec($ch);
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->_api_key;
    }

    /**
     * @param mixed $api_key
     * @return OystLib
     */
    public function setApiKey($api_key)
    {
        $this->_api_key = $api_key;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiPaymentEndpoint()
    {
        return $this->_api_payment_endpoint;
    }

    /**
     * @param mixed $api_payment_endpoint
     * @return OystLib
     */
    public function setApiPaymentEndpoint($api_payment_endpoint)
    {
        $this->_api_payment_endpoint = $api_payment_endpoint;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiCatalogEndpoint()
    {
        return $this->_api_catalog_endpoint;
    }

    /**
     * @param mixed $api_catalog_endpoint
     * @return OystLib
     */
    public function setApiCatalogEndpoint($api_catalog_endpoint)
    {
        $this->_api_catalog_endpoint = $api_catalog_endpoint;
        return $this;
    }
}

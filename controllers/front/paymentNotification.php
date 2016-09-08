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

        $event_data = trim(str_replace("'", '', file_get_contents('php://input')));
        $event_data = json_decode($event_data, true);

        foreach ($event_data['notification_items'] as $notification_item) {
            $insert = array(
                'id_order' => 0,
                'id_cart' => (int)$notification_item['order_id'],
                'payment_id' =>  pSQL($notification_item['payment_id']),
                'event_code' =>  pSQL($notification_item['event_code']),
                'event_data' => pSQL(json_encode($event_data)),
                'date_event' => pSQL(substr(str_replace('T', '', $notification_item['event_date']), 0, 19)),
                'date_add' => date('Y-m-d H:i:s'),
            );
            if (Db::getInstance()->insert('oyst_payment_notification', $insert)) {
                $this->convertCartToOrder($notification_item);
            }
        }

        die(json_encode(array('result' => 'ok')));
    }

    public function convertCartToOrder($payment_notification)
    {
        // Load cart
        $cart = new Cart((int)$payment_notification['order_id']);

        // Build hash and load cart data
        // @Todo add cart hash in payment notification URL
        $cart_details = $cart->getSummaryDetails(null, true);
        $cart_hash = sha1(serialize($cart->nbProducts()));
        $url_hash = $cart_hash;

        // Load data in context
        $this->context->cart = $cart;
        $address = new Address((int) $cart->id_address_invoice);
        $this->context->country = new Country((int) $address->id_country);
        $this->context->customer = new Customer((int) $cart->id_customer);
        $this->context->language = new Language((int) $cart->id_lang);
        $this->context->currency = new Currency((int) $cart->id_currency);

        // Load shop in context
        if (isset($cart->id_shop)) {
            $this->context->shop = new Shop($cart->id_shop);
        }

        if ($payment_notification['success'] == 'true') {

            $message = null;
            $transaction = array(
                'id_transaction' => pSQL($payment_notification['payment_id']),
                'transaction_id' => pSQL($payment_notification['payment_id']),
                'total_paid' => (float)($payment_notification['amount']['value'] / 100),
                'currency' => pSQL($payment_notification['amount']['currency']),
                'payment_date' => pSQL(substr(str_replace('T', '', $payment_notification['event_date']), 0, 19)),
                'payment_status' => pSQL($payment_notification['success']),
            );

            if ($transaction['total_paid'] != $cart->getOrderTotal()) {
                $payment_status = (int) Configuration::get('PS_OS_ERROR');
                $message = $this->l('Price paid on Oyst is not the same that on PrestaShop.').'<br />';
            } elseif ($url_hash != $cart_hash) {
                $payment_status = (int) Configuration::get('PS_OS_ERROR');
                $message = $this->l('Cart changed, please retry.').'<br />';
            } else {
                $payment_status = (int) Configuration::get('PS_OS_PAYMENT');
                $message = $this->l('Payment accepted.').'<br />';
            }


            if (_PS_VERSION_ < '1.5') {
                $shop = null;
            } else {
                $shop_id = $this->context->shop->id;
                $shop = new Shop($shop_id);
            }

            $this->module->validateOrder($cart->id, $payment_status, $transaction['total_paid'], $this->module->displayName, $message, $transaction, $cart->id_currency, false, $this->context->customer->secure_key, $shop);
        }
    }

    public function logNotification($debug) {
        $data = "<!----Start notification-->\n";
        $data .= "Response:\n".var_export(file_get_contents('php://input'), true)."/n";
        $data .= "Debug:\n".var_export($debug, true)."/n";
        $data .= "<!----End notification-->\n";
        $this->log($data);
    }

    public function log($data) {
        if (is_array($data)) {
            $data = var_export($data, true);
        }
        file_put_contents(dirname(__FILE__).'/../../logs/log-payment.txt', '['.date('Y-m-d H:i:s').'] '.$data."\n", FILE_APPEND);
    }

}



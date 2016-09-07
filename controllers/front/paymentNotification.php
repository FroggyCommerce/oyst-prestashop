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
                $this->logNotification('Accepted');
            }
            $this->logNotification($insert);
        }

        die('OK!');
    }

    public function logNotification($debug) {
        $data = "<!----Start notification-->\n";
        $data .= "Date:\n".date('Y-m-d H:i:s')."\n";
        $data .= "Response:\n".var_export(file_get_contents('php://input'), true)."/n";
        $data .= "Debug:\n".var_export($debug, true)."/n";
        $data .= "<!----End notification-->\n\n";
        file_put_contents(dirname(__FILE__).'/../../logs/log-payment.txt', $data, FILE_APPEND);
    }
}



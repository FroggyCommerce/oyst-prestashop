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
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__).'/index.php';

/**
 * Class Controller for a Backward compatibility
 * Allow to use method declared in 1.5
 */

class FroggyControllerBackwardModule
{
    /**
     * @param $js_uri
     * @return void
     */
    public function addJS($js_uri)
    {
        Tools::addJS($js_uri);
    }

    /**
     * @param $css_uri
     * @param string $css_media_type
     * @return void
     */
    public function addCSS($css_uri, $css_media_type = 'all')
    {
        Tools::addCSS($css_uri, $css_media_type);
    }

    public function addJquery()
    {
        if (_PS_VERSION_ < '1.5') {
            $this->addJS(_PS_JS_DIR_.'jquery/jquery-1.4.4.min.js');
        } elseif (_PS_VERSION_ >= '1.5') {
            $this->addJS(_PS_JS_DIR_.'jquery/jquery-1.7.2.min.js');
        }
    }
}

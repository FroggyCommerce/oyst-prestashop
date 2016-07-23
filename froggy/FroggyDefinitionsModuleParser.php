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

class FroggyDefinitionsModuleParser
{

    /**
     * @var
     */
    protected $filepath;

    /**
     * @param $filepath
     */
    public function __construct($filepath)
    {
        if (!file_exists($filepath)) {
            throw new Exception('File given to definitions parser does not exists : '.$this->filepath);
        }
        $this->filepath = $filepath;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function parse()
    {
        $definitions = Tools::jsonDecode(Tools::file_get_contents($this->filepath), true);

        // On old PS 1.4 version, jsonDecode return an object instead of array
        if (is_object($definitions)) {
            $definitions = (array)$definitions;
        }

        if (is_null($definitions)) {
            throw new Exception('Definition parser cannot decode file : '.$this->filepath);
        }

        return $definitions;
    }
}

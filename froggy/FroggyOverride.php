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

class FroggyOverride
{
    public $name;

    /**
     * Constructor
     *
     * @param string $name Module unique name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function run()
    {
        // Install overrides
        try {
            return $this->installOverrides();
        } catch (Exception $e) {
            $this->uninstallOverrides();
            return false;
        }
    }

    /**
     * Install overrides files for the module
     *
     * @return bool
     */
    public function installOverrides()
    {
        if (!is_dir($this->getLocalPath().'override')) {
            return true;
        }

        // Get files list to override and check if we there will be a problem with one of the override
        $overrides = FroggyOverride::scandir($this->getLocalPath().'override', 'php', '', true);
        foreach ($overrides as $ko => $vo) {
            $core_classpath = $this->getClassPath(basename($vo, '.php'), true);
            $override_classpath = $this->getClassPath(basename($vo, '.php'), false);
            if (empty($core_classpath)) {
                unset($overrides[$ko]);
            } else if (file_exists($override_classpath)) {
                return false;
            } else if (!is_writable(dirname($override_classpath))) {
                return false;
            }
        }

        // Add overrides
        foreach ($overrides as $file) {
            $this->addOverride($file);
        }

        return true;
    }

    /**
     * Uninstall overrides files for the module
     *
     * @return bool
     */
    public function uninstallOverrides()
    {
        if (!is_dir($this->getLocalPath().'override')) {
            return true;
        }

        // Get files list to override and check if we there will be a problem with one of the override
        $overrides = FroggyOverride::scandir($this->getLocalPath().'override', 'php', '', true);
        foreach ($overrides as $ko => $vo) {
            $core_classpath = $this->getClassPath(basename($vo, '.php'), true);
            if (empty($core_classpath)) {
                unset($overrides[$ko]);
            }
        }

        // Add overrides
        foreach ($overrides as $file) {
            $this->removeOverride($file);
        }

        return true;
    }

    /**
     * Add all methods in a module override to the override class
     *
     * @param string $classname
     * @return bool
     */
    public function addOverride($file)
    {
        $ps_version = str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3));
        $override_src = $this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$file;
        $override_src_version = str_replace('.php', '.'.$ps_version.'.php', $override_src);
        if (file_exists($override_src_version)) {
            $override_src = $override_src_version;
        }
        $override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.basename($file, '.php').'.php';
        if (strpos($file, 'controllers/')) {
            $override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.basename($file, '.php').'.php';
        }
        copy($override_src, $override_dest);
        return true;
    }

    /**
     * Remove all methods in a module override from the override class
     *
     * @param string $classname
     * @return bool
     */
    public function removeOverride($file)
    {
        $ps_version = str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3));
        $override_src = $this->getLocalPath().'override'.DIRECTORY_SEPARATOR.$file;
        $override_src_version = str_replace('.php', '.'.$ps_version.'.php', $override_src);
        if (file_exists($override_src_version)) {
            $override_src = $override_src_version;
        }
        $override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.basename($file, '.php').'.php';
        if (strpos($file, 'controllers/')) {
            $override_dest = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'override'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.basename($file, '.php').'.php';
        }

        $override_src_base64 = md5(Tools::file_get_contents($override_src));
        $override_dest_base64 = md5(Tools::file_get_contents($override_dest));
        if ($override_src_base64 == $override_dest_base64) {
            @unlink($override_dest);
        }

        return true;
    }

    /**
     * Get local path for module
     *
     * @return string
     */
    public function getLocalPath()
    {
        return _PS_MODULE_DIR_.$this->name.'/';
    }

    public function getClassPath($classname, $core = true)
    {
        if ($classname == 'index') {
            return '';
        }

        if ($core == true) {
            if (file_exists(dirname(__FILE__).'/../../../classes/'.$classname.'.php')) {
                return dirname(__FILE__).'/../../../classes/'.$classname.'.php';
            }
            if (file_exists(dirname(__FILE__).'/../../../controllers/'.$classname.'.php')) {
                return dirname(__FILE__).'/../../../controllers/'.$classname.'.php';
            }
        } else {
            if (file_exists(dirname(__FILE__).'/../../../classes/'.$classname.'.php')) {
                return dirname(__FILE__).'/../../../override/classes/'.$classname.'.php';
            }
            if (file_exists(dirname(__FILE__).'/../../../controllers/'.$classname.'.php')) {
                return dirname(__FILE__).'/../../../override/controllers/'.$classname.'.php';
            }
        }
        return '';
    }

    /**
     * @params string $path Path to scan
     * @params string $ext Extention to filter files
     * @params string $dir Add this to prefix output for example /path/dir/*
     *
     * @return array List of file found
     * @since 1.5.0
     */
    public static function scandir($path, $ext = 'php', $dir = '', $recursive = false)
    {
        $path = rtrim(rtrim($path, '\\'), '/').'/';
        $real_path = rtrim(rtrim($path.$dir, '\\'), '/').'/';
        $files = scandir($real_path);
        if (!$files) {
            return array();
        }

        $filtered_files = array();

        $real_ext = false;
        if (!empty($ext)) {
            $real_ext = '.'.$ext;
        }
        $real_ext_length = Tools::strlen($real_ext);

        $subdir = ($dir) ? $dir.'/' : '';
        foreach ($files as $file) {
            if (!$real_ext || (strpos($file, $real_ext) && strpos($file, $real_ext) == (Tools::strlen($file) - $real_ext_length))) {
                $filtered_files[] = $subdir.$file;
            }

            if ($recursive && $file[0] != '.' && is_dir($real_path.$file)) {
                foreach (FroggyOverride::scandir($path, $ext, $subdir.$file, $recursive) as $subfile) {
                    $filtered_files[] = $subfile;
                }
            }
        }
        return $filtered_files;
    }
}

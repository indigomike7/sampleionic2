<?php

/**
 * @package mfox
 * @since   3.09
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author     Nam Nguyen
 * @package    mfox
 * @subpackage mfox.service
 * @since      3.09
 */
class Mfox_Service_Token extends Phpfox_Service
{
    /**
     * @var int Token length.
     */
    CONST TOKEN_LEN = 24;

    /**
     * Constructor.
     */
    function __construct()
    {
        $this->_sTable = Phpfox::getT('mfox_theme');
    }

    public function css($filename)
    {

        $buildNumber = Phpfox::getParam('mfox.theme_number');

        if (!$buildNumber) {
            $buildNumber = "0";
        }


        header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $basePath = Phpfox::getParam('core.path');

        $url = $basePath . '/file/mfox/css/' . $filename . '_' . $buildNumber . '.css?v=' . time();


        header('location: ' . $url . '?v=' . time());
        exit;
    }

    /**
     * redirect to target stylesheets file.
     */
    public function iphone_css()
    {
        $this->css('iphone');
    }

    /**
     * redirect to target stylesheets file.
     */
    public function ipad_css()
    {
        $this->css('ipad');
    }

    public function android_css()
    {
        $this->css('android');
    }

    public function test_css()
    {
        return $this->buildCss(array(), time());
    }

    /**
     * @param array  $variables
     * @param string $buildNumber
     *
     * @throws InvalidArgumentException
     * @return array
     */
    public function buildCss($variables, $buildNumber)
    {
        $compileOptions = array('compress' => true);

        include_once PHPFOX_DIR_MODULE . 'mfox/inc/scss.inc.php';

        $themeDir = PHPFOX_DIR_MODULE . 'mfox/themes';

        $themes = array(
            'iphone'  => '/iphone/ionic.scss',
//            'ipad'    => '/ipad/ionic.scss',
//            'android' => '/android/ionic.scss',
        );

        $response = array();

        foreach ($themes as $theme => $filename) {
            try {

                $scss = new scssc($compileOptions);

                $filename = $themeDir . $filename;

                $scss->setImportPaths(array(dirname($filename)));

                $scss->setVariables($variables);

                $scss->setFormatter('scss_formatter_compressed');
//
                if (!file_exists($filename)) {
                    Phpfox_Error::set("File $filename not found!");
                }

                $scssContent = file_get_contents($filename);

                if (!$scssContent) {
                    Phpfox_Error::set("File $filename is empty!");
                }

                $cssContent = $scss->compile($scssContent);

                if (!$cssContent) {
                    Phpfox_Error::set("Could not compile style empty!");
                }


                $targetFile = 'mfox/css/' . $theme . '_' . $buildNumber . '.css';

                $targetPath = PHPFOX_DIR_FILE . $targetFile;

                $dir = dirname($targetPath);

                if (!is_dir($dir)) {
                    if (!mkdir($dir, 0777, 1)) {
                        exit("Could not create directory  " . dirname($targetPath));
                    }
                }


                $fp = fopen($targetPath, 'w');

                if ($fp) {
                    fwrite($fp, $cssContent);
                    fclose($fp);
                } else {
                    Phpfox_Error::set('Could not write result to ' . $targetPath);
                }

            } catch (Exception $ex) {
                Phpfox_Error::set($ex->getMessage());
            }
        }

        return Phpfox_Error::get();
    }
}
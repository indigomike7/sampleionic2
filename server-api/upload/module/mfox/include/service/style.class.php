<?php
/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author     ductc@younetco.com
 * @package    mfox
 * @subpackage mfox.service
 * @version    3.01
 * @since      June 5, 2013
 * @link       Mfox Api v3.0
 */
class Mfox_Service_Style extends Phpfox_Service
{
    /**
     * Constructor.
     */
    function __construct()
    {
        $this->_sTable = Phpfox::getT('mfox_style');
    }

    /**
     * Add new style.
     *
     * @param string $sName
     * @param bool   $isPublish
     * @param array  $aVariables
     *
     * @return int
     */
    function add($sName, $isPublish, $aVariables)
    {
        $buildNumber = time();
        $isPublish = !empty($isPublish) ? $isPublish : 0;

        $this->buildCss($aVariables, $buildNumber);

        $iId = $this->database()->insert($this->_sTable, array(
            'name'         => $sName,
            'is_publish'   => $isPublish,
            'data'         => serialize($aVariables),
            'build_number' => $buildNumber,
            'time_stamp'   => PHPFOX_TIME,
        ));

        if ($isPublish) {
            $this->publish($iId);
        }

        return $iId;
    }

    /**
     * Edit style.
     *
     * @param int    $iStyleId
     * @param string $name
     * @param bool   $isPublish
     * @param array  $aVariables
     *
     * @return bool
     */
    function edit($iStyleId, $name, $isPublish, $aVariables)
    {
        $buildNumber = time();
        $isPublish = !empty($isPublish) ? $isPublish : 0;

        $this->buildCss($aVariables, $buildNumber);

        $rs = $this->database()->update($this->_sTable, array(
            'name'         => (string)$name,
            'is_publish'   => $isPublish,
            'build_number' => $buildNumber,
            'data'         => serialize($aVariables)
        ), 'style_id = ' . (int)$iStyleId);

        if ($isPublish) {
            $this->publish($iStyleId);
        }

        return $rs;
    }

    /**
     * remove style id
     *
     * @param int $iStyleId
     *
     * @return bool
     */
    function remove($iStyleId)
    {
        return $this->database()->delete($this->_sTable, 'style_id=' . $iStyleId);
    }

    /**
     * Publish style id
     *
     * @param int $iStyleId
     */
    function publish($iStyleId)
    {
        $this->database()->update($this->_sTable, array('is_publish' => 0), 'style_id <>' . $iStyleId);
        $this->database()->update($this->_sTable, array('is_publish' => 1), 'style_id = ' . $iStyleId);
    }

    /**
     * Reset style.
     */
    function resetStyle()
    {
        $this->database()->update($this->_sTable, array('is_publish' => 0));
    }

    /**
     * Get the default style.
     *
     * @return array
     */
    function getDefaultStyles()
    {
        Phpfox::getPhrase("mfox.positive");

        return array(
            'positive' => '#01a0db',
        );
    }

    /**
     * @return string
     */
    function getStylesPattern()
    {
        $file = PHPFOX_DIR_MODULE . '/mfox/static/css/custom.css';

        return file_get_contents($file);
    }

    /**
     * Get pattern merged styles
     *
     * @param array $aMergedStyles
     *
     * @return string
     */
    function mergedStyles($aMergedStyles)
    {
        $org = $this->getStylesPattern();

        $reg = array();

        foreach ($aMergedStyles as $name => $value) {
            $name = '{' . $name . '}';
            $reg[ $name ] = $value;
        }

        return strtr($org, $reg);
    }

    /**
     * @param $iStyleId
     *
     * @return array|null
     */
    public function getRow($iStyleId)
    {
        return $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where('style_id = ' . (int)$iStyleId)
            ->execute('getRow');
    }

    /**
     * Get for edit.
     *
     * @param int $iStyleId
     *
     * @return array
     */
    public function getForEdit($iStyleId)
    {
        /**
         * @var array
         */
        $aRow = $this->getRow($iStyleId);
        /**
         * @var array
         */
        $aStyles = array();
        if ($aRow) {
            $aStyles = unserialize($aRow['data']);
        }

        return $aStyles;
    }

    /**
     * Get custom CSS.
     *
     * @return string
     */
    function _getCustomCss()
    {
        $aRow = $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where('is_publish=1')
            ->execute('getRow');
        if ($aRow) {
            $aStyles = unserialize($aRow['data']);
        } else {
            // $aStyles = $this->getDefaultStyles();
            return '/* no custom style */';
        }

        return $this->mergedStyles($aStyles);
    }

    /**
     * Get custom CSS.
     *
     * @return string
     */
    function getCustomCss()
    {
        return $this->_getCustomCss();
    }

    /**
     * Get style list.
     *
     * @param array  $aConditions
     * @param string $sSort
     * @param string $iPage
     * @param string $iLimit
     *
     * @return array
     */
    public function get($aConditions, $sSort = 'style.time_stamp DESC', $iPage = '', $iLimit = '')
    {
        /**
         * @var int
         */
        $iCnt = $this->database()->select('COUNT(style.style_id)')
            ->from(Phpfox::getT('mfox_style'), 'style')
            ->where($aConditions)
            ->order($sSort)
            ->execute('getSlaveField');
        /**
         * @var array
         */
        $aItems = array();
        if ($iCnt) {
            $aItems = $this->database()->select('style.*')
                ->from(Phpfox::getT('mfox_style'), 'style')
                ->where($aConditions)
                ->order($sSort)
                ->limit($iPage, $iLimit, $iCnt)
                ->execute('getSlaveRows');
        }

        return array($iCnt, $aItems);
    }

    /**
     * Update style status. For ajax.
     *
     * @param int $iStyleId
     * @param int $iAction
     */
    public function updateStyleStatus($iStyleId, $iAction)
    {
        $this->database()->update($this->_sTable, array('is_publish' => 0), 'style_id <>' . $iStyleId);
        $this->database()->update($this->_sTable, array('is_publish' => $iAction), 'style_id = ' . $iStyleId);
    }

    /**
     * Delete multi-styles.
     *
     * @param array $aStyleIds
     *
     * @return boolean
     */
    public function deleteStyles($aStyleIds)
    {
        foreach ($aStyleIds as $iStyleId) {
            $this->remove((int)$iStyleId);
        }

        return true;
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
            'ipad'    => '/ipad/ionic.scss',
            'android' => '/android/ionic.scss',
        );

        $response = array();

        foreach ($themes as $theme => $filename) {
            try {

                $scss = new scssc($compileOptions);

                $filename = $themeDir . $filename;

                $scss->setImportPaths(array(dirname($filename)));

                $scss->setVariables($variables);

//              $scss->setFormatter('scss_formatter_compressed');

                if (!file_exists($filename)) {
                    exit("File $filename not found!");
                }

                $scssContent = file_get_contents($filename);

                if (!$scssContent) {
                    exit("File $filename is empty!");
                }

                $cssContent = $scss->compile($scssContent);

                if (!$cssContent) {
                    exit("Could not compile style empty!");
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
                    exit('Could not write result to ' . $targetPath);
                }

            } catch (Exception $ex) {
                exit($ex->getMessage());
            }
        }

        return Phpfox_Error::get();
    }

    /**
     * @return string
     */
    public function getDefaultBuildNumber()
    {

        $buildNumber = $this->database()
            ->select('build_number')
            ->from($this->_sTable)
            ->order('is_publish desc')
            ->limit(1)
            ->execute('getSlaveField');

        if (!$buildNumber) {
            $buildNumber = 0;
        }

        return (string)$buildNumber;

    }

    /**
     * @param $filename
     */
    public function css($filename)
    {

        $buildNumber = $this->getDefaultBuildNumber();

        if (!$buildNumber) {
            $buildNumber = 0;
        }


        header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $basePath = Phpfox::getParam('core.path');

        $url = $basePath . 'file/mfox/css/' . $filename . '_' . $buildNumber . '.css?v=' . time();


        header('location: ' . $url);
        exit;
    }
}

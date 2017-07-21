<?php

class Mfox_Component_Controller_Admincp_Styles_Index extends Phpfox_Component
{

    function process()
    {
        $bIsPublish = false;
        $sStyleName = null;

        $oService = Phpfox::getService('mfox.style');
        /**
         * @var bool
         */
        $bIsEdit = false;
        if ($iStyleId = $this->request()->getInt('id')) {
            $aDefaultStyles = $oService->getForEdit($iStyleId);
            if (!$aDefaultStyles) {
                $this->url()->send('admincp.mfox.styles.manage', null, Phpfox::getPhrase('mfox.style_is_not_valid'));
            }
            $bIsEdit = true;
            $aRow = $oService->getRow($iStyleId);

            if ($aRow) {
                $bIsPublish = $aRow['is_publish'];
                $sStyleName = $aRow['name'];
            }

        } else {
            $aDefaultStyles = $oService->getDefaultStyles();
        }

        // Post data to add or edit.
        if ($aVals = $this->request()->get('val')) {
            if ($bIsEdit) {
                if ($oService->edit($iStyleId, $aVals['name'], isset($aVals['is_publish']) ? $aVals['is_publish'] : 0, $aVals['styles'])) {
                    $this->url()->send('admincp.mfox.styles.manage', null, Phpfox::getPhrase('mfox.style_successfully_edited'));
                }
            } else {
                if ($oService->add($aVals['name'], isset($aVals['is_publish']) ? $aVals['is_publish'] : 0, $aVals['styles'])) {
                    $this->url()->send('admincp.mfox.styles.manage', null, Phpfox::getPhrase('mfox.style_successfully_added'));
                }
            }
        }
        /**
         * @var array
         */
        $aStyles = array();
        foreach ($aDefaultStyles as $name => $value) {
            $aStyles[] = array(
                'name'  => $name,
                'label' => Phpfox::getPhrase('mfox.' . $name),
                'value' => $value,
            );
        }
        $this->template()
            ->setTitle(Phpfox::getPhrase('mfox.add_custom_style'))
            ->setBreadcrumb(Phpfox::getPhrase('mfox.add_custom_style'), $this->url()->makeUrl('admincp.mfox.styles'))
            ->assign(array(
                'sStyleName' => $sStyleName,
                'bIsPublish' => $bIsPublish,
                'bIsEdit'    => $bIsEdit,
                'iStyleId'   => $iStyleId,
                'aStyles'    => $aStyles
            ));
    }

}

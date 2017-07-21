<?php

class Mfox_Component_Controller_Admincp_Styles_Manage extends Phpfox_Component {

    function process()
    {
        if ($this->request()->get('delete') && $aIds = $this->request()->getArray('id'))
        {
            Phpfox::getService('mfox.style')->deleteStyles($aIds);
        }
        /**
         * @var int
         */
        $iPage = $this->request()->getInt('page');
		/**
         * @var array
         */
		$aPages = array(100);
        /**
         * @var array
         */
		$aDisplays = array();
		foreach ($aPages as $iPageCnt)
		{
			$aDisplays[$iPageCnt] =  Phpfox::getPhrase('core.per_page', array('total' => $iPageCnt));
		}		
		/**
         * @var array
         */
		$aSorts = array(
			'time_stamp' =>  Phpfox::getPhrase('core.time')
		);
		/**
         * @var array
         */
		$aFilters = array(
			'display' => array(
				'type' => 'select',
				'options' => $aDisplays,
				'default' => '100'
			),
			'sort' => array(
				'type' => 'select',
				'options' => $aSorts,
				'default' => 'time_stamp',
				'alias' => 'style'
			),
			'sort_by' => array(
				'type' => 'select',
				'options' => array(
					'DESC' =>  Phpfox::getPhrase('core.descending'),
					'ASC' =>  Phpfox::getPhrase('core.ascending')
				),
				'default' => 'DESC'
			)
		);		
		
		$oSearch = Phpfox::getLib('search')->set(array(
				'type' => 'reports',
				'filters' => $aFilters,
				'search' => 'search'
			)
		);
		/**
         * @var int
         */
		$iLimit = $oSearch->getDisplay();
		
		list($iCnt, $aStyles) = Phpfox::getService('mfox.style')->get($oSearch->getConditions(), $oSearch->getSort(), $oSearch->getPage(), $iLimit);
		
		Phpfox::getLib('pager')->set(array('page' => $iPage, 'size' => $iLimit, 'count' => $oSearch->getSearchTotal($iCnt)));		
		
        $this->template()
                ->setTitle( Phpfox::getPhrase('mfox.manage_styles'))
                ->setBreadcrumb( Phpfox::getPhrase('mfox.manage_styles'), $this->url()->makeUrl('admincp.mfox.styles.manage'))
                ->assign(array('aStyles' => $aStyles));
    }

}

<?php
;
if (Phpfox::isModule('mfox') 
	&& isset($aVals) && isset($aVals['parent_id']) && isset($aVals['user_id']))
{
	// update is_seen = 0 in "im_alert" table 
	$aIm = Phpfox::getService('mfox.chat')->getImByParentAndUser($aVals['parent_id'], $aVals['user_id']);
	if(isset($aIm['owner_user_id'])){
		$iReceiverId = $aIm['owner_user_id'];
		Phpfox::getService('mfox.chat')->updateIsSeen(0, $iReceiverId, $aVals['parent_id']);
	}
}

?>
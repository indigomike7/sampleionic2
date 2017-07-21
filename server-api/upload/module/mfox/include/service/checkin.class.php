<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_checkin extends Phpfox_Service {
	 /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_sTable_feed = Phpfox::getT('feed');
		$this->_sTable_user_status = Phpfox::getT('user_status');
		$this->southWest = array();
		$this->northEast = array();
    }
	
	public function contains($lat, $lng)
	{
		// check latitude
		
		if (($this->southWest['lat'] > $lat) || ($lat > $this->northEast['lat'])) {
			return false;
		}
		
		// check longitude
		return $this->containsLng($lng);
	}
	
	protected function containsLng($lng)
	{
		if ($this->crossesAntimeridian()) {
			return $lng >= $this->northEast['lng'] || $lng <= $this->southWest['lng'];
		} else {
			return $this->southWest['lng'] <= $lng && $lng <= $this->northEast['lng'];
		}
	}
	
	public function crossesAntimeridian()
	{
		return $this->southWest['lng'] > $this->northEast['lng'];
	}

	private function isValidLatLng($LatLng){
		if(!isset($LatLng['lat']) || !isset($LatLng['lng']))
		{
			return false;
		}
		
		return true;
	}
	
	private function isValidLatLngBounds($LatLngBounds){
		
		if(!isset($LatLngBounds['southwest']) || !isset($LatLngBounds['northeast'])){
			return false;
		}
	
		if(!$this->isValidLatLng($LatLngBounds['southwest']) || !$this->isValidLatLng($LatLngBounds['northeast']))
			return false;
		return true;
	}
	
	private function parseFloat($LatLng){
		$LatLng['lat'] = floatval($LatLng['lat']);
		$LatLng['lng'] = floatval($LatLng['lng']);
		return $LatLng;
	}

	/*
		input
		{
			iAmountOfCheckin
			LatLngBounds
			{
				southwest
				{
					lat
					lng
				},
				northeast
				{
					lat
					lng
				}
			},
			iTimeLimit: integer (days)
		}
		
		out
		{
			fLatitude
			fLongitude
			sContent
			sLocationName
			sTime
			iUserId
			sFullName
			UserProfileImg_Url
		}
	*/
	public function get($aData){
		
		$iAmountOfCheckin = isset($aData['iAmountOfCheckin'])?$aData['iAmountOfCheckin']:5;
		$aNewCond = array();
		$is_LatLngBounds = false;
		if(isset($aData['LatLngBounds']))
		{
			$LatLngBounds = $aData['LatLngBounds'];
			
			if($this->isValidLatLngBounds($LatLngBounds))
			{
				$this->southWest = $this->parseFloat($LatLngBounds['southwest']);
				$this->northEast = $this->parseFloat($LatLngBounds['northeast']);
				$is_LatLngBounds = true;
				
			}
			
		}
		$aNewCond[] = ' and feed.type_id = "user_status" and us.location_latlng!=""';
		
		if(isset($aData['iTimeLimit']) && $aData['iTimeLimit']>0){
			$aNewCond[] = ' and feed.time_stamp > '.(PHPFOX_TIME-($aData['iTimeLimit']*24*60*60));
		}
		
		$oQuery = $this->database()->select('u.*, us.*, feed.time_stamp as "sTime", '.Phpfox::getUserField())
			->from($this->_sTable_feed, 'feed')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
			->join($this->_sTable_user_status,'us','us.status_id = feed.item_id')
            ->where($aNewCond)
			->order('feed.feed_id desc');
			
		if(!$is_LatLngBounds)
		{
			$oQuery->limit($iAmountOfCheckin);
		}
		
		$aRows = $oQuery->execute('getSlaveRows');			
		$tmps = array();
		if($is_LatLngBounds)
		{
			foreach($aRows as $key=>$aRow){
				$location_latlng = json_decode($aRow['location_latlng'], true);
				
				if($this->contains($location_latlng['latitude'],$location_latlng['longitude'])){
				
					$tmps[] = $aRow;
				}
				
				if(count($tmps)>=$iAmountOfCheckin){
					break;
				}
			}
		}			
		else
		{
			$tmps = $aRows;
		}
		
		//Parse data as expected results
		$aResults = array();
		foreach($tmps as $key=>$tmp){
			$location_latlng = json_decode($tmp['location_latlng'], true);
			$aInsert = array();
			$aInsert['fLatitude'] = $location_latlng['latitude'];
			$aInsert['fLongitude'] = $location_latlng['longitude'];
			$aInsert['sContent'] = $tmp['content'];
			$aInsert['sLocationName'] = $tmp['location_name'];
			$aInsert['iCheckinId'] = $tmp['status_id'];
			$aInsert['sTime'] = date('D, j M Y G:i:s O', $tmp['sTime']);
			$aInsert['iUserId'] = $tmp['user_id'];
			$aInsert['sFullName'] = $tmp['full_name'];
			$aInsert['iTimestamp'] = $tmp['sTime'];     
			$aInsert['UserProfileImg_Url'] = Phpfox::getService('mfox.user')->getImageUrl($tmp, '_50_square');
			$aResults[] = $aInsert;
		}
		
		return $aResults;
	}
}


?>
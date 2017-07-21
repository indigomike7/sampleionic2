<?php
;
if (Phpfox::isModule('mfox'))
{
	if ($iId > 0)
	{
		/*$body['aps'] = array(
                'alert' => $this->code->getText(),
                'sound' => 'default',
                'badge' => '1'
            );
            $message['payload'] = json_encode($body);
		*/
		Phpfox::getService('mfox.cloudmessage') -> send(array(
			'ios' => array(
				'aps' => array(
					'alert' => 'notification',
					'sound' => 'default',
					'badge' => '1'
				),
				'iId' => $iId,
				'sType' => 'notification'
			),
			'android' => array(
				'message' => 'notification',
				'iId' => $iId,
				'sType' => 'notification'
			)
		), $iOwnerUserId);

	}
	return true;
}
?>

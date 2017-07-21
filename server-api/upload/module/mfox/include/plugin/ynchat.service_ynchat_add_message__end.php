<?php

if (Phpfox::isModule('mfox'))
{
    if ($iMessageId > 0)
    {
        Phpfox::getService('mfox.ynchat')->pushNotification($aInsert);
    }
}

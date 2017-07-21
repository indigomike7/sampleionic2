<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Statistic extends Phpfox_Service {

    public function info($aData){
        $sItemType = isset($aData['sItemType']) ?   $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $bCanComment = Phpfox::getService('mfox.comment')->isAllowed($aData);

        $response  = array(
            'sItemType'=> $sItemType,
            'iItemId'=> $iItemId,
            'aComments'=>array(),
            'bCanComment'=> $bCanComment,
            'aLikes'=>array(),
            'iTotalComment'=>0,
            'iTotalLike'=>0,
            'aDislikes'=> array(),
            'bIsDisliked'=> false,
            'iTotalDislike'=> 0,
        );

        if (Phpfox::isModule('comment')){
            $response['aComments'] = Phpfox::getService('mfox.comment')->listallcomments(array_merge($aData, array('iAmountOfComment'=>3)));
            $response['iTotalComment'] =  Phpfox::getService('mfox.comment')->getCommentCount($aData, $aFeed = null);
        }

        if (Phpfox::isModule('like')){
            $inputData = Phpfox::getService('mfox.like')->changeInputData($aData);            
            if(isset($inputData['sItemType']) && isset($inputData['iItemId'])){
                $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser(
                    $inputData['sItemType']
                    , $inputData['iItemId']
                    , false
                    , 999999
                );
                $aUserLike = array();
                foreach($aLike['likes'] as $like){
                    $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
                }

                $response['iTotalLike']=  $aLike['feed_total_like'];
                $response['aLikes'] = $aUserLike;
                $response['bIsLike'] =  Phpfox::getService('mfox.like')->checkIsLiked(
                    $inputData['sItemType']
                    , $inputData['iItemId']
                    , Phpfox::getUserId()
                ); 

               // for dislike 
                $aUserDislike = array();
                $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser(
                    $inputData['sItemType']
                    , $inputData['iItemId']
                    , $bGetCount = false);
                foreach($aDislike as $dislike){
                    if(Phpfox::getUserId() ==  $dislike['user_id']){
                        $response['bIsDisliked'] = true;
                    }
                    $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
                }
                $response['iTotalDislike']=  count($aUserDislike);
                $response['aDislikes'] = $aUserDislike;
            }
        }

        return $response;
    }
}

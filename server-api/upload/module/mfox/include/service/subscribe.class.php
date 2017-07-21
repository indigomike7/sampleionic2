<?php
/**
 * Service component
 *
 * @category Mobile phpfox server api
 * @author Ly Tran <lytk@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.subscribe
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * Supported Subscribe api
 * 
 * We do not support recurring flow which is same on website
 * Admin need to create (manually) subscription and follow flow on IAP
 * After user(s) has been downgraded, user(s) needed to purchase again
 *
 * @package mfox.subscribe
 * @author Ly Tran <lytk@younetco.com>
 */
class Mfox_Service_Subscribe extends Phpfox_Service {
    /**
     * @ignore
     * Class constructor
     */
    public function __construct() {
		
    }

    public function fetch($aData){
        return $this->getSubscriptionPackages($aData);
    }

    /**
     * @ignore
     */
    public function getSubscriptionPackages($aData){    
        $result = array();

        if(Phpfox::isModule('subscribe') && Phpfox::getParam('subscribe.enable_subscription_packages')){
            if(Phpfox::isUser()){
                $aPackages = Phpfox::getService('subscribe')->getPackages();
            } else {
                $aPackages = Phpfox::getService('subscribe')->getPackages(true);
            }

            foreach ($aPackages as $key => $aItem) {
                $result[] = $this->__getSubscriptionPackageData($aItem, 'small');
            }
        }

        return $result;
    }

    /**
     * @ignore
     */
    private function __getSubscriptionPackageData($aItem, $sMoreInfo = 'large'){
    	$sSubscriptionPackageImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'subscribe.url_image',
                'file' => $aItem['image_path'],
                'suffix' => '_120',
                'return_url' => true
               )
        );	

        $sRecurringPeriod = '';
        switch ($aItem['recurring_period']) {
            case '1':
                $sRecurringPeriod =  Phpfox::getPhrase('subscribe.monthly');
                break;

            case '2':
                $sRecurringPeriod =  Phpfox::getPhrase('subscribe.quarterly');
                break;

            case '3':
                $sRecurringPeriod =  Phpfox::getPhrase('subscribe.biannualy');
                break;

            case '4':
                $sRecurringPeriod =  Phpfox::getPhrase('subscribe.annually');
                break;
            
            default:
                $sRecurringPeriod = '';
                break;
        }

        $aPrice = array();
        if(isset($aItem['price'])){
            foreach ($aItem['price'] as $key => $value) {
                $aPrice[] = array(
                    'sCost'=>$value['cost'],
                    'sCurrencyId'=>$value['currency_id'],
                    'sCurrencySymbol'=>$value['currency_symbol'],
                );
            }
        }

        $aRecurringCosts = array();
        if ($aItem['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aItem['recurring_cost']))
        {
            $aRecurringCosts = unserialize($aItem['recurring_cost']);    
            foreach ($aRecurringCosts as $sKey => $iCost)
            {
                if (Phpfox::getService('core.currency')->getDefault() == $sKey)
                {
                    $default_recurring_cost = $iCost;
                    $default_recurring_currency_id = $sKey;
                    break;
                }
            }                   
        }

        $sModuleId = 'subscribe';
        $sStoreKitPurchaseIdIphone = '';
        $sStoreKitPurchaseIdIpad = '';
        $sPlayStoreProductId = '';
        $deviceIphone = Phpfox::getService('mfox.helper')->getConst('device.support.ios');
        $deviceIpad = Phpfox::getService('mfox.helper')->getConst('device.support.ipad');
        $deviceAndroid = Phpfox::getService('mfox.helper')->getConst('device.support.android');
        $aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getAllStoreKitPurchaseByModuleId($sModuleId, $aItem['package_id']);
        foreach ($aStoreKitPurchase as $key => $value) {
            switch ($value['storekitpurchase_device']) {
                case $deviceIphone:
                    $sStoreKitPurchaseIdIphone = $value['storekitpurchase_key'];
                    break;

                case $deviceIpad:
                    $sStoreKitPurchaseIdIpad = $value['storekitpurchase_key'];                        
                    break;                        

                case $deviceAndroid:
                    $sPlayStoreProductId = $value['storekitpurchase_key'];                        
                    break;                        

                default:
                    break;
            }
        }

        $result = array(
                    'iPackageId'=>$aItem['package_id'],
                    'sTitle'=>$aItem['title'],
                    'sDescription'=>$aItem['description'],
                    'aCost'=> unserialize($aItem['cost']),
                    'bHasPackageImage'=> $aItem['image_path']?1:0,
                    'aRecurringCost'=> $aRecurringCosts,
                    'iRecurringPeriod'=>$aItem['recurring_period'],
                    'sRecurringPeriod'=>$sRecurringPeriod,
                    'iUserGroupId'=>$aItem['user_group_id'],
                    'iFailUserGroup'=>$aItem['fail_user_group'],
                    'sSubscriptionPackageImage'=>$sSubscriptionPackageImage,
                    'bIsActive' => ((int)$aItem['is_active'] > 0 ? true : false),
                    'bIsRegistration' => ((int)$aItem['is_registration'] > 0 ? true : false),
                    'bIsRequired' => ((int)$aItem['is_required'] > 0 ? true : false),
                    'bShowPrice' => ((int)$aItem['show_price'] > 0 ? true : false),
                    'iTotalActive'=>$aItem['total_active'],
                    'sDefaultCost'=>$aItem['default_cost'],
                    'sDefaultCurrencyId'=>$aItem['default_currency_id'],
                    'sCurrencySymbol'=>$aItem['currency_symbol'],
                    'sDefaultRecurringCost' => isset($default_recurring_cost) ? $default_recurring_cost : '',
                    'sDefaultRecurringCurrencyId' => isset($aItem['default_recurring_currency_id']) ? $aItem['default_recurring_currency_id'] : '',
                    'aPrice'=>$aPrice,
                    'sPlayStoreProductId'=>$sPlayStoreProductId,
                    'aStoreKitPurchaseId' => array(
                        'iphone' => $sStoreKitPurchaseIdIphone, 
                        'ipad' => $sStoreKitPurchaseIdIpad, 
                        ),
                    ); 

        switch ($sMoreInfo) {
            case 'large':
                return array_merge($result, array(
                ));
                break;
            case 'medium':
            case 'small':
                return $result;
                break;
        }
    }

    public function getPermission($aData) {
        return $this->__getPermission($aData);
    }

   /**
    * @ignore 
    */
    private function __getPermission($aParams = array()) {
        $extra = array();
        return array_merge(array(
            'bSubscribeIsRequiredOnSignUp' => Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up'),
        ), $extra);        
    }

    public function detail($aData){
        $iPackageId = isset($aData['iPackageId']) ? (int) $aData['iPackageId'] : 0;
        $aPackage = $this->getPackageByPackageId($iPackageId);
        if(!isset($aPackage['package_id'])){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.package_you_are_looking_for_either_does_not_exist_or_has_been_removed"))
            );            
        }

        return $this->__getSubscriptionPackageData($aPackage, 'large');
    }

   /**
    * @ignore 
    */
    public function getPackageByPackageId($iPackageId, $bIsForSignUp = false, $bShowAllSubscriptions = false)
    {       
        $aPackages = $this->database()->select('sp.*')
            ->from(Phpfox::getT('subscribe_package'), 'sp')
            ->where('sp.package_id = ' . (int) $iPackageId . ' AND sp.is_active = 1' . ($bIsForSignUp ? ' AND sp.is_registration = 1' : ''))
            ->execute('getRows');           
        
        foreach ($aPackages as $iKey => $aPackage)
        {           
            if (Phpfox::getUserBy('user_group_id') == $aPackage['user_group_id'] && $bShowAllSubscriptions == false)
            {               
                unset($aPackages[$iKey]);
                
                continue;
            }
            
            if (!empty($aPackage['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPackage['cost']))
            {
                $aCosts = unserialize($aPackage['cost']);

                foreach ($aCosts as $sKey => $iCost)
                {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey)
                    {
                        $aPackages[$iKey]['default_cost'] = $iCost;
                        $aPackages[$iKey]['default_currency_id'] = $sKey;
                        $aPackages[$iKey]['currency_symbol'] = Phpfox::getService('core.currency')->getSymbol($sKey);
                    }
                    else
                    {
                        if ((int) $iCost === 0)
                        {
                            continue;
                        }
                        
                        $aPackages[$iKey]['price'][$sKey]['cost'] = $iCost;
                        $aPackages[$iKey]['price'][$sKey]['currency_id'] = $sKey;
                        $aPackages[$iKey]['price'][$sKey]['currency_symbol'] = Phpfox::getService('core.currency')->getSymbol($sKey);
                    }
                }
                $aPackage = $aPackages[$iKey];
                if ($aPackage['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aPackage['recurring_cost']))
                {
                    $aRecurringCosts = unserialize($aPackage['recurring_cost']);    
                    foreach ($aRecurringCosts as $sKey => $iCost)
                    {
                        if (Phpfox::getService('core.currency')->getDefault() == $sKey)
                        {
                            $aPackages[$iKey]['default_recurring_cost'] = Phpfox::getService('api.gateway')->getPeriodPhrase($aPackage['recurring_period'], $iCost, $aPackages[$iKey]['default_cost'], $aPackage['currency_symbol']);
                            $aPackages[$iKey]['default_recurring_currency_id'] = $sKey;
                        }
                    }                   
                }
            }
        }       

        return (count($aPackages) > 0 ? $aPackages[0] : array());
    }    

    public function transactionadd($aData){
        $iPackageId = isset($aData['iPackageId']) ? (int) $aData['iPackageId'] : 0;
        if (!($aPackage = Phpfox::getService('subscribe')->getPackage($iPackageId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('subscribe.unable_to_find_the_package_you_are_looking_for'));
        }
        if (Phpfox::getUserBy('user_group_id') == $aPackage['user_group_id'])
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('subscribe.attempting_to_upgrade_to_the_same_user_group_you_are_already_in'));
        }

        $aPackage['default_currency_id'] = isset($aPackage['default_currency_id']) ? $aPackage['default_currency_id'] : $aPackage['price'][0]['alternative_currency_id'];
        $aPackage['default_cost'] = isset($aPackage['default_cost']) ? $aPackage['default_cost'] : $aPackage['price'][0]['alternative_cost'];
        $iPurchaseId = Phpfox::getService('subscribe.purchase.process')->add(array(
                'package_id' => $aPackage['package_id'],
                'currency_id' => $aPackage['default_currency_id'],
                'price' => $aPackage['default_cost']
            )
        );  
        $bIsFree = false;
        /* Make sure we mark it as free only if the default cost is free and its not a recurring charge */
        if ($aPackage['default_cost'] == '0.00' && $aPackage['recurring_period'] == 0)
        {
            $bIsFree = true;
            Phpfox::getService('subscribe.purchase.process')->update($iPurchaseId, $aPackage['package_id'], 'completed', Phpfox::getUserId(), $aPackage['user_group_id'], $aPackage['fail_user_group']);
        }

        return array(
            'error_code' => 0,
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.added_invoice_successfully")),
            'iPackageId' => $iPackageId,
            'iPurchaseId' => $iPurchaseId,
            'bIsFree' => $bIsFree,
        );        
    }

    public function transactionupdate($aData){
        $iPurchaseId = isset($aData['iPurchaseId']) ? (int) $aData['iPurchaseId'] : 0;
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.purchase_is_not_valid")));
        }

        $sStoreKidTransactionId = isset($aData['sStoreKidTransactionId']) ? $aData['sStoreKidTransactionId'] : '';
        $sPlayStoreOrderId = isset($aData['sPlayStoreOrderId']) ? $aData['sPlayStoreOrderId'] : '';
        $sDevice = isset($aData['sDevice']) ? $aData['sDevice'] : '';
        $sStatus = '';
        switch ($aData['sStatus']) {
            case 'success':
                $sStatus = 'completed';
                $message = 'Updated purchase successfully';
                Phpfox::getService('subscribe.purchase.process')->update($aPurchase['purchase_id'], $aPurchase['package_id'], $sStatus, $aPurchase['user_id'], $aPurchase['user_group_id'], $aPurchase['fail_user_group']);

                // update transaction 
                if(empty($sDevice) == false){
                    $sDevice = Phpfox::getService('mfox.helper')->changeTypeDevice($sDevice);
                    $aExtra  =  array();
                    $platform = $sDevice;
                    $transaction_item_type = Phpfox::getService("mfox.helper")->getConst("device.support." . $platform, "id");

                    $sModuleId = 'subscribe';
                    $transaction_store_kit_purchase_id = '';
                    $aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getAllStoreKitPurchaseByModuleId($sModuleId, $aItem['package_id']);
                    foreach ($aStoreKitPurchase as $key => $value) {
                        if($transaction_item_type == $value['storekitpurchase_device']){
                            $transaction_store_kit_purchase_id = $value['storekitpurchase_key'];
                            break;
                        }
                    }

                    $aVals = array(
                        'transaction_method_id' => Phpfox::getService("mfox.helper")->getConst("transaction.method.inapppurchase", "id"), 
                        'extra' => serialize($aExtra), 
                        'transaction_amount' => $aPurchase['price'], 
                        'transaction_currency' => $aPurchase['currency_id'], 
                        'transaction_item_id' => $aPurchase['package_id'], 
                        'transaction_item_type' => $transaction_item_type, 
                        'transaction_module_id' => $sModuleId, 
                        'transaction_user_id' => Phpfox::getUserId(), 
                        'transaction_store_kit_purchase_id' => $transaction_store_kit_purchase_id, 
                        'transaction_store_kit_transaction_id' => (empty($sStoreKidTransactionId) ? $sPlayStoreOrderId : $sStoreKidTransactionId), 
                    );
                    Phpfox::getService('mfox.transaction')->addTransaction($aVals);
                }

                break;

            case 'fail':
                $sStatus = 'cancel';
                $message = 'Please try purchase again.';
                break;
            
            default:
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_this_status_yet")));
                break;
        }

        return array('result' => 1, 'error_code' => 0, 'message' => $message);
    }
}

<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Transaction extends Phpfox_Service
{
	private $_aTransactionStatus ;
	private $_aTransactionMethods ;

	public function __construct() {
		$this->_aTransactionStatus = array( 
			"initialized" => array(
				"id" => 1, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.initialized")),
			),
			"expired" => array(
				"id" => 2, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.expired")),
			),
			"pending" => array(
				"id" => 3, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.pending")),
			),
			"completed" => array(
				"id" => 4, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.completed")),
			),
			"cancelled" => array(
				"id" => 5, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.cancelled")),
			),
		);

		$this->_aTransactionMethods = array( 
			"inapppurchase" => array(
				"id" => 1, 
				"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.inapppurchase")),
			),
		);
	}

	public function getAllTransactionMethods() {
		return $this->_aTransactionMethods;
	}

	public function getAllTransactionStatus() {
		return $this->_aTransactionStatus;
	}

	public function getStoreKitPurchaseByModuleId($module_id, $item_id = null, $device = 1){
		$sWhere = '';
		if($item_id !== null){
			$sWhere .= ' AND l.storekitpurchase_item_id = ' . (int)$item_id;
		}
		$sWhere .= ' AND l.storekitpurchase_device = ' . (int)$device;

		return $this->database()->select('*')
			->from(Phpfox::getT('mfox_storekitpurchase'), 'l')
			->where('l.storekitpurchase_module_id = \'' .  $this->database()->escape($module_id) . '\'' . $sWhere)
			->execute('getSlaveRow');
	}

	public function getAllStoreKitPurchaseByModuleId($module_id, $item_id = null){
		$sWhere = '';
		if($item_id !== null){
			$sWhere .= ' AND l.storekitpurchase_item_id = ' . (int)$item_id;
		}

		return $this->database()->select('*')
			->from(Phpfox::getT('mfox_storekitpurchase'), 'l')
			->where('l.storekitpurchase_module_id = \'' .  $this->database()->escape($module_id) . '\'' . $sWhere)
			->execute('getSlaveRows');
	}

	public function getMultiStoreKitPurchaseByModuleId($module_id, $device = 1){
		return $this->database()->select('*')
			->from(Phpfox::getT('mfox_storekitpurchase'), 'l')
			->where('l.storekitpurchase_module_id = \'' .  $this->database()->escape($module_id) . '\' AND l.storekitpurchase_device = ' . (int)$device)
			->execute('getSlaveRows');
	}

	public function deleteStoreKitPurchaseByModuleId($module_id, $device = 1){
		$this->database()->delete(Phpfox::getT('mfox_storekitpurchase')
			, 'storekitpurchase_module_id = \'' .  $this->database()->escape($module_id) . '\' AND storekitpurchase_device = ' . (int)$device
		);
	}

	public function insertStoreKitPurchaseByModuleId($aParams = array()){
		if(empty($aParams['storekitpurchase_key'])){
			return false;
		}

		$aSql = array(
			'storekitpurchase_key' => $aParams['storekitpurchase_key'],
			'storekitpurchase_module_id' => $aParams['storekitpurchase_module_id'],
			'storekitpurchase_type' => (isset($aParams['storekitpurchase_type']) ? $aParams['storekitpurchase_type'] : 'purchase_product'),			
			'storekitpurchase_item_id' => (isset($aParams['storekitpurchase_item_id']) ? $aParams['storekitpurchase_item_id'] : ''),			
			'storekitpurchase_device' => (isset($aParams['storekitpurchase_device']) ? $aParams['storekitpurchase_device'] : 1),			
		);

		$id = $this->database()->insert(Phpfox::getT('mfox_storekitpurchase'), $aSql);

		return $id;
	}

	public function addTransaction($aVals) {
		$aInsert = array( 
			"transaction_method_id"   => $aVals["transaction_method_id"],
			"transaction_status_id"   => Phpfox::getService("mfox.helper")->getConst("transaction.status.completed", "id"),
			"extra"                   => $aVals["extra"], 
			"transaction_description" => "",
			"transaction_amount"      => $aVals["transaction_amount"],
			"transaction_currency"    => $aVals["transaction_currency"],
			"gateway_transaction_id"  => NULL,
			"transaction_start_date"  => PHPFOX_TIME,
			"transaction_pay_date"    => PHPFOX_TIME,
			"transaction_item_id"     => $aVals["transaction_item_id"],
			"transaction_item_type"     => $aVals["transaction_item_type"],
			"transaction_module_id"     => $aVals["transaction_module_id"],
			"transaction_user_id"     => $aVals["transaction_user_id"],			
			"transaction_store_kit_purchase_id"       => $aVals["transaction_store_kit_purchase_id"],
			"transaction_store_kit_transaction_id"       => $aVals["transaction_store_kit_transaction_id"],
		);

		$iTransactionId = $this->database()->insert(Phpfox::getT("mfox_transaction"), $aInsert);

		return $iTransactionId;
	}
}

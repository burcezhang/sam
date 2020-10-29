<?php
/**
 * AnyDATA Protocol
 */

use \Lib\Context;
use \Lib\Gateway;
use \Lib\StatisticClient;
use \Lib\Store;
use \Lib\Dbhelper;
use \Lib\CommFunc;
use \Lib\PushMessage;
use \Protocols\GatewayProtocol;
use \Protocols\TextProtocol;

class Event
{
    /**
     */
    public static function onGatewayConnect($client_id)
    {
        //Gateway::sendToCurrentClient(TextProtocol::encode("WELCOME"));
    }

    /**
     */
    public static function onGatewayMessage($buffer)
    {
        $buffer=bin2hex($buffer);
		//var_dump($buffer);
		//return TextProtocol::check($buffer);
		///*
		$recv_len = strlen($buffer);
		if($recv_len < 4)
		{
			return 4 - $recv_len;
		}
		$str_payload = substr($buffer,0,4);
		$total_len = base_convert(CommFunc::getHexSEQ($str_payload),16,10);
		$iReturn = $total_len * 2 + 8 - $recv_len;

		return $iReturn;
		//*/
    }

   /**
    * @param int $client_id 
    * @param string $message
    * @return void
    */
   public static function onMessage($client_id, $message)
   {
	$message = bin2hex($message);
	//var_dump($message);
	unset($message_data);
       unset($data);
       $message_data = strtoupper(TextProtocol::decode($message));
	$replay_data = null;
       //0000310c
       $data['CMD_ID']=substr($message_data,4,2);
	$revByte=substr($message_data,6,2);
       $data['EXT_CMD_ID']=substr('00'.dechex((hexdec($revByte) >> 2)),-2);
	$data['LAST']=(hexdec($revByte)&0x01);
	$data['ENCRYPT']=((hexdec($revByte) >> 1)&0x01);
       $data['CMD_NAME']=CommFunc::getCmdName($data['CMD_ID'],$data['EXT_CMD_ID']);
        //$data['TIME_STAMP']=hexdec(CommFunc::getHexSEQ(substr($message,8,8)));

        // **************************
        if(empty($_SESSION['name']))
        {
            $client_sn = '';
			if($data['CMD_NAME']=='CONN_AUTH_REQ_ESN'){
                $client_sn =  CommFunc::getHexSEQ(substr($message_data,8,8));
            }else if($data['CMD_NAME']=='CONN_AUTH_REQ_IMEI'){
                $client_sn =  CommFunc::getHexSEQ(substr($message_data,8,16));
            }
			if(!$client_sn){}else{
				// Check SN,First
				if(Dbhelper::checkConn($client_sn)===true)
				{
					$_SESSION['name'] = $client_sn;

					$data['PAYLOAD']=serialize(CommFunc::getPayload($data['CMD_NAME'],substr($message_data,8)));
					$data['DEVICE_SN']=$_SESSION['name'];
					Dbhelper::saveData($data);
					$replay_data = "010000".substr('00'.dechex((hexdec($data['EXT_CMD_ID']) << 2)),-2)."00";
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}else{
					$replay_data = "010000".substr('00'.dechex((hexdec($data['EXT_CMD_ID']) << 2)),-2)."01";
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}
			}
        }else{
			if($data['CMD_NAME']=='CONN_AUTH_REQ_ESN' || $data['CMD_NAME']=='CONN_AUTH_REQ_IMEI'){
				$replay_data = "010000".substr('00'.dechex((hexdec($data['EXT_CMD_ID']) << 2)),-2)."00";
				Gateway::sendToCurrentClient(hex2bin($replay_data));
			}else{
				if(($data['CMD_NAME']=='IN_HOME_ALERT_RPT') || ($data['CMD_NAME']=='OUT_OF_HOME_ALERT_RPT')){
					$tmp=CommFunc::getPayload($data['CMD_NAME'],substr($message_data,8));
					$data['BEACON_ID']=$tmp['BEACON_ID'];
					if($data['CMD_NAME']=='IN_HOME_ALERT_RPT'){
						$tmp=Dbhelper::checkBeaconInfo($data['BEACON_ID']);
					}
					$data['PAYLOAD']=serialize($tmp);
				}else if($data['CMD_NAME']=='CELL_TOWER_INFO_RPT'){
					$data['PAYLOAD']=serialize(CommFunc::getPayload($data['CMD_NAME'],substr($message_data,8)));
					$result['POSITION']=CommFunc::GetCellTowerInfo(substr($message_data,8));
				}else if($data['CMD_NAME']=='WIFI_TOWER_INFO_RPT'){
					$data['PAYLOAD']=serialize(CommFunc::getPayload($data['CMD_NAME'],substr($message_data,8)));
					$result['POSITION']=CommFunc::GetWifiTowerInfo(substr($message_data,8));
				}else{
					//if($data['CMD_NAME']=='OUT_OF_HOME_ALERT_RPT') $data['BEACON_ID']=$_SESSION['BEACON_ID'];
					$data['PAYLOAD']=serialize(CommFunc::getPayload($data['CMD_NAME'],substr($message_data,8)));
				}
				$data['DEVICE_SN']=$_SESSION['name'];

				Dbhelper::saveData($data);

				if($data['CMD_NAME']=='DEV_INFO_RPT'){
					$checkSyncNum = Dbhelper::checkSyncNum($data['DEVICE_SN']);
					if((int)$checkSyncNum['total']>0){
						$replay_data = "00003111"; //SET LAST TO 1
						Gateway::sendToCurrentClient(hex2bin($replay_data));
						sleep(10);
						$nextData = CommFunc::createConfigCmd(Dbhelper::getSyncRow($data['DEVICE_SN'],$checkSyncNum),(int)$checkSyncNum['total']);
						$replay_data = $nextData['CMD_HEX'];
						if($replay_data){
							Gateway::sendToCurrentClient(hex2bin($replay_data));
							Dbhelper::execSql(sprintf($nextData['CMD_SQL'],$data['DEVICE_SN']));
						}
					}else{
						$replay_data = "00003110"; //SET LAST TO 0
						Gateway::sendToCurrentClient(hex2bin($replay_data));
					}
				}else if($data['CMD_NAME']=='SYS_CONFIG_TO_MS'){
					$checkSyncNum = Dbhelper::checkSyncNum($data['DEVICE_SN']);
					if((int)$checkSyncNum['total']>0)
					{
						$nextData = CommFunc::createConfigCmd(Dbhelper::getSyncRow($data['DEVICE_SN'],$checkSyncNum),(int)$checkSyncNum['total']);
						$replay_data = $nextData['CMD_HEX'];
						if($replay_data){
							Gateway::sendToCurrentClient(hex2bin($replay_data));
							Dbhelper::execSql(sprintf($nextData['CMD_SQL'],$data['DEVICE_SN']));
						}
					}
				}else if($data['CMD_NAME']=='CONN_GET_SYSTEM_TIME_REQ'){
					$replay_data = "0400".$data['CMD_ID'].substr('00'.dechex((hexdec($data['EXT_CMD_ID']) << 2)),-2);
					$replay_data .=  CommFunc::getHexSEQ(substr('00000000'.dechex( strtotime('-10year -5day +8hours',CommFunc::getTime()) ),-8));
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}else if($data['CMD_NAME']=='CELL_TOWER_INFO_RPT'){
					$replay_data = "08003114";
					$replay_data .= $result['POSITION'];
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}else if($data['CMD_NAME']=='WIFI_TOWER_INFO_RPT'){
					$replay_data = "08003118";
					$replay_data .= $result['POSITION'];
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}else{
					$replay_data = "0000".$data['CMD_ID'].substr('00'.dechex((hexdec($data['EXT_CMD_ID']) << 2)),-2);
					Gateway::sendToCurrentClient(hex2bin($replay_data));
				}
			}
        }
		switch($data['CMD_NAME']){
			case 'EMERGENCY_ALERT_RPT':
			case 'TEMPERATURE_ALERT_RPT':
			case 'SHOCK_ALERT_RPT':
			case 'LOW_MOVMENT_ALERT_PRT':
			case 'HAZARDOUS_MOVEMENT_ALERT_RPT':
			case 'GEOFENCE_ALERT_RPT':
			case 'IN_HOME_ALERT_RPT':
			case 'OUT_OF_HOME_ALERT_RPT':
			case 'SNOOZE_ALERT_RPT':
			case 'DEV_PWR_ON_RPT':
			case 'DEV_PWR_OFF_RPT':
			case 'LOW_BATTERY_ALERT_RPT':
			case 'FULL_BATTERY_ALERT_RPT':
			case 'REMIND_ALERT':
			$data['AlertID'] = Dbhelper::saveAlert($data);
			//include('Lib/PushMessage.php');
			PushMessage::pushAlert($data);
			break;
		}

		//Write Log
		$LogData['DateTime']=date('Y/m/d H:i:s');
		$LogData['CMD_ID']=$data['CMD_ID'];
		$LogData['EXT_CMD_ID']=$data['EXT_CMD_ID'];
		$LogData['CMD_NAME']=$data['CMD_NAME'];
		$LogData['CLIENT_ID']=$client_id;
		$LogData['SESSION']=$_SESSION['name'];
		$LogData['RevData']=$message;
		$LogData['RepData']=$replay_data;
		Dbhelper::writeData(serialize($LogData));
		return;
   }

   /**
    * @param integer $client_id
    * @return void
    */
   public static function onClose($client_id)
   {
       //GateWay::sendToAll(TextProtocol::encode("{$_SESSION['name']}[$client_id] logout"));
   }
}

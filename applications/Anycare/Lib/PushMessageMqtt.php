<?php
namespace Lib;

use \Lib\Dbhelper;
include_once('MQTT/MQQTTConnection.php');
include_once('MQTT/MQTTMessage.php');
include_once('Net/HttpClient.php');

class PushMessage{
	private static function pushToClient($smsData){
		//header('Content-Type: text/html; charset=UTF-8');
		if(!empty($smsData['topic'])&&!empty($smsData['target'])){
			$topic=$smsData['topic'];
			$target=$smsData['target'];
			$body=$smsData['body'];
			$conn = new \MQQTTConnection();
			$conn->connect('mqtt', array('SAM_HOST' => '113.106.194.222', 'SAM_PORT' => 1883));
			$msgCpu = new \MQTTMessage($body);
			$conn->send('topic://'.$topic.'/'.$target, $msgCpu);
			$conn->disconnect();

			$notes['target']=$topic.'/'.$target;
			$notes['inData']=$body;
			$notes['reData']=1;

			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='PUSH';
			$logData['act_obj']=$smsData['act_obj'];
			$logData['notes']=serialize($notes);
			self::saveLog($logData);
			return true;
		}else{
			$notes['reData']='No target';
			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='PUSH';
			$logData['act_obj']=$smsData['obj_id'];
			$logData['notes']= serialize($notes);
			self::saveLog($logData);
			return false;
		}
	}
	private static function sendSMS($smsData){
		if($smsData['phone']){
			$timestamp = self::timestamp();
			$data=array();
			$data['cmd']='send';
			$data['eprId']='242';
			$data['userId']='szzhangjian';
			$data['key']= md5($data['eprId'].$data['userId'].'Zhangj242'.$timestamp);
			$data['timestamp']=$timestamp;
			$data['format']='json';
			$data['mobile']=is_array($smsData['phone'])?implode(',',$smsData['phone']):$smsData['phone'];
			$data['msgId']=$timestamp;
			$data['content']=$smsData['body'];
			$http=new \HttpClient('client.sms10000.com');
			$ret=$http->post('/api/webservice',$data);

			$notes = array();
			$notes['phone']=$data['mobile'];
			$notes['inData']=$smsData;
			$notes['reData']=$ret;

			$logData = array();
			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='SMS';
			$logData['act_obj']=$smsData['act_obj'];
			$logData['notes']=serialize($notes);
			self::saveLog($logData);
			return true;
		}else{
			$notes = array();
			$logData = array();
			$notes['reData']='No phone';
			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='SMS';
			$logData['act_obj']=$smsData['obj_id'];
			$logData['notes']=serialize($notes);
			self::saveLog($logData);
			return false;
		}

	}
	//存日志
	private static function saveLog($logData){
		if(!$logData['user_id'])$logData['user_id']=0;
		if(!$logData['act_type'])$logData['act_type']='';
		if(!$logData['module'])$logData['module']='';
		if(!$logData['act_obj'])$logData['act_obj']='';
		if(!$logData['notes'])$logData['notes']='';
		$logData['act_time'] = self::getCurrTime();
		Dbhelper::writeAlertLog($logData);
	}
	private static function getCurrTime() {
		date_default_timezone_set('Asia/Shanghai');
		return time();
	}
	private static function toTime($sDatetime) {
		if (empty ( $sDatetime )) {
			return '';
		}
		date_default_timezone_set('Asia/Shanghai');
		return strtotime($sDatetime);
	}
	private static function timestamp() {
		list($s1, $s2) = explode(' ', microtime());
		return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
	}
    //根据设备请求的类型获取对应的命令名称
    public static function pushAlert($DATA){
		$device_set = Dbhelper::getDeviceSet($DATA['DEVICE_SN']);
		$checkAction=array();

		$checkAction['st'] = self::toTime('2000-01-01');
		$checkAction['et'] = self::getCurrTime()+864000;
		$checkAction['auto_pass'] = false;

		switch($DATA['CMD_NAME']){
			case 'SHOCK_ALERT_RPT': //跌倒告警
				$checkAction['flg']= $device_set['set']['arg1'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;//是否弹窗
			break;
			case 'EMERGENCY_CALL': //紧急来电
			case 'EMERGENCY_ALERT_RPT': //紧急告警
				$checkAction['flg']= $device_set['set']['arg2'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
			break;
			case 'GEOFENCE_ALERT_RPT': //围栏
				$checkAction['flg']= $device_set['set']['arg3'];
			break;
			case 'SNOOZE_ALERT_RPT': //静止告警
				$checkAction['flg']= $device_set['set']['arg4'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
			break;
			case 'TEMPERATURE_ALERT_RPT': //温度
				$checkAction['flg']= $device_set['set']['arg5'];
			break;
			case 'LOW_BATTERY_ALERT_RPT': //低电
			case 'FULL_BATTERY_ALERT_RPT': //满电
			case 'DEV_PWR_OFF_RPT': //关机
				$checkAction['flg'] = $device_set['sms']['arg6'];
			break;
			case 'DEV_PWR_ON_RPT': //开机
				$checkAction['flg'] = 0;
				$checkAction['st'] = 0;
				$checkAction['et'] = 0;
			break;
			case 'REMIND_ALERT': //预约
				$checkAction['flg']=$device_set['set']['arg8'];
			break;
			case 'IN_HOME_ALERT_RPT': //在Beacon附近
				$checkAction['flg']=$device_set['set']['arg10'];
			break;
			case 'OUT_OF_HOME_ALERT_RPT': //离开Beacon
				$checkAction['flg']=$device_set['set']['arg11'];
			break;
			default:
				$checkAction['auto_pass'] = true;
		}
		$save=true;//保存日志
		if($checkAction['auto_pass']){//PASS
			$save=false;
		}
		//SMS和电话设置同时使用。
		if($checkAction['flg'] > 0 && self::getCurrTime()>$checkAction['st'] && self::getCurrTime()<$checkAction['et']){
			//订购了SMS服务，系统自动处理
			$mArr=Dbhelper::getTemplate($DATA['DEVICE_SN'],$DATA['CMD_NAME'],$DATA['AlertID'],$DATA['BEACON_ID']);
			if($checkAction['flg']=='2'||$checkAction['flg']=='3') self::pushToClient($mArr);
			if($checkAction['flg']=='1'||$checkAction['flg']=='3') self::sendSMS($mArr);
			unset($data);
			$data['id']         =$DATA['AlertID'];
			$data['operator']   = 1;
			$data['step']       ='2';
			$data['start_time'] =self::getCurrTime();
			$data['end_time']   =self::getCurrTime();
			$data['status_flg'] ='F';
			Dbhelper::updateAlert($data);
		}elseif($save){
			unset($data);
			$data['id']         =$DATA['AlertID'];
			$data['operator']   = 1;
			$data['step']       ='1';
			$data['start_time'] =self::getCurrTime();
			$data['end_time']   =self::getCurrTime();
			$data['status_flg'] ='F';
			Dbhelper::updateAlert($data);
		}
	}
}

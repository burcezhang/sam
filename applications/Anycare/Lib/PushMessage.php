<?php
namespace Lib;

include_once('Baidu/Channel.php');
include_once('Net/HttpClient.php');
use \Lib\Dbhelper;

class PushMessage{
	private static $apiKey = "xT8c7ZYqDYFfIIw5rbEX7qnD";
	private static $secretKey = "GwSIYl3RwZLmPRK0aYAFwkfyXpYzGR8t";
	private static function pushAndroid($topic,$target,$alert,$body){
		$channel = new \Channel( self::$apiKey, self::$secretKey );
		$push_type = 2; //推送消息
		$optional[\Channel::TAG_NAME] = $topic.'_'.$target;
		$optional[\Channel::DEVICE_TYPE] = 3;
		$optional[\Channel::MESSAGE_TYPE] = 0;
		//http://developer.baidu.com/wiki/index.php?title=docs/cplat/push/api/list
		//http://developer.baidu.com/wiki/index.php?title=docs/cplat/push/faq#.E4.B8.BA.E4.BD.95.E9.80.9A.E8.BF.87Server_SDK.E6.8E.A8.E9.80.81.E6.88.90.E5.8A.9F.EF.BC.8CAndroid.E7.AB.AF.E5.8D.B4.E6.94.B6.E4.B8.8D.E5.88.B0.E9.80.9A.E7.9F.A5.EF.BC.9F
		$message=array('title'=>$alert['title'],'description'=>$body,'alert_id'=>$alert['id'],'alert_type'=>$topic,'target'=>$target);
		$message=json_encode($message);
		/*
		$message = '{
			"title": "'.$title.'",
			"description": "'.$body.'",
			"notification_basic_style":7,
			"open_type":2,
			"custom_content":{\'alert_type\':\''.$topic.'\',\'target\':\''.$target.'\'}
 		}';*/
		$message_key = "msg_key";
    	return $channel->pushMessage( $push_type, $message, $message_key, $optional );
	}
	private static function pushIOS($topic,$target,$alert,$body){
		$channel = new \Channel ( self::$apiKey, self::$secretKey );
		$push_type = 2; //推送消息
		$optional[\Channel::TAG_NAME] = $topic.'_'.$target;
		$optional[\Channel::DEVICE_TYPE] = 4;
		$optional[\Channel::MESSAGE_TYPE] = 1;
		$optional[\Channel::DEPLOY_STATUS] = 1;
		//通知类型的内容必须按指定内容发送，示例如下：
		$message = '{
			"aps":{
				"alert":"'.$body.'",
				"sound":"",
				"badge":0,
				"open_type":2,
				"custom_content":{\'alert_type\':\''.$topic.'\',\'target\':\''.$target.'\'}
			}
	 	}';
    	return $channel->pushMessage( $push_type, $message, $message_key, $optional );
	}
	private static function pushToClient($smsData){
		if(!empty($smsData['topic'])&&!empty($smsData['target'])){
			$topic=$smsData['topic'];
			$target=$smsData['target'];
			$body=$smsData['body'];

	    	$ret = self::pushAndroid($topic,$target,$smsData['title'],$body);

			$notes['target']=$topic.'/'.$target;
			$notes['inData']=$body;
			$notes['reData']=$ret;

			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='PUSH';
			$logData['act_obj']=$smsData['act_obj'];
			$logData['notes']=serialize($notes);
			self::saveLog($logData);
		}else{
			$notes['reData']='No target';
			$logData['user_id']=0;
			$logData['act_type']=$smsData['act_type'];
			$logData['module']='PUSH';
			$logData['act_obj']=$smsData['act_obj'];
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
			$data['eprId']='355';
			$data['userId']='szanydata';
			$data['key']= md5($data['eprId'].$data['userId'].'Anydata355'.$timestamp);
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
			$logData['act_obj']=!empty($smsData['act_obj'])?$smsData['act_obj']:0;
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
			$logData['act_obj']=!empty($smsData['act_obj'])?$smsData['act_obj']:0;
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

		$checkAction['flg'] = 0;
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
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'LOW_MOVMENT_ALERT_PRT'://静止报警
			case 'SNOOZE_ALERT_RPT': //小睡告警
				$checkAction['flg']= $device_set['set']['arg4'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'TEMPERATURE_ALERT_RPT': //温度
				$checkAction['flg']= $device_set['set']['arg5'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'LOW_BATTERY_ALERT_RPT': //低电//
			case 'FULL_BATTERY_ALERT_RPT': //满电
			case 'DEV_PWR_OFF_RPT': //关机
				$checkAction['flg'] = $device_set['set']['arg6'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'DEV_PWR_ON_RPT': //开机
				$checkAction['flg'] = $device_set['set']['arg12'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'REMIND_ALERT': //预约
				$checkAction['flg']=$device_set['set']['arg8'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'IN_HOME_ALERT_RPT': //在Beacon附近
				$checkAction['flg']=$device_set['set']['arg10'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			case 'OUT_OF_HOME_ALERT_RPT': //离开Beacon
				$checkAction['flg']=$device_set['set']['arg11'];
				$checkAction['auto_pass'] = $device_set['type']=='0'?true:false;
				break;
			default:
				$checkAction['auto_pass'] = true;
		}
		$save=true;//保存日志
		if($checkAction['auto_pass']){//PASS
			$save=false;
		}
		//SMS和电话设置同时使用。
		if((int)$checkAction['flg'] > 0 && self::getCurrTime()>$checkAction['st'] && self::getCurrTime()<$checkAction['et']){
			$Alert=array();
			$Alert['DEV_PWR_ON_RPT']=array('id'=>2,'title'=>'开机告警');
			$Alert['EMERGENCY_ALERT_RPT']=array('id'=>3,'title'=>'紧急告警');
			$Alert['EMERGENCY_CALL']=array('id'=>4,'title'=>'紧急来电');
			$Alert['GEOFENCE_ALERT_RPT']=array('id'=>6,'title'=>'围栏告警');
			$Alert['HAZARDOUS_MOVEMENT_ALERT_RPT']=array('id'=>7,'title'=>'危险运动告警');
			$Alert['FULL_BATTERY_ALERT_RPT']=array('id'=>5,'title'=>'满电告警');
			$Alert['LOW_BATTERY_ALERT_RPT']=array('id'=>8,'title'=>'低电告警');
			$Alert['DEV_PWR_OFF_RPT']=array('id'=>1,'title'=>'关机告警');
			$Alert['LOW_MOVMENT_ALERT_PRT']=array('id'=>9,'title'=>'静止报警');
			$Alert['SNOOZE_ALERT_RPT']=array('id'=>11,'title'=>'小睡告警');
			$Alert['SHOCK_ALERT_RPT']=array('id'=>10,'title'=>'跌倒报警');
			$Alert['TEMPERATURE_ALERT_RPT']=array('id'=>12,'title'=>'温度告警');
			//$Alert['SERVICE_EXPIRE']=array('id'=>13,'title'=>'服务即将到期时提醒');
			$Alert['IN_HOME_ALERT_RPT']=array('id'=>14,'title'=>'到家提醒');
			$Alert['OUT_OF_HOME_ALERT_RPT']=array('id'=>15,'title'=>'离家提醒');
			//订购了SMS服务，系统自动处理
			$mArr=Dbhelper::getTemplate($DATA['DEVICE_SN'],$DATA['CMD_NAME'],$DATA['AlertID'],isset($DATA['BEACON_ID'])?$DATA['BEACON_ID']:0);
			$mArr['title']=$Alert[$DATA['CMD_NAME']];
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

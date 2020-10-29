<?php
namespace Lib;
/**
 * 数据库读写
 * @author keoo.tian
 */

class Dbhelper
{

	public static function getConn()
	{
			$mysql_server="localhost";
			$mysql_user="anycare";
			$mysql_pwd="anycare8888";
			$mysql_dbname="anycarecc";
			$myconn=mysql_pconnect($mysql_server,$mysql_user,$mysql_pwd);
			mysql_select_db($mysql_dbname,$myconn);
			mysql_query("set names utf8");
			return $myconn;
	}
	public static function getTime()
	{
		date_default_timezone_set('Asia/Shanghai');
		return time();
	}
	private static function str_replace_once($needle, $replace, $haystack) {
		$pos = strpos($haystack, $needle);
		if ($pos === false) {
			return $haystack;
		}
		return substr_replace($haystack, $replace, $pos, strlen($needle));
	}
	public static function getTemplate($device_sn,$alert_type,$alertid,$beaconid=0){
        $boolResult = true;

		$strsql="select a.elderly_name,a.monitor_info,a.beacons,b.content FROM any_elderly_info as a,any_sms_template as b ";
		$strsql.="where a.device_sn = '$device_sn' and b.sms_type='$alert_type' and  b.status_flg='Y'";
		$result = mysql_query($strsql,self::getConn());
		if(empty($result)){
			$arrayResult = array();
		}else{
			$elderly_info = mysql_fetch_assoc($result);
			$monitor_info = unserialize($elderly_info['monitor_info']);
			$monitor_phone=array();
			for($imonitor=0;$imonitor<count($monitor_info);$imonitor++){
				if($monitor_info[$imonitor]['phone']) $monitor_phone[] = $monitor_info[$imonitor]['phone'];
			}

			$strsql="SELECT c.account,c.phone,c.home_phone FROM any_elderly_info AS a INNER JOIN any_elderly_monitor AS b ON a.id = b.elderly_id INNER JOIN any_user_mas AS c ON b.user_id = c.id";
			$strsql.="where a.device_sn = '$device_sn' and  a.status_flg='Y' and c.status_flg='Y'";
			$result1 = mysql_query($strsql, self::getConn());
			if(!empty($result1)){
				while($Rs = mysql_fetch_assoc($result1)){
					if(!empty($Rs['phone'])&&preg_match('/^1[3,4,5,8][0-9]{9}$/',$Rs['phone'])){
						$monitor_phone[] = $Rs['phone'];
					}
				}
				mysql_free_result($result1);
			}
			if('IN_HOME_ALERT_RPT'==$alert_type||'OUT_OF_HOME_ALERT_RPT'==$alert_type) {
				$beacon_info = !empty($elderly_info['beacons'])?unserialize($elderly_info['beacons']):array();
				unset($beacon_info['FLG']);
				if('OUT_OF_HOME_ALERT_RPT'==$alert_type){
					$strsql="select beacon_id FROM any_alert_list where device_sn = '$device_sn' and alert_type='IN_HOME_ALERT_RPT' order by id desc limit 1";
					$result2 = mysql_query($strsql,self::getConn());
					if(!empty($result2)){
						$Rs = mysql_fetch_assoc($result2);
						$beaconid=$Rs['beacon_id']?$Rs['beacon_id']:0;
						mysql_free_result($result2);
					}
				}
				$beacon=array('name'=>'','address'=>'');
				if($beaconid!==0){
					foreach($beacon_info as $i=>$Rs){
						if($beaconid==$Rs['beaconID']){
							$beacon=$Rs;
							break;
						}
					}
				}
				$sms_template = self::str_replace_once('***', $elderly_info['elderly_name'], $elderly_info['content']);
				$sms_template = self::str_replace_once('***', $beacon['name'], $sms_template);
				$sms_template = self::str_replace_once('***', $beacon['address'], $sms_template);
				$sms_template = $sms_template;
			}else{
				$sms_template = str_replace('***',$elderly_info['elderly_name'], $elderly_info['content']);
			}
			$arrayResult =array('phone'=>$monitor_phone,'body'=>$sms_template,'act_type'=>$alert_type,'act_obj'=>$alertid,'target'=>$device_sn,'topic'=>$alert_type);
		}
        if($result) mysql_free_result($result);
        return $arrayResult;
	}
	public static function getDeviceSet($device_sn)
	{
        $boolResult = true;
		$strsql="select device_set1,device_set2 from any_elderly_info where device_sn='$device_sn'";
		$result = mysql_query($strsql,self::getConn());
		if(empty($result)){
			$arrayResult = array();
		}else{
			$arrayResult = mysql_fetch_assoc($result);
			$device_set['set']=unserialize($arrayResult['device_set1']);
			$device_set['type']=$arrayResult['device_set2'];
		}
        mysql_free_result($result);
        return $device_set;
	}
	public static function updateAlert($data)
	{
		$strsql='update any_alert_list set operator=\''.$data['operator'].'\',step=\''.$data['step'].'\',start_time=\''.$data['start_time'].'\',end_time=\''.$data['end_time'].'\',status_flg=\''.$data['status_flg'].'\' where id=\''.$data['id'].'\'';
		mysql_query($strsql,self::getConn());
	}

	//仅记录测试数据，生产环境不需要
	public static function writeData($message)
	{
		$strsql="insert into misone_demo(dt,remark)values(".self::getTime().",'$message')";
		mysql_query($strsql,self::getConn());
	}
	public static function writeAlertLog($Data)
	{
		$strsql="insert into any_alert_log(user_id,act_type,module,act_obj,notes,act_time)values
				('".$Data['user_id']."','".$Data['act_type']."','".$Data['module']."','".$Data['act_obj']."','".$Data['notes']."','".$Data['act_time']."')";
		mysql_query($strsql,self::getConn());
	}

    //检查连接是否合法
    public static function checkConn($SN)
	{
        $boolResult = true;
		$strsql="select COUNT(id) from any_device_mas where status_flg in('Y','S') and device_sn='$SN'";
		$rs = mysql_query($strsql,self::getConn());
        if(empty($rs)){
            $boolResult = false;
        }else{
			$record = mysql_fetch_array($rs);
			if((int)$record[0] == 0)$boolResult=false;
		}
        mysql_free_result($rs);
        return $boolResult;
	}

    public static function saveData($message)
	{
		if($message['CMD_NAME'] && $message['DEVICE_SN'])
		{
			date_default_timezone_set('Asia/Shanghai');
			$strsql="insert into any_protocol_data(dt,cmd1,cmd2,cmd_name,payload,device_sn)values(".self::getTime().",'".$message['CMD_ID']."','".$message['EXT_CMD_ID']."','".$message['CMD_NAME']."','".$message['PAYLOAD']."','".$message['DEVICE_SN']."')";
			mysql_query($strsql,self::getConn());
		}
	}

	public static function saveAlert($message)
	{
		if($message['CMD_NAME'] && $message['DEVICE_SN'])
		{
			date_default_timezone_set('Asia/Shanghai');
			if($message['CMD_NAME']=='IN_HOME_ALERT_RPT'||$message['CMD_NAME']=='OUT_OF_HOME_ALERT_RPT'){
				$strsql="insert into any_alert_list(alert_type,alert_dt,device_sn,operator,start_time,end_time,voice_src,step,status_flg,beacon_id)values('".$message['CMD_NAME']."',".self::getTime().",'".$message['DEVICE_SN']."',0,0,0,'',0,'A','".$message['BEACON_ID']."')";
			}else{
				$strsql="insert into any_alert_list(alert_type,alert_dt,device_sn,operator,start_time,end_time,voice_src,step,status_flg)values('".$message['CMD_NAME']."',".self::getTime().",'".$message['DEVICE_SN']."',0,0,0,'',0,'A')";
			}
			mysql_query($strsql,self::getConn());
			$rs = mysql_query('SELECT LAST_INSERT_ID() as ID',self::getConn());
			if(empty($rs)){
				$record['ID'] = 0;
			}else{
				$record = mysql_fetch_array($rs);
			}
			mysql_free_result($rs);
			return $record['ID'];
		}
	}

	public static function execSql($strSql)
	{
		if($strSql)
		{
			return mysql_query($strSql,self::getConn());
		}else{
			return false;
		}
	}
	//检查是否有数据需要下发到设备
	public static function checkSyncNum($SN)
	{
		$arrayResult['total'] = 0;
		$strsql="select set_flg,count(id) as cnt from (
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set4 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set5 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set6 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set7 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set8 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set9 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set10 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set11 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set12 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set13 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set14 like '%wait_update%'
				union all
				select 'device_set' as set_flg,id from any_elderly_info where device_sn='$SN' and device_set15 like '%wait_update%'
				union all
				select 'geofence' as set_flg,id from any_geofence where device_sn='$SN' and sync_flg!='0' limit 1
				union all
				select 'remind_alert' as set_flg,id from any_remind_alert where device_sn='$SN' and sync_flg!='0'
				union all
				select 'pill_alert' as set_flg,id from any_pill_alert where device_sn='$SN' and sync_flg!='0'
			) as table0 group by set_flg";
		$result = mysql_query($strsql,self::getConn());
        if(empty($result)){
            $arrayResult['total'] = 0;
        }else{
			while($record = mysql_fetch_assoc($result)){
				$arrayResult[$record['set_flg']] = $record['cnt'];
				$arrayResult['total']+=(int)$record['cnt'];
			}
		}
        mysql_free_result($result);
        return $arrayResult;
	}
	//获取需要下发到设备的数据
	public static function getSyncRow($SN,$checkSyncNum)
	{
		if(isset($checkSyncNum['device_set']) && (int)$checkSyncNum['device_set'] > 0){
			return self::getDeviceSetData($SN);
		}
		if(isset($checkSyncNum['geofence']) && (int)$checkSyncNum['geofence'] > 0){
			return self::getGeofenceSetData($SN);
		}
		if(isset($checkSyncNum['remind_alert']) && (int)$checkSyncNum['remind_alert'] > 0){
			return self::getRemindAlertSetData($SN);
		}
		if(isset($checkSyncNum['pill_alert']) && (int)$checkSyncNum['pill_alert'] > 0){
			return self::getPillAlertSetData($SN);
		}
		return false;
	}
	//获取需要下发到设备的：设备参数设置
	public static function getDeviceSetData($SN)
	{
		$strsql="select * from (
				select 'device_set4' as set_flg,device_set4 as set_data from any_elderly_info where device_sn='$SN' and device_set4 like '%wait_update%'
				union all
				select 'device_set5' as set_flg,device_set5 as set_data from any_elderly_info where device_sn='$SN' and device_set5 like '%wait_update%'
				union all
				select 'device_set6' as set_flg,device_set6 as set_data from any_elderly_info where device_sn='$SN' and device_set6 like '%wait_update%'
				union all
				select 'device_set7' as set_flg,device_set7 as set_data from any_elderly_info where device_sn='$SN' and device_set7 like '%wait_update%'
				union all
				select 'device_set8' as set_flg,device_set8 as set_data from any_elderly_info where device_sn='$SN' and device_set8 like '%wait_update%'
				union all
				select 'device_set9' as set_flg,device_set9 as set_data from any_elderly_info where device_sn='$SN' and device_set9 like '%wait_update%'
				union all
				select 'device_set10' as set_flg,device_set10 as set_data from any_elderly_info where device_sn='$SN' and device_set10 like '%wait_update%'
				union all
				select 'device_set11' as set_flg,device_set11 as set_data from any_elderly_info where device_sn='$SN' and device_set11 like '%wait_update%'
				union all
				select 'device_set12' as set_flg,device_set12 as set_data from any_elderly_info where device_sn='$SN' and device_set12 like '%wait_update%'
				union all
				select 'device_set13' as set_flg,device_set13 as set_data from any_elderly_info where device_sn='$SN' and device_set13 like '%wait_update%'
				union all
				select 'device_set14' as set_flg,device_set14 as set_data from any_elderly_info where device_sn='$SN' and device_set14 like '%wait_update%'
				union all
				select 'device_set15' as set_flg,device_set15 as set_data from any_elderly_info where device_sn='$SN' and device_set15 like '%wait_update%'
			) as table0 limit 1";
		$result = mysql_query($strsql,self::getConn());
		if($result){
			$row = mysql_fetch_assoc($result);
			$resultData['set_flag']=$row['set_flg'];
			$resultData['set_data']=unserialize($row['set_data']);
			return $resultData;
		}else{
			return array();
		}
	}
	//获取需要下发到设备的：围栏
	public static function getGeofenceSetData($SN)
	{
		$strsql="select * from any_geofence where device_sn='$SN' and sync_flg!='3'";
		$result = mysql_query($strsql,self::getConn());
		if($result){
			$rsArray = array();
			while($record = mysql_fetch_assoc($result)){
				$rsArray[] = $record;
			}
			$resultData['set_flag']='geofence';
			$resultData['set_data']=$rsArray;
			return $resultData;
		}else{
			return array();
		}
	}
	//获取需要下发到设备的：预约提醒
	public static function getRemindAlertSetData($SN)
	{
		$strsql = "select * from any_remind_alert where device_sn='$SN' and sync_flg!='0' order by sync_flg desc limit 1";
		$result = mysql_query($strsql,self::getConn());
		if($result){
			$row = mysql_fetch_assoc($result);
			$resultData['set_flag']='remind_alert';
			$resultData['set_data']=$row;
			return $resultData;
		}else{
			return array();
		}
	}
	//获取需要下发到设备的：吃药提醒
	public static function getPillAlertSetData($SN)
	{
		$strsql = "select * from any_pill_alert where device_sn='$SN' and sync_flg!='0' order by sync_flg desc limit 1";
		$result = mysql_query($strsql,self::getConn());
		//var_dump($strsql);
		if($result){
			$row = mysql_fetch_assoc($result);
			$resultData['set_flag']='pill_alert';
			$resultData['set_data']=$row;
			//var_dump($resultData);
			return $resultData;
		}else{
			return array();
		}
	}

	public static function checkBeaconInfo($beacon)
	{
		$strsql = "select * from any_beacon where beaconID='$beacon'";
		$result = mysql_query($strsql,self::getConn());
		//var_dump($strsql);
		if($result){
			$row = mysql_fetch_assoc($result);
			$resultData['TIME_STAMP']=self::getTime();
			$resultData['LOC_INFO']['TIME_STAMP']=self::getTime();
			$resultData['LOC_INFO']['LATITUDE']=$row['lat'];
			$resultData['LOC_INFO']['LONGITUDE']=$row['lon'];
			$resultData['LOC_INFO']['FIX_TYPE']=4;
			$resultData['BEACON_ID']=$beacon;
			$resultData['BEACON_NAME']=$row['name'];
			return $resultData;
		}else{
			return array();
		}
	}

}

<?php
namespace Lib;
/**
 *  Common Function
 * @author keoo.tian
 */

include_once('Net/HttpClient.php');

class CommFunc
{

	public static function getHexSEQ($strHex)
	{
		$sResult="";
		$sHexArray = str_split($strHex,2);
		for($i=count($sHexArray)-1;$i>=0;$i--)
		{
			$sResult.=$sHexArray[$i];
		}
		return $sResult;
	}

	public static function getIPHeader($CMD_ID,$Last,$Encrypt,$EXT_CMD_ID,$Mask){
		return $CMD_ID.( dechex(hexdec($EXT_CMD_ID)<<2) + dechex($Last) + dechex($Encrypt<<1)).substr('0000'.self::getHexSEQ($Mask),-4);
	}

	public static function getTime()
	{
		date_default_timezone_set('Asia/Shanghai');
		return time();
	}


    public static function getCmdName($CMD_ID,$EXT_CMD_ID)
	{
		unset($sResult);
		$sResult="";
        $cmd_full=$CMD_ID.$EXT_CMD_ID;
        switch($cmd_full)
        {
            case "0000":
                $sResult="CONN_AUTH_REQ_ESN";
                break;
            case "0001":
                $sResult="CONN_AUTH_REQ_IMEI";
                break;
            case "0003":
                $sResult="CONN_GET_SYSTEM_TIME_REQ";
                break;

            case "2000":
                $sResult="EMERGENCY_ALERT_RPT";
                break;
            case "2001":
                $sResult="TEMPERATURE_ALERT_RPT";
                break;
            case "2002":
                $sResult="SHOCK_ALERT_RPT";
                break;
            case "2003":
                $sResult="LOW_MOVMENT_ALERT_PRT";
                break;
            case "2004":
                $sResult="PILL_BOTTLE_OPEN_RPT";
                break;

            case "3100":
                $sResult="POSITION_TRACKING_ALERT_RPT";
                break;
            case "3101":
                $sResult="GEOFENCE_ALERT_RPT";
                break;
            case "3102":
                $sResult="IN_HOME_ALERT_RPT";
                break;
            case "3103":
                $sResult="OUT_OF_HOME_ALERT_RPT";
                break;
            case "3104":
                $sResult="DEV_INFO_RPT";
                break;
            case "3105":
                $sResult="CELL_TOWER_INFO_RPT";
                break;                
            case "3106":
                $sResult="WIFI_TOWER_INFO_RPT";
                break;                
                
            case "4101":
                $sResult="ONE_TIME_FIX_RPT";
                break;
            case "4102":
                $sResult="DEV_PWR_ON_RPT";
                break;
            case "4103":
                $sResult="DEV_PWR_OFF_RPT";
                break;
            case "4104":
                $sResult="LOW_BATTERY_ALERT_RPT";
                break;
            case "4105":
                $sResult="STR_FWD_RPT";
                break;
            case "4106":
                $sResult="ERROR_RPT";
                break;
            case "4107":
                $sResult="FULL_BATTERY_ALERT_RPT";
                break;
                
            case "5000":
            case "5001":
            case "6000":
            case "6001":
            case "6002":
            case "6003":
            case "6004":
            case "6005":
            case "6006":
            case "6007":
                $sResult="SYS_CONFIG_TO_MS";
                break;
            default:
				$sResult='';//"NA";
        }
		return $sResult;
	}

    public static function getPayload($sCmdName,$sPayload)
	{
		unset($sResult);
		$sResult="";
		switch($sCmdName)
        {
            case "CONN_AUTH_REQ_ESN":
                $sResult = self::decodePayload__CONN_AUTH_REQ_ESN($sPayload);
                break;
            case "CONN_AUTH_REQ_IMEI":
                $sResult = self::decodePayload__CONN_AUTH_REQ_IMEI($sPayload);
                break;
			case "CONN_GET_SYSTEM_TIME_REQ":
               $sResult = self::decodePayload__CONN_GET_SYSTEM_TIME_REQ($sPayload);
               break;

            case "EMERGENCY_ALERT_RPT":
                $sResult = self::decodePayload__EMERGENCY_ALERT_RPT($sPayload);
                break;
            case "TEMPERATURE_ALERT_RPT":
                $sResult = self::decodePayload__TEMPERATURE_ALERT_RPT($sPayload);
                break;
            case "SHOCK_ALERT_RPT":
                $sResult = self::decodePayload__SHOCK_ALERT_RPT($sPayload);
                break;
            case "LOW_MOVMENT_ALERT_PRT":
                $sResult = self::decodePayload__LOW_MOVMENT_ALERT_PRT($sPayload);
                break;
            case "PILL_BOTTLE_OPEN_RPT":
                $sResult = self::decodePayload__PILL_BOTTLE_OPEN_RPT($sPayload);
                break;
            case "POSITION_TRACKING_ALERT_RPT":
                $sResult = self::decodePayload__POSITION_TRACKING_ALERT_RPT($sPayload);
                break;
            case "GEOFENCE_ALERT_RPT":
                $sResult = self::decodePayload__GEOFENCE_ALERT_RPT($sPayload);
                break;
            case "IN_HOME_ALERT_RPT":
                $sResult = self::decodePayload__IN_HOME_ALERT_RPT($sPayload);
                break;
            case "OUT_OF_HOME_ALERT_RPT":
                $sResult = self::decodePayload__OUT_OF_HOME_ALERT_RPT($sPayload);
                break;
            case "DEV_INFO_RPT":
                $sResult = self::decodePayload__DEV_INFO_RPT($sPayload);
                break;
            case "CELL_TOWER_INFO_RPT":
                $sResult = self::decodePayload__CELL_TOWER_INFO_RPT($sPayload);
                break;
            case "WIFI_TOWER_INFO_RPT":
                $sResult = self::decodePayload__WIFI_TOWER_INFO_RPT($sPayload);
                break;
                
            //case "SNOOZE_ALERT_RPT":
            //    $sResult = self::decodePayload__SNOOZE_ALERT_RPT($sPayload);
            //    break;
            case "ONE_TIME_FIX_RPT":
                $sResult = self::decodePayload__ONE_TIME_FIX_RPT($sPayload);
                break;
            case "DEV_PWR_ON_RPT":
                $sResult = self::decodePayload__DEV_PWR_ON_RPT($sPayload);
                break;
            case "DEV_PWR_OFF_RPT":
                $sResult = self::decodePayload__DEV_PWR_OFF_RPT($sPayload);
                break;
            case "LOW_BATTERY_ALERT_RPT":
                $sResult = self::decodePayload__LOW_BATTERY_ALERT_RPT($sPayload);
                break;
            case "STR_FWD_RPT":
                $sResult = self::decodePayload__STR_FWD_RPT($sPayload);
                break;
            case "ERROR_RPT":
                $sResult = self::decodePayload__ERROR_RPT($sPayload);
                break;
            case "FULL_BATTERY_ALERT_RPT":
                $sResult = self::decodePayload__FULL_BATTERY_ALERT_RPT($sPayload);
                break;
            case "SYS_CONFIG_READ_REQ":
                $sResult = self::decodePayload__SYS_CONFIG_READ_REQ($sPayload);
                break;
            case "SYS_CONFIG_WRITE_REQ":
                $sResult = self::decodePayload__SYS_CONFIG_WRITE_REQ($sPayload);
                break;
            default:
				$sResult = '';//"NA";
        }
		return $sResult;
	}

    //(CONN_AUTH_REQ_ESN)
    public static function decodePayload__CONN_AUTH_REQ_ESN($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['IMEI_ESN']=self::getHexSEQ(substr($sPayload,0,8));
        	$sData['PROTOCOL_VERSION']=self::getHexSEQ(substr($sPayload,8));
		return $sData;
	}
    //(CONN_AUTH_REQ_IMEI)
    public static function decodePayload__CONN_AUTH_REQ_IMEI($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['IMEI_ESN']=self::getHexSEQ(substr($sPayload,0,16));
        $sData['PROTOCOL_VERSION']=self::getHexSEQ(substr($sPayload,16));
		return $sData;
	}
    //(CONN_GET_SYSTEM_TIME)
	public static function decodePayload__CONN_GET_SYSTEM_TIME_REQ($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		return $sData;
	}
    //(EMERGENCY_ALERT_RPT)
    public static function decodePayload__EMERGENCY_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO($sPayload);
		return $sData;
	}
    //(TEMPERATURE_ALERT_RPT)
    public static function decodePayload__TEMPERATURE_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['TEMPERATURE']=hexdec(self::getHexSEQ(substr($sPayload,0,2)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
		//$sData['TEMPERATURE_VALUE']=hexdec(self::getHexSEQ(substr($sPayload,72)));
		return $sData;
	}
    //(SHOCK_ALERT_RPT)
    public static function decodePayload__SHOCK_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
        //$sData['SHOCK_VALUE']=hexdec(self::getHexSEQ(substr($sPayload,72)));
		return $sData;
	}
    //(LOW_MOVMENT_ALERT_PRT)
    public static function decodePayload__LOW_MOVMENT_ALERT_PRT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
        //$sData['LOW_MOVEMENT_VALUE']=hexdec(self::getHexSEQ(substr($sPayload,72)));
		return $sData;
	}
    //(PILL_BOTTLE_OPEN_RPT)
    public static function decodePayload__PILL_BOTTLE_OPEN_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
		return $sData;
	}
    //(POSITION_TRACKING_ALERT_RPT)
    public static function decodePayload__POSITION_TRACKING_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,68));
		return $sData;
	}
    //(GEOFENCE_ALERT_RPT)
    public static function decodePayload__GEOFENCE_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,68));
		$sData['NUM_GEO_ENTRY']=hexdec(self::getHexSEQ(substr($sPayload,68,2)));
		$sByte=hexdec(self::getHexSEQ(substr($sPayload,70,2)));
		$sData['GEO_IN_OUT']=($sByte&0x01);
		$sData['GEOFENCE_ID']=($sByte >> 1);
		return $sData;
	}
    //(IN_HOME_ALERT_RPT)
    public static function decodePayload__IN_HOME_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['BEACON_ID']=self::getHexSEQ(substr($sPayload,0));
		return $sData;
	}
    //(OUT_OF_HOME_ALERT_RPT)
    public static function decodePayload__OUT_OF_HOME_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['BEACON_ID']=self::getHexSEQ(substr($sPayload,0));
		return $sData;
	}
    //(DEV_INFO_RPT)
    public static function decodePayload__DEV_INFO_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['STEP_COUNT']=hexdec(self::getHexSEQ(substr($sPayload,0,4)));
		$sData['BATT']=hexdec(self::getHexSEQ(substr($sPayload,4,2)));
		return $sData;
	}
    //(CELL_TOWER_INFO_RPT)
    public static function GetCellTowerInfo($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();
		$btsLen=hexdec(self::getHexSEQ(substr($sPayload,0,2)));
		$nbtsLen=hexdec(self::getHexSEQ(substr($sPayload,$btsLen*2+2,2)));
		$bts=hex2bin(self::getHexSEQ(substr($sPayload,2,$btsLen*2)));
		$nbts=hex2bin(self::getHexSEQ(substr($sPayload,$btsLen*2+4,$nbtsLen*2)));
		$http=new \HttpClient('apilocate.amap.com');
		$data=array('accesstype'=>'0','imei'=>$_SESSION['name'],'cdma'=>'0','bts'=>$bts,'nearbts'=>$nbts,'output'=>'xml','key'=>'4789231c367b4cddc274d017b0fcc9be');
		$http->get('/position',$data);
		$sData=$http->getContent();
		preg_match('/<location>([0-9|.|,]+)/',$sData,$out);
		$mGPS=explode(',',$out[1]);
		$sLat = (double)$mGPS[1] * 33554432 / 180;
		$sLat = self::getHexSEQ(substr('00000000'.dechex(($sLat)),-8));
		$sResult = $sLat;
		$sLon = (double)$mGPS[0] * 67108864 / 360;	
		$sLon = self::getHexSEQ(substr('00000000'.dechex(($sLon)),-8));
		$sResult .= $sLon;
		
		return $sResult;
	}

    //(CELL_TOWER_INFO_RPT)
    public static function decodePayload__CELL_TOWER_INFO_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();
		$sData['CELL_INFO']=hex2bin(self::getHexSEQ(substr($sPayload,0)));
		return $sData;
	}	

    //(WIFI_TOWER_INFO_RPT)
    public static function GetWifiTowerInfo($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();
		$WIFI_INFO=hex2bin(self::getHexSEQ(substr($sPayload,0)));
		$http=new \HttpClient('apilocate.amap.com');
		$data=array('accesstype'=>'1','imei'=>$_SESSION['name'],'macs'=>$WIFI_INFO,'output'=>'xml','key'=>'4789231c367b4cddc274d017b0fcc9be');
		$http->get('/position',$data);
		$sData=$http->getContent();
		preg_match('/<location>([0-9|.|,]+)/',$sData,$out);
		$mGPS=explode(',',$out[1]);
		$sLat = (double)$mGPS[1] * 33554432 / 180;
		$sLat = self::getHexSEQ(substr('00000000'.dechex(($sLat)),-8));
		$sResult = $sLat;
		$sLon = (double)$mGPS[0] * 67108864 / 360;	
		$sLon = self::getHexSEQ(substr('00000000'.dechex(($sLon)),-8));
		$sResult .= $sLon;
				
		return $sResult;
	}	

    //(WIFI_TOWER_INFO_RPT)
    public static function decodePayload__WIFI_TOWER_INFO_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();
		$sData['WIFI_INFO']=hex2bin(self::getHexSEQ(substr($sPayload,0)));
		return $sData;
	}	

    //(ONE_TIME_FIX_RPT)
    public static function decodePayload__ONE_TIME_FIX_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,68));
		return $sData;
	}
    //(DEV_PWR_ON_RPT)
    public static function decodePayload__DEV_PWR_ON_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		return $sData;
	}
    //(DEV_PWR_OFF_RPT)
    public static function decodePayload__DEV_PWR_OFF_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
		return $sData;
	}
    //(LOW_BATTERY_ALERT_RPT)
    public static function decodePayload__LOW_BATTERY_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
        	$sData['BATTERY_VALUE']=hexdec(self::getHexSEQ(substr($sPayload,0)));
		return $sData;
	}
    //(FULL_BATTERY_ALERT_RPT)
    public static function decodePayload__FULL_BATTERY_ALERT_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		//$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
        //$sData['BATTERY_VALUE']=hexdec(self::getHexSEQ(substr($sPayload,72)));
		return $sData;
	}

	//(SNOOZE_ALERT_RPT)
    //public static function decodePayload__SNOOZE_ALERT_RPT($sPayload)
	//{
	//	unset($sData);
	//	$sData['LOC_INFO']=self::decodePayload__LOC_INFO(substr($sPayload,0,72));
	//	return $sData;
	//}
    //(STR_FWD_RPT)
    public static function decodePayload__STR_FWD_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
        	$sData['DATA']=hexdec(self::getHexSEQ(substr($sPayload,0)));
		return $sData;
	}
    //(ERROR_RPT)
    public static function decodePayload__ERROR_RPT($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
        	$sData['ERROR_CODE']=hexdec(self::getHexSEQ(substr($sPayload,0)));
		return $sData;
	}
	//(SYS_CONFIG_READ_REQ)
    public static function decodePayload__SYS_CONFIG_READ_REQ($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['SYS_CONFIG_READ_REQ']=substr($sPayload,0);
		return $sData;
	}
	//(SYS_CONFIG_WRITE_REQ)
    public static function decodePayload__SYS_CONFIG_WRITE_REQ($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=self::getTime();//hexdec(self::getHexSEQ(substr($sPayload,0,8)));
		$sData['SYS_CONFIG_WRITE_REQ']=substr($sPayload,0);
		return $sData;
	}
    //(LOC_INFO  36BYTE)
    public static function decodePayload__LOC_INFO($sPayload)
	{
		unset($sData);
		$sData['TIME_STAMP']=strtotime('+10year +5day -8hours',hexdec(self::getHexSEQ(substr($sPayload,0,8))));
		$sData['LATITUDE']=hexdec(self::getHexSEQ(substr($sPayload,8,8)))*180/33554432;
		$sData['LONGITUDE']=hexdec(self::getHexSEQ(substr($sPayload,16,8)))*360/67108864;
		$sData['HEADING']=hexdec(self::getHexSEQ(substr($sPayload,24,4)))*360/1024;
		$sData['ALTITUDE']=hexdec(self::getHexSEQ(substr($sPayload,28,4)));
		$sData['FIX_TYPE']=hexdec(self::getHexSEQ(substr($sPayload,32,2)));
		$sData['GPS_SPEED']=hexdec(self::getHexSEQ(substr($sPayload,34,2)));
		$sData['UNCERT_HORI']=hexdec(self::getHexSEQ(substr($sPayload,36,8)))*0.1;
		$sData['UNCERT_PERP']=hexdec(self::getHexSEQ(substr($sPayload,44,8)))*0.1;
		$sData['UNCERT_HORI_ANGLE']=hexdec(self::getHexSEQ(substr($sPayload,52,8)))*0.001;
		$sData['UNCERT_ALT']=hexdec(self::getHexSEQ(substr($sPayload,60,8)))*0.1;
		//$sData['BATT_STATUS']=hexdec(self::getHexSEQ(substr($sPayload,68,2)));
		//$sData['TEMPERATURE']=hexdec(self::getHexSEQ(substr($sPayload,70,2)));
        //$sData['STEP']=hexdec(self::getHexSEQ(substr($sPayload,72,4))); //新协议已去掉
		return $sData;
	}

	//
	public static function createConfigCmd($arrayData,$cmdNum)
	{
		if(!isset($arrayData['set_flag'])) return false;
		if(!isset($arrayData['set_data'])) return false;
		date_default_timezone_set('Asia/Shanghai');
		$sResult = false;
		$sSql = '';
		$aData = $arrayData['set_data'];
		switch($arrayData['set_flag'])
		{
			case 'device_set4': // System Config : Emergency Number
				$sSql = 'update any_elderly_info set device_set4=replace(device_set4,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '1C0050040800';
				if($cmdNum>1){$sResult = '1C0050050800';}
				$sNumber = $aData['NO1'];
				for($i=0;$i<strlen($sNumber);$i++)
				{
					$sResult .= dechex(ord($sNumber[$i]));
				}
				for($i=strlen($sNumber);$i<13;$i++)
				{
					$sResult .= '00';
				}
				$sNumber = $aData['NO2'];
				for($i=0;$i<strlen($sNumber);$i++)
				{
					$sResult .= dechex(ord($sNumber[$i]));
				}
				for($i=strlen($sNumber);$i<13;$i++)
				{
					$sResult .= '00';
				}
				break;
			case 'device_set5': // System Config : Port
				$sSql = 'update any_elderly_info set device_set5=replace(device_set5,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '040050040200';
				if($cmdNum>1){$sResult = '040050050200';}
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['PORT']),-4));
				break;
			case 'device_set6': //System Config : URL
				$sSql = 'update any_elderly_info set device_set6=replace(device_set6,"wait_update","__updated__") where device_sn="%s"';
				$sUrl = $aData['URL'];
				$sResult = self::getHexSEQ(substr('0000'.dechex(strlen($sUrl)+2),-4));
				if($cmdNum>1){$sResult .= '50050100';}else{$sResult .= '50040100';}
				for($i=0;$i<strlen($sUrl);$i++)
				{
					$sResult .= dechex(ord($sUrl[$i]));
				}
				break;
			case 'device_set7': // System Config : GPS Access Cycle
				$sSql = 'update any_elderly_info set device_set7=replace(device_set7,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '080050048000';
				if($cmdNum>1){$sResult = '080050058000';}
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME1']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME2']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME3']),-4));
				break;
			case 'device_set8': // System Config : Operation Time Zone
				$sSql = 'update any_elderly_info set device_set8=replace(device_set8,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '080050042000';
				if($cmdNum>1){$sResult = '080050052000';}
				$sResult .= substr('00'.dechex(date('H',$aData['TIME1'])),-2);
				$sResult .= substr('00'.dechex(date('i',$aData['TIME1'])),-2);
				$sResult .= substr('00'.dechex(date('H',$aData['TIME2'])),-2);
				$sResult .= substr('00'.dechex(date('i',$aData['TIME2'])),-2);
				$sResult .= substr('00'.dechex(date('H',$aData['TIME3'])),-2);
				$sResult .= substr('00'.dechex(date('i',$aData['TIME3'])),-2);
				break;
			case 'device_set9': //System Config : Temperature Access Cycle
				$sSql = 'update any_elderly_info set device_set9=replace(device_set9,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '080050044000';
				if($cmdNum>1){$sResult = '080050054000';}
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME1']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME2']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME3']),-4));
				break;
			case 'device_set10': // HealthCare Config : Temperature
				$sSql = 'update any_elderly_info set device_set10=replace(device_set10,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '050060040100';
				if($cmdNum>1){$sResult = '050060050100';}

				$sVal1 = (int)$aData['LOW_T'];
				$sVal2 = abs($sVal1) << 1;
				$sVal2 = base_convert($sVal2,10,2) + ($sVal1>=0?1:0);
				$sResult .= substr('00'.base_convert($sVal2,2,16),-2);

				$sVal1 = (int)$aData['HIGHT_T'];
				$sVal2 = abs($sVal1) << 1;
				$sVal2 = base_convert($sVal2,10,2) + ($sVal1>=0?1:0);
				$sResult .= substr('00'.base_convert($sVal2,2,16),-2);

				$sVal1 = (int)$aData['METHOD'];
				$sVal2 = base_convert($sVal1,10,16);
				$sResult .= substr('00'.$sVal2,-2);
				break;
			case 'device_set11': //System Config : Call Answer
				$sSql = 'update any_elderly_info set device_set11=replace(device_set11,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '030050040004';
				if($cmdNum>1){$sResult = '030050050004';}
				$sResult .= substr('00'.dechex($aData['EN']),-2);
				break;
			case 'device_set12': //System Config : Personal Info Change
				$sSql = 'update any_elderly_info set device_set12=replace(device_set12,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '050050040008';
				if($cmdNum>1){$sResult = '050050050008';}
				$sResult .= substr('00'.dechex($aData['SEX']),-2);
				$sResult .= substr('00'.dechex($aData['HIGHT']),-2);
				$sResult .= substr('00'.dechex($aData['WEIGHT']),-2);
				break;
			case 'device_set13': //Tracking General Config : Tracking RPT Cycle
				$sSql = 'update any_elderly_info set device_set13=replace(device_set13,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '080060140100';
				if($cmdNum>1){$sResult = '080060150100';}
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME1']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME2']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME3']),-4));
				break;
			case 'device_set14': // Tracking General Config :  ???
				$sSql = 'update any_elderly_info set device_set14=replace(device_set14,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '080060140100';
				if($cmdNum>1){$sResult = '080060150100';}
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME1']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME2']),-4));
				$sResult .= self::getHexSEQ(substr('0000'.dechex($aData['TIME3']),-4));
				break;
			case 'device_set15': //Tracking General Config : Agps Enable
				$sSql = 'update any_elderly_info set device_set15=replace(device_set15,"wait_update","__updated__") where device_sn="%s"';
				$sResult = '030060148000';
				if($cmdNum>1){$sResult = '030060158000';}
				$sResult .= self::getHexSEQ(substr('00'.dechex($aData['arg1']=='on'?1:0),-2));
				break;
			case 'beacons': //beacon_id
				$sSql = 'update any_elderly_info set beacons=replace(beacons,"wait_update","__updated__") where device_sn="%s"';
				unset($aData['FLG']);
				$beaconNum=count($aData);
				$sResult = self::getHexSEQ(substr('0000'.dechex(($beaconNum * 12 + 1)),-4));
				if($cmdNum>1){$sResult .= '602D';}else{$sResult .= '602C';}
				$sResult .=  substr('00'.dechex($beaconNum),-2);
				foreach($aData as $beacon){
					$sResult .= self::getHexSEQ(substr('00000000'.$beacon['beaconID'],-8));
					$sLat = (double) $beacon['lat'];
					$sLat = $sLat * 33554432 / 180;
					$sResult .= self::getHexSEQ(substr('00000000'.dechex(($sLat)),-8));
					$sLon = (double) $beacon['lon'];
					$sLon = $sLon * 67108864 / 360;
					$sResult .= self::getHexSEQ(substr('00000000'.dechex(($sLon)),-8));
				}
				break;
			case 'geofence':
				$sSql = 'update any_geofence set sync_flg="0" where device_sn="%s"';
				$geofenceNum = count($aData);
				$sResult = self::getHexSEQ(substr('0000'.dechex(($geofenceNum * 14 + 1)),-4));
				if($cmdNum>1){$sResult .= '600D';}else{$sResult .= '600C';}
				$sResult .= substr('00'.dechex($geofenceNum),-2);
				foreach($aData as $row){
					//var_dump($row);
					$sVal0 = (int)$row['geo_attr'];
					$sVal1 = (int)$row['geo_no'];
					$sVal2 = abs($sVal1) << 2;
					$sVal2 = base_convert($sVal2,10,2) + ($sVal0==1?1:0);
					$sResult .= substr('00'.base_convert($sVal2,2,16),-2);
					$sResult .= substr('00'.dechex($row['alert_type']),-2);
					$sResult .= self::getHexSEQ(substr('00000000'.dechex(((int)$row['radius'])),-8));
					$sLat = (double)$row['area_lat'];
					$sLat = $sLat * 33554432 / 180;
					$sLat = self::getHexSEQ(substr('00000000'.dechex(($sLat)),-8));
					$sResult .= $sLat;
					$sLon = (double)$row['area_lon'];
					$sLon = $sLon * 67108864 / 360;
					$sLon = self::getHexSEQ(substr('00000000'.dechex(($sLon)),-8));
					$sResult .= $sLon;
				}
				break;
			case 'remind_alert':
				$sSql = 'update any_remind_alert set sync_flg="0" where device_sn="%s" and id='.$aData['id'];
				//var_dump($sSql);
				//var_dump($arrayData);
				unset($sResult);
				$sResult = substr('00'.dechex($aData['bellring']),-2);
				$sResult .=  '01'; //每次只能设置1个
				$sResult .= substr('00'.dechex($aData['remind_no']),-2); //PILL_ID
				if($aData['sync_flg']=='3'){
					$sResult .= '000000000000000000';
				}else{
					//新增和修改时的格式相同
					//$aData['start_date'] = strtotime('-10year -5day +8hours',$aData['start_date']);
					$sResult .= substr('0000'.dechex(date('Y',$aData['start_date'])),-4);
					$sResult .= substr('00'.dechex(date('m',$aData['start_date'])),-2);
					$sResult .= substr('00'.dechex(date('d',$aData['start_date'])),-2);
					$aData['end_date']=strtotime('+'.$aData['end_date'].' day',$aData['start_date']); //结束时间=开始时间+N天
					$sResult .= substr('0000'.dechex(date('Y',$aData['end_date'])),-4);
					$sResult .= substr('00'.dechex(date('m',$aData['end_date'])),-2);
					$sResult .= substr('00'.dechex(date('d',$aData['end_date'])),-2);
					$sResult .= substr('00'.dechex($aData['repeat']),-2);
					$times = unserialize($aData['remark']);
					$sResult .= substr('00'.dechex(count($times)),-2);
					foreach($times as $k=>$v)
					{
						$sResult .= substr('00'.dechex(date('H',$v)),-2);
						$sResult .= substr('00'.dechex(date('i',$v)),-2);
					}
				}
				if($cmdNum>1){
					$sResult = self::getHexSEQ(substr('0000'.dechex(strlen($sResult)/2),-4)).'601D'.$sResult;
				}else{
					$sResult = self::getHexSEQ(substr('0000'.dechex(strlen($sResult)/2),-4)).'601C'.$sResult;
				}
				break;
			case 'pill_alert':
				$sSql = 'update any_pill_alert set sync_flg="0" where device_sn="%s" and id='.$aData['id'];
				//var_dump($aData);
				unset($sResult);
				$sResult = substr('00'.dechex($aData['bellring']),-2);
				$sResult .=  '01'; //每次只能设置1个
				$sResult .= substr('00'.dechex($aData['pill_no']),-2); //PILL_ID
				if($aData['sync_flg']=='3')
				{
					$sResult .= '000000000000000000';
				}else{
					//新增和修改时的格式相同
					//$aData['start_time'] = strtotime('-10year -5day +8hours',$aData['start_time']);
					//$aData['end_time']   = strtotime('-10year -5day +8hours',$aData['end_time']);
					$sResult .= substr('0000'.dechex(date('Y',$aData['start_time'])),-4);
					$sResult .= substr('00'.dechex(date('m',$aData['start_time'])),-2);
					$sResult .= substr('00'.dechex(date('d',$aData['start_time'])),-2);
					$sResult .= substr('0000'.dechex(date('Y',$aData['end_time'])),-4);
					$sResult .= substr('00'.dechex(date('m',$aData['end_time'])),-2);
					$sResult .= substr('00'.dechex(date('d',$aData['end_time'])),-2);
					$sResult .= substr('00'.dechex($aData['repeat']),-2);
					$times = unserialize($aData['remark']);
					$sResult .= substr('00'.dechex(count($times)),-2);
					foreach($times as $k=>$v)
					{
						$sResult .= substr('00'.dechex(date('H',$v)),-2);
						$sResult .= substr('00'.dechex(date('i',$v)),-2);
					}
				}
				if($cmdNum>1){
					$sResult = self::getHexSEQ(substr('0000'.dechex(strlen($sResult)/2),-4)).'601D'.$sResult;
				}else{
					$sResult = self::getHexSEQ(substr('0000'.dechex(strlen($sResult)/2),-4)).'601C'.$sResult;
				}
				break;
			default:
				$sResult = false;
		}
		$returnData['CMD_HEX']=$sResult;
		$returnData['CMD_SQL']=$sSql;
		//var_dump($returnData);
		return $returnData;
	}




}

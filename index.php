<?php
define("TOKEN", "barry");
$wechatObj = new wechatCallbackapiTest();
if (isset($_GET['echostr'])) {
    $wechatObj->valid();
}else{
    $wechatObj->responseMsg();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $RX_TYPE = trim($postObj->MsgType); 

            switch($RX_TYPE){ 
                case "event": 
                $result = $this->receiveEvent($postObj); 
                breadk; 
                case "text":
                $result = $this->receiveText($postObj); 
                breadk;
            }
            echo $result;
        }else{
            echo "";
            exit;
        }
    }

    private function receiveEvent($object){ 
        $content = ""; 
        switch ($object->Event){ 
            case "subscribe": 
            $content = "欢迎关注春暖花开再见咯";//这里是向关注者发送的提示信息 
            break; 
            case "unsubscribe": 
            $content = ""; 
            break; 
        } 
        $result = $this->transmitText($object,$content); 
        return $result; 
    } 
    private function transmitText($object,$content){ 
        $textTpl = "<xml> 
        <ToUserName><![CDATA[%s]]></ToUserName> 
        <FromUserName><![CDATA[%s]]></FromUserName> 
        <CreateTime>%s</CreateTime> 
        <MsgType><![CDATA[text]]></MsgType> 
        <Content><![CDATA[%s]]></Content> 
        <FuncFlag>0</FuncFlag> 
        </xml>"; 
        $result = sprintf($textTpl, $object->FromUserName, $object->$ToUserName, time(), $content); 
        return $result; 
    } 
	private function getWeather($city){ 
		$ch = curl_init();
		$dateArray = array();
        $city=urlencode($city);
        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityname='.$city;
        $header = array(
            'apikey: 0e4144670a2fb52eee0629e79123207c',
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $data = json_decode($res,true);
		$cityname=$data['retData']['city'];
		$citydate=$data['retData']['today']['date'];
		$cityweek=$data['retData']['today']['week'];
		$citytemp=$data['retData']['today']['curTemp'];
		$citypm=$data['retData']['today']['aqi'];
		$cityhightemp=$data['retData']['today']['hightemp'];
		$citylowtemp=$data['retData']['today']['lowtemp'];
		$citytype=$data['retData']['today']['type'];
		$cityfengli=$data['retData']['today']['fengli'];
		$cityfengxiang=$data['retData']['today']['fengxiang'];
		$cityforecast=$data['retData']['forecast'];
		$out="今日温度：".$citylowtemp."~".$cityhightemp."\n\r"."当前温度：".$citytemp."\n\r"."天气情况：".$citytype."\n\r"."PM2.5值：".$citypm."\n\r风向风力：".$cityfengxiang." ".$cityfengli;
        $dateArray[] = array("Title"=>$cityname."\n\r".$citydate." ".$cityweek,"Description"=>"","Picurl"=>"","Url" =>"");
        $dateArray[] = array("Title"=>$out,"Description"=>"","Picurl"=>"","Url" =>"");
		for($i=0;$i<count($cityforecast);$i++){
            $outstr="☀ ".$cityforecast[$i]["date"]." ".$cityforecast[$i]["week"]."\n\r气温：".$cityforecast[$i]["lowtemp"]."~".$cityforecast[$i]["hightemp"]."\n\r风力：".$cityforecast[$i]["fengxiang"]." ".$cityforecast[$i]["fengli"]."\n\r天气：".$cityforecast[$i]["type"];
            $dateArray[] = array("Title"=>$outstr,"Description"=>"","Picurl"=>"","Url" =>"");
        }
        return $outstr; 
    }
    private function receiveText($object){ 
        $content = ""; 		
        if($object->Content=="时间"){ 
			$content = date("Y-m-d H:i:s",time());//这里是向关注者发送的提示信息 
            $result = $this->transmitText($object,$content); 
		}elseif(strpos($object->Content,"天气")!== false){
			$city = explode(" ",$object->Content);
			$city = $city[0];
			$content = $this->getWeather($city);
			$result = $this->transmitText($object,$content);
		}else{
			$content = "发送:\n\r \"地区 天气\":获得天气情况\n\r\"时间\":获得当前时间";
            $result = $this->transmitText($object,$content); 
        } 
        
        return $result; 
    } 

}
?>
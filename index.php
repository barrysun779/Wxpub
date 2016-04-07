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
    private function receiveText($object){ 
        $content = ""; 
        switch ($object->Content){ 
            case "1": 
            $content = date("Y-m-d H:i:s",time());//这里是向关注者发送的提示信息 
            break; 
            default:
            $content = '随意';
        } 
        $result = $this->transmitText($object,$content); 
        return $result; 
    } 

}
?>
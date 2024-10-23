<?php

namespace App\Helper;

class Sms
{
    private static $uname='Yadaksadra';
    private static $pass='@Sadra72144';
    private static $from='+983000505';
    private static $url='https://ippanel.com/services.jspd';

    public static function send($numbers,$body){
        $rcpt_nm = $numbers;
        $param = array
        (
            'uname'=> self::$uname,
            'pass'=> self::$pass,
            'from'=> self::$from,
            'message'=>$body,
            'to'=>json_encode($rcpt_nm),
            'op'=>'send'
        );

        $handler = curl_init("https://ippanel.com/services.jspd");
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        return $response2;
    }

    public static function sendWithPatern($to,$pattern_code,$input_data){
        $url = "https://ippanel.com/patterns/pattern?username=" . 
        self::$uname . 
        "&password=" . 
        urlencode(self::$pass) . 
        "&from=".self::$from."&to=".
        json_encode($to) . 
        "&input_data=" . 
        urlencode(json_encode($input_data)). 
        "&pattern_code=$pattern_code";
        
        $handler = curl_init($url);
        
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $input_data);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        return $response;
    }
}

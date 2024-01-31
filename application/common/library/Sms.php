<?php

namespace app\common\library;

use fast\Random;
use think\Hook;

/**
* 短信验证码类
*/
class Sms
{

    /**
    * 验证码有效时长
    * @var int
    */
    protected static $expire = 120;

    /**
    * 最大允许检测的次数
    * @var int
    */
    protected static $maxCheckNums = 10;

    /**
    * 获取最后一次手机发送的数据
    *
    * @param   int    $mobile 手机号
    * @param   string $event  事件
    * @return  Sms
    */
    public static function get($mobile, $event = 'default') {
        $sms = \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
        ->order('id', 'DESC')
        ->find();
        Hook::listen('sms_get', $sms, null, true);
        return $sms ? $sms : null;
    }

    // /**
    //  * 发送验证码
    //  *
    //  * @param   int    $mobile 手机号
    //  * @param   int    $code   验证码,为空时将自动生成4位数字
    //  * @param   string $event  事件
    //  * @return  boolean
    //  */
    // public static function send($mobile, $code = null, $event = 'default')
    // {




    //     $code = is_null($code) ? Random::numeric(config('captcha.length')) : $code;
    //     $time = time();
    //     $ip = request()->ip();
    //     $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
    //     $result = Hook::listen('sms_send', $sms, null, true);
    //     if (!$result) {
    //         $sms->delete();
    //         return false;
    //     }
    //     return true;
    // }

    /**
    * 发送验证码
    *
    * @param   int    $mobile 手机号
    * @param   int    $code   验证码,为空时将自动生成4位数字
    * @param   string $event  事件
    * @return  boolean
    */
    public static function send($mobile, $code = null, $event = 'default', $areaCode) {
        $code = is_null($code) ? mt_rand(100000, 999999) : $code;
        $time = time();
        $ip = request()->ip();
        // 请求参数
        $appkey = "M1OUKn";
        $appcode = "1000";
        $appsecret = "pSyr6G";
        $phoneNumbers = $mobile; // Replace with actual phone numbers
        $msg = urlencode("【TNT】Dear Your verification, code is:".$code);

        $extend = "001"; // Replace with your desired extend value
        $url = "http://47.242.85.7:9090/sms/batch/v2?appkey={$appkey}&appcode={$appcode}&appsecret={$appsecret}&phone={$phoneNumbers}&msg={$msg}&extend={$extend}";

        // 发送请求并获取响应
        $response = file_get_contents($url);

        // 解析响应JSON
        $responseData = json_decode($response, true);
        // dump($responseData); die;
        // 处理响应数据
        if ($responseData['code'] === "00000") {
            $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
            // var_dump($gets);die;
            return $sms;

            // // 请求成功
            // $uid = $responseData['uid'];
            // $result = $responseData['result'];

            // foreach ($result as $item) {
            //     $status = $item['status'];
            //     $phone = $item['phone'];
            //     $desc = $item['desc'];
            //     echo "Status for {$phone}: {$status} - {$desc}\n";
            // }
        } else {
            // 请求异常
            $errorCode = $responseData['code'];
            $errorDesc = $responseData['desc'];
            echo "Error: {$errorCode} - {$errorDesc}\n";
        }
        die;



        // 请求地址
        $url = 'http://47.243.5.189:9090/sms/batch/v1';

        // 请求头
        $headers = [
            'Content-type: application/json',
        ];
        // 请求参数
        $data = [
            'appkey' => 'pqHQMU',
            'appcode' => '1000',
            'sign' => '',
            // 签名验证MD5（appkey+appsecret+timestamp），需要根据实际情况生成
            'uid' => '',
            // 可选
            'phone' => $mobile,
            // 手机号码，多个号码用逗号隔开
            'msg' => "【99vip】您的验证码是".$code."。如非本人操作，请忽略本短信",
            // 下发短信内容
            'timestamp' => time() * 1000,
            // 时间戳，当前时间5分钟内请求有效
            'extend' => '123',
            // 可选
        ];
        // 生成签名
        $data['sign'] = md5($data['appkey'] . 'bSvM6Y' . $data['timestamp']);
        // 发送请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $response = curl_exec($ch);
        curl_close($ch);
        $sms = \app\common\model\Sms::create(['event' => $event, 'mobile' => $mobile, 'code' => $code, 'ip' => $ip, 'createtime' => $time]);
        // var_dump($gets);die;
        return $sms;
    }


    /**
    * 发送通知
    *
    * @param   mixed  $mobile   手机号,多个以,分隔
    * @param   string $msg      消息内容
    * @param   string $template 消息模板
    * @return  boolean
    */
    public static function notice($mobile, $msg = '', $template = null) {
        $params = [
            'mobile' => $mobile,
            'msg' => $msg,
            'template' => $template
        ];
        $result = Hook::listen('sms_notice', $params, null, true);
        return $result ? true : false;
    }

    /**
    * 校验验证码
    *
    * @param   int    $mobile 手机号
    * @param   int    $code   验证码
    * @param   string $event  事件
    * @return  boolean
    */
    public static function check($mobile, $code, $event = 'default') {
        $time = time() - self::$expire;
        $sms = \app\common\model\Sms::where(['mobile' => $mobile, 'event' => $event])
        ->order('id', 'DESC')
        ->find();
        if ($sms) {
            if ($sms['createtime'] > $time && $sms['times'] <= self::$maxCheckNums) {
                $correct = $code == $sms['code'];
                if (!$correct) {
                    $sms->times = $sms->times + 1;
                    $sms->save();
                    return false;
                } else {
                    $result = Hook::listen('sms_check', $sms, null, true);
                    return $result;
                }
            } else {
                // 过期则清空该手机验证码
                self::flush($mobile, $event);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
    * 清空指定手机号验证码
    *
    * @param   int    $mobile 手机号
    * @param   string $event  事件
    * @return  boolean
    */
    public static function flush($mobile, $event = 'default') {
        \app\common\model\Sms::
        where(['mobile' => $mobile, 'event' => $event])
        ->delete();
        Hook::listen('sms_flush');
        return true;
    }
}
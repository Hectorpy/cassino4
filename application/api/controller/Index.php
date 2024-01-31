<?php

namespace app\api\controller;
use think\Db;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use think\Hook;
use app\common\library\Sms as Smslib;
use think\Validate;
/**
* 首页接口
*/
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
    * 首页
    *
    */
    public function index() {
        $this->success('请求成22功');
    }


    /**
    * 是否绑定 pix
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    */
    public function is_bank() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }

        $ret = db::name("bank")->where("user_id", $user->id)->find();
       
        if ($ret) {
             $data['data']=$ret;
             $data['code']=200;
            $this->success('vinculado', $data, 200);
        } else {
            $data['data']=[];
            $data['code']=101;
            $this->error('não vinculado', $data, 200);
        }
    }

    /**
    * 绑定pix
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string firstname 姓
    * @param string lastname 名
    * @param string email 邮箱
    * @param string phone 手机号
    * @param string cpf 税号
    * @param string pix  pix账号
    * @param string pixtype 类型
    */
    public function add_bank() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }

        $payerFirstName = $this->request->post('firstname');
        $payerLastName = $this->request->post('lastname');
        $payerEmail = $this->request->post('email');
        $payerPhone = $this->request->post('phone');
        $payerCPF = $this->request->post('cpf');
        $PIX = $this->request->post('pix');
        $pixType = $this->request->post('pixtype');

        if ($payerFirstName == '' || $payerLastName == '' || $payerEmail == '' || $payerPhone == '' || $payerCPF == '') {
            $this->error("O parâmetro obrigatório não está vazio");
        }

        if (db::name('bank')->where("pix", $PIX)->find()) {
            $this->error("conta pix já existe");
        }

        $ret = db::name('bank')->insert([
            'firstname' => $payerFirstName,
            'lastname' => $payerLastName,
            'email' => $payerEmail,
            'phone' => $payerPhone,
            'cpf' => $payerCPF,
            'user_id' => $user->id,
            'pix' => $PIX,
            'pixtype' => $pixType,
            'updatetime' => time(),
        ]);

        if ($ret) {
            $this->success('ligar com sucesso', [], 200);
        } else {
            $this->error('falha na ligação');
        }
    }


    public function getban() {


        $result = db::name("lb")->select();


        if ($result) {
            $this->success("ok", $result, 200);
        } else {
            $this->error("not data");
        }
    }
    /**
    * 获取配置参数
    *
    * @ApiMethod (POST)

    */
    public function get_c() {

        $result['chat'] = db::name("config")->where("name", "chat")->value("value");
        $result['chats'] = db::name("config")->where("name", "chats")->value("value");
        $result['appd'] = db::name("config")->where("name", "appd")->value("value");
        if ($result) {
            $this->success("ok", $result, 200);
        } else {
            $this->error("not data");
        }
    }



    function generateSignature($data, $key) {
        ksort($data);
        $paramStr = '';
        foreach ($data as $paramKey => $paramValue) {
            $paramStr .= $paramKey . '=' . $paramValue . '&';
        }
        $paramStr .= 'key=' . $key;
        $signature = hash('sha256', $paramStr);
        return $signature;
    }

    /**
    * 充值&提现
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $status 0充值1提现
    * @param string $is_get 是否充值奖励
    * @param string $money 金额
    */

    public function Withdraw_recharge() {


        $user = $this->auth->getUser();

        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $monss = $this->request->post('money');
        $is_get = $this->request->post('is_get')??0;
        $type = $this->request->post('status');
        $pay_get = db::name("config")->where("name", "pay_get")->value("value");
        if ($monss <= 0) {
            $this->success("O valor não está vazio");
        }


        $dp = db::name("dotop")->where("user_id", $user->id)->where("type", $type)->where("status", 0)->find();
        // if ($dp) {
        //     $this->error("Por favor, aguarde a revisão");
        // }
        $member = db::table('fa_user')->where('id', $user->id)->find();
        if ($type == 1) {
            //提现

            $mobile = $this->request->post('mobile');
            // $bankCard = $this->request->post('bankCard');
            // $bankName = $this->request->post('bankName');
            $With_count = db::name("dotop")->where("user_id", $user->id)->where("type", 1)->whereTime("addtime", "today")->count();
            $With_limt = db::name("dotop")->where("user_id", $user->id)->where("type", 1)->whereTime("addtime", "today")->sum("money");


            $payerFirstName = $this->request->post('payerFirstName')??"1";
            $payerLastName = $this->request->post('payerLastName')??"1";
            $payerEmail = $this->request->post('payerEmail')??"1";
            $payerPhone = $this->request->post('payerPhone')??"1";
            $payerCPF = $this->request->post('payerCPF')??"1";
            $PIX = $this->request->post('PIX')??"1";
            $pixType = $this->request->post('pixType')??"1";

            if ($payerFirstName == '' || $payerLastName == '' || $payerEmail == '' || $payerPhone == '' || $payerCPF == '') {
                $this->error("O parâmetro obrigatório não está vazio");
            }
            db::name('user')->where("id", $this->auth->id)->update([
                'payerFirstName' => $payerFirstName,
                'payerLastName' => $payerLastName,
                'payerEmail' => $payerEmail,
                'payerPhone' => $payerPhone,
                'payerCPF' => $payerCPF,
                'PIX' => $PIX,
                'pixtype' => $pixType,
                'updatetime' => time(),
            ]);




            $vips = db::name("vips")->where("level", $this->auth->level)->find();

            if ($With_count >= $vips['day_withdraw']) {

                $this->error("O limite de retirada de hoje foi atingido");

            }
            if ($With_limt >= $vips['withdraw_limt']) {

                $this->error("O valor da retirada de hoje atingiu o limite superior");

            }

            if ($monss >= $vips['single_withdraw']) {

                $this->error("A retirada excede o limite único");

            }

            $info = "Os clientes solicitam subpontos";
            if ($user->money < $monss) {
                $this->success("Valor insuficiente disponível para retirada");
            }

            $withdrawa = $vips['withdraw']/100;
            $sxf = ($monss*$withdrawa);

            $news = $monss-$sxf;
            db::table('fa_user')->where('id', $user->id)->setDec("money", $news);
            //  \app\common\model\User::money(-$money, $this->auth->id, "提现扣除".$money, 7);

            $result = db::name('dotop')->insert([
                'user_id' => $user->id,
                'username' => $member['username'],
                'me_user' => $member['username'],
                'dai_id' => 0,
                'mobile' => $mobile??"",
                'bankCard' => $bankCard??"",
                'bankName' => $bankName??"",
                'info' => $info,
                'status' => 0,
                'sxf' => $sxf??0,
                'is_get' => $is_get,
                'type' => $type,
                'money' => $monss,
                'addtime' => time(),
                'uptime' => time(),
            ]);

            if ($result) {
                $this->success("A inscrição foi enviada e aguarda análise", 200, 200);
            } else {
                $this->error("not data");
            }


        } else {
            $payerFirstName = $this->request->post('payerFirstName')??"1";
            $payerLastName = $this->request->post('payerLastName')??"1";
            $payerEmail = $this->request->post('payerEmail')??"1";
            $payerPhone = $this->request->post('payerPhone')??"1";
            $payerCPF = $this->request->post('payerCPF')??"1";

            if ($payerFirstName == '' || $payerLastName == '' || $payerEmail == '' || $payerPhone == '' || $payerCPF == '') {
                $this->error("O parâmetro obrigatório não está vazio");
            }
            db::name('user')->where("id", $this->auth->id)->update([
                'payerFirstName' => $payerFirstName,
                'payerLastName' => $payerLastName,
                'payerEmail' => $payerEmail,
                'payerPhone' => $payerPhone,
                'payerCPF' => $payerCPF,
                'updatetime' => time(),
            ]);


            if ($pay_get == 1) {


                //参数
                $merchantNo = "3018230613001";
                $merchantOrderNo = 'Bpay'.date("YmdHis").rand(100000, 999999);
                $countryCode = "BRA";
                $currencyCode = "BRL";
                $paymentType = "900410282001";
                $paymentAmount = $monss;
                $goods = "iphone";
                $notifyUrl = "https://server.pg8808.top/api/index/pay_notify";
                $pageUrl = "https://pg8808.top/";
                $returnedParams = '回传参数';
                $extendedParams = 'payerFirstName^'.$payerFirstName.'|payerLastName^'.$payerLastName.'|payerEmail^'.$payerEmail.'|payerPhone^'.$payerPhone.'|payerCPF^'.$payerCPF;


                //签名参数组成待签名串
                $signStr = "countryCode=" . $countryCode . "&currencyCode=" . $currencyCode . "&extendedParams=" . $extendedParams . "&goods=" . $goods . "&merchantNo=" . $merchantNo . "&merchantOrderNo=" . $merchantOrderNo . "&notifyUrl=" . $notifyUrl . "&pageUrl=" . $pageUrl . "&paymentAmount=" . $paymentAmount . "&paymentType=" . $paymentType . "&returnedParams=" . $returnedParams;



                $sign = "";


                //商户私钥
                $merchant_private_key = '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALMNUSkKzZraPQTUEQIFOSCiaOGL234RzRbmJ4xDzBHy2RAyNdaapM+l3X6P9rmxEZ3Rj+0X/ljqYIkGUeBFagAmitza0Eb4n7Xnfur9T8U3+NkTS9Ed9Z34nlBzC92/Z5GMG2pp6mTf70XC8bFdqlBsW8nPVQsw9jBc4SRCZk1HAgMBAAECgYBCP1MgFFcuTED3YF9KmBQi9vRHPy/e3Uc8ibtoMk129ptJWsqAtIb2LTBee3WWDuWttrPBzXbV/yHokOYKTKSC+fMznFBf2Ulke4Swvd/GlVDdOeMgSoZfFBfZrVX4zSTwallmYsr0w5WqM4D3jDlOZLkANQUkkpgHDbXZrSu1wQJBAPQov+2qgVcFXY6nwQ5To5QQYhnXIkNmwbBHMQ3BljLfbCrohrno+cI3AhnHtxzIz0yfHoYDk9feXPiPWKoswgsCQQC7vEIYGI1c7GE/fWv0Yoj3XGb7eqgfJ3a8Jab8DlUHnV8S4nmlLi/stP5kCrHmf8GA9R8ErgSnQUdLsJEIFAM1AkEAuN0lvLiNp6rLVJjVhphzUUc6T+Bg8/GYk3TDwmuh4rDhwHdAkwDAInnt4EEj9upgct5DiSqqRRb7A8PdWTP8UwJAamYU84EexTZ3GzujLnuV8tOczhRDKnz8Tz/rttkMmec4FgTjOpnFsZsWvm5NSzzG16aU8NsLahuWI7CrUe+9rQJAAWRb0AOajY3Yy+GocB6e3Z/VmIjwx2I+S1rCiFSy6S1XeBW2HviDiiyP9caEypn1Fqg+/Mm98y1rAPnjWde5NQ==
-----END PRIVATE KEY-----';


                //签名
                $merchant_private_key = openssl_get_privatekey($merchant_private_key);
                openssl_sign($signStr, $sign_info, $merchant_private_key, OPENSSL_ALGO_MD5);
                $sign = base64_encode($sign_info);


                //提交参数
                $postdata = array(
                    'merchantNo' => $merchantNo,
                    'merchantOrderNo' => $merchantOrderNo,
                    'countryCode' => $countryCode,
                    'currencyCode' => $currencyCode,
                    'paymentType' => $paymentType,
                    'paymentAmount' => $paymentAmount,
                    'goods' => $goods,
                    'notifyUrl' => $notifyUrl,
                    'pageUrl' => $pageUrl,
                    'returnedParams' => $returnedParams,
                    'extendedParams' => $extendedParams,
                    'sign' => $sign
                );
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, "https://api.bpay.tv/api/v2/payment/order/create");
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                $data = json_encode($postdata);
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $res = curl_exec($curl);
                $errorno = curl_errno($curl);
                curl_close($curl);

                $recode = json_decode($res);
                //   dump();die;


                if ($recode->code == 200) {

                    $info = "Clientes solicitam pontos";

                    $result = db::name('dotop')->insert([
                        'user_id' => $user->id,
                        'username' => $member['username'],
                        'me_user' => $member['username'],
                        'dai_id' => 0,
                        'mobile' => $mobile??"",
                        // 'bankCard' => $bankCard??"",
                        // 'bankName' => $bankName??"",
                        'info' => $info,
                        'status' => 0,
                        'orderNo' => $merchantOrderNo,
                        'sxf' => $sxf??0,
                        'is_get' => $is_get,
                        'type' => $type,
                        'money' => $monss,
                        'addtime' => time(),
                        'uptime' => time(),
                    ]);

                    $this->success("ok", $recode->data, 200);
                } else {
                    $this->error($recode->message);
                }


            } else {
                $curl = curl_init();

                $requestData = array(
                    "appId" => "3203fd523974a60b868adca08d98cb5b",
                    "merOrderNo" => 'BetcatPay'.date("YmdHis").rand(100000, 999999),
                    "currency" => "BRL",
                    "amount" => $monss,
                    "returnUrl" => "https://pg8808.top/",
                    "notifyUrl" => "https://server.pg8808.top/api/index/BetcatPay_notify"
                );
                $merchantKey = "b7c19e9b217c14f6e9ba69b7f5337d0a"; // 替换为你的商户密钥

                // 生成签名
                $signature = $this->generateSignature($requestData, $merchantKey);

                // 将签名加入请求数据
                $requestData["sign"] = $signature;

                $data = json_encode($requestData);

                $headers = array(
                    'User-Agent: Apifox/1.0.0 (https://apifox.com)',
                    'Content-Type: application/json'
                );
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://v1.a.betcatpay.com/api/v1/payment/order/create', // 请替换为实际的 URL
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $data,
                    CURLOPT_HTTPHEADER => $headers,
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                $recode = json_decode($response);

                if ($recode->code == 0) {

                    $info = "Clientes solicitam pontos";

                    $result = db::name('dotop')->insert([
                        'user_id' => $user->id,
                        'username' => $member['username'],
                        'me_user' => $member['username'],
                        'dai_id' => 0,
                        'mobile' => $mobile??"",
                        // 'bankCard' => $bankCard??"",
                        // 'bankName' => $bankName??"",
                        'info' => $info,
                        'status' => 0,
                        'orderNo' => $requestData['merOrderNo'],
                        'sxf' => $sxf??0,
                        'is_get' => $is_get,
                        'type' => $type,
                        'money' => $monss,
                        'addtime' => time(),
                        'uptime' => time(),
                    ]);
                    $datas['paymentUrl'] = $recode->data->params->url;
                    $datas['qrcode'] = $recode->data->params->qrcode;

                    $this->success("ok", $datas, 200);
                } else {
                    $this->error($recode->msg);
                }

            }
        }
        die;
        // dump($result);die;
        if ($result) {
            $this->success("A inscrição foi enviada e aguarda análise", 200);
        } else {
            $this->error("not data");
        }
    }





    /**
    * BetcatPay回调
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    */
    public function BetcatPay_notify() {

        //获取回调
        $json_raw = file_get_contents("php://input");
        $json_data = (array)json_decode($json_raw);


        //打印日志
        $file = "notic_" . date("Ymd") . ".log";
        $ct = date("Y-m-d H:i:s", time());




        //排序参数
        function asc_sort($params = array()) {
            if (!empty($params)) {
                $p = ksort($params);
                if ($p) {
                    $str = '';
                    foreach ($params as $k => $val) {
                        $str .= $k . '=' . $val . '&';
                    }
                    $strs = rtrim($str, '&');
                    return $strs;
                }
            }
            return false;
        }
        error_log("收到代付回调数据" . var_export($json_data, true) . " \r\n", 3, $file);
        $currencyCode = $json_data['currencyCode'];
        $countryCode = $json_data['countryCode'];
        $transferTime = $json_data['transferTime'];
        $transferAmount = $json_data['transferAmount'];
        $transferStatus = $json_data['transferStatus'];
        $orderTime = $json_data['orderTime'];
        $orderAmout = $json_data['orderAmout'];
        $orderNo = $json_data['orderNo'];
        $merchantOrderNo = $json_data['merchantOrderNo'];
        $merchantNo = $json_data['merchantNo'];
        $sign = $json_data['sign'];
        $postdata = array(
            'currencyCode' => $currencyCode,
            'countryCode' => $countryCode,
            'transferTime' => $transferTime,
            'transferAmount' => $transferAmount,
            'transferStatus' => $transferStatus,
            'orderTime' => $orderTime,
            'orderAmout' => $orderAmout,
            'orderNo' => $orderNo,
            'merchantOrderNo' => $merchantOrderNo,
            'merchantNo' => $merchantNo
        );
        $signStr = asc_sort($postdata);
        error_log("组装的签名数据" . var_export($signStr, true) . " \r\n", 3, $file);



        $pay_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWifnUddUUr07LTh6QCRfPG006l2Qo5byi47X8h3ZHmTJAU0c1kJlLRgsJml5hiqydEk7IVgA1nBlsk8P3m477idqoGpKYG5L/WrS3SY/WE/cjMUoaMrckHYYc28q3dPPc6/7pECZ8efRtmgE3s2vmAsNH5EJtxUV6UcuvqH7PwQIDAQAB
-----END PUBLIC KEY-----';

        //验证
        $pay_public_key = openssl_get_publickey($pay_public_key);
        $flag = openssl_verify($signStr, base64_decode($sign), $pay_public_key, OPENSSL_ALGO_MD5);

        if ($flag) {


            $ordeinfo = db::name("dotop")->where("orderNo", $merchantOrderNo)->find();
            $result = db::name("dotop")->
            where("id", $ordeinfo['id'])->update(['status' => 1, 'info' => "pontuado com sucesso", "uptime" => time()]);

            db::name('mx')->insert([
                'uid' => $ordeinfo['user_id'],
                'info' => "pontuado com sucesso",
                'type' => 1,
                'money' => $ordeinfo['money'],
                'addtime' => time(),
            ]);


            echo "SUCCESS";
            error_log("验签成功" . " \r\n", 3, $file);
        } else {
            echo "Verification Error";
            error_log("验签失败 \r\n", 3, $file);
        }



    }



    /**
    * Bpay回调
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    */
    public function pay_notify() {




        //获取回调
        $json_raw = file_get_contents("php://input");

        if (!$json_raw) {
            echo"未获取回调参数"; die;
        }

        $json_data = (array)json_decode($json_raw, true, 512, JSON_BIGINT_AS_STRING);

        //打印日志
        $file = "notic_" . date("Ymd") . ".log";
        $ct = date("Y-m-d H:i:s", time());


        error_log("收到的回调数据" . var_export($json_data, true) . " \r\n", 3, $file);

        $countryCode = $json_data['countryCode'];
        $orderNo = $json_data['orderNo'];
        $orderTime = $json_data['orderTime'];
        $orderAmount = $json_data['orderAmount'];
        $sign = $json_data['sign'];
        $paymentTime = $json_data['paymentTime'];
        $merchantOrderNo = $json_data['merchantOrderNo'];
        $paymentAmount = $json_data['paymentAmount'];
        $currencyCode = $json_data['currencyCode'];
        $paymentStatus = $json_data['paymentStatus'];
        $returnedParams = $json_data['returnedParams'];
        $merchantNo = $json_data['merchantNo'];
        $signStr = "countryCode=" . $countryCode . "&currencyCode=" . $currencyCode . "&merchantNo=" . $merchantNo . "&merchantOrderNo=" . $merchantOrderNo . "&orderAmount=" . $orderAmount . "&orderNo=" . $orderNo . "&orderTime=" . $orderTime . "&paymentAmount=" . $paymentAmount . "&paymentStatus=" . $paymentStatus . "&paymentTime=" . $paymentTime . "&returnedParams=" . $returnedParams;

        error_log("组装的签名数据" . var_export($signStr, true) . " \r\n", 3, $file);



        $pay_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWifnUddUUr07LTh6QCRfPG006l2Qo5byi47X8h3ZHmTJAU0c1kJlLRgsJml5hiqydEk7IVgA1nBlsk8P3m477idqoGpKYG5L/WrS3SY/WE/cjMUoaMrckHYYc28q3dPPc6/7pECZ8efRtmgE3s2vmAsNH5EJtxUV6UcuvqH7PwQIDAQAB
-----END PUBLIC KEY-----';

        //验证


        $pay_public_key = openssl_get_publickey($pay_public_key);

        $flag = openssl_verify($signStr, base64_decode($sign), $pay_public_key, OPENSSL_ALGO_MD5);




        if ($flag) {

            $ordeinfo = db::name("dotop")->where("orderNo", $merchantOrderNo)->find();
            if (!empty($ordeinfo)) {
                // 数据库查询有结果
                if ($ordeinfo['status'] == 0) {
                    \app\common\model\User::money($ordeinfo['money'], $ordeinfo['user_id'], "Recarga com sucesso".$ordeinfo['money']);
                    db::name("dotop")->where("id", $ordeinfo['id'])->update(['status' => 1, 'info' => "Recarga com sucesso", "uptime" => time()]);
                    db::name('mx')->insert([
                        'uid' => $ordeinfo['user_id'],
                        'info' => "Recarga com sucesso",
                        'type' => 1,
                        'money' => $ordeinfo['money'],
                        'addtime' => time(),
                    ]);

                }
            }
            echo "SUCCESS";
            error_log("验签成功" . " \r\n", 3, $file);
        } else {
            echo "Verification Error";
            error_log("验签失败".openssl_error_string()." \r\n", 3, $file);
        }

    }







    /**
    * Bpay提现回调
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    */
    public function Bpay_Tnotify() {




        //获取回调
        $json_raw = file_get_contents("php://input");

        if (!$json_raw) {
            echo"未获取回调参数"; die;
        }

        $json_data = (array)json_decode($json_raw, true, 512, JSON_BIGINT_AS_STRING);

        //打印日志
        $file = "notic_" . date("Ymd") . ".log";
        $ct = date("Y-m-d H:i:s", time());


        error_log("收到的回调数据" . var_export($json_data, true) . " \r\n", 3, $file);

        $countryCode = $json_data['countryCode'];
        $orderNo = $json_data['orderNo'];
        $orderTime = $json_data['orderTime'];
        $orderAmount = $json_data['orderAmount'];
        $sign = $json_data['sign'];
        $paymentTime = $json_data['paymentTime'];
        $merchantOrderNo = $json_data['merchantOrderNo'];
        $paymentAmount = $json_data['paymentAmount'];
        $currencyCode = $json_data['currencyCode'];
        $paymentStatus = $json_data['paymentStatus'];
        $returnedParams = $json_data['returnedParams'];
        $merchantNo = $json_data['merchantNo'];
        $signStr = "countryCode=" . $countryCode . "&currencyCode=" . $currencyCode . "&merchantNo=" . $merchantNo . "&merchantOrderNo=" . $merchantOrderNo . "&orderAmount=" . $orderAmount . "&orderNo=" . $orderNo . "&orderTime=" . $orderTime . "&paymentAmount=" . $paymentAmount . "&paymentStatus=" . $paymentStatus . "&paymentTime=" . $paymentTime . "&returnedParams=" . $returnedParams;

        error_log("组装的签名数据" . var_export($signStr, true) . " \r\n", 3, $file);



        $pay_public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDWifnUddUUr07LTh6QCRfPG006l2Qo5byi47X8h3ZHmTJAU0c1kJlLRgsJml5hiqydEk7IVgA1nBlsk8P3m477idqoGpKYG5L/WrS3SY/WE/cjMUoaMrckHYYc28q3dPPc6/7pECZ8efRtmgE3s2vmAsNH5EJtxUV6UcuvqH7PwQIDAQAB
-----END PUBLIC KEY-----';

        //验证


        $pay_public_key = openssl_get_publickey($pay_public_key);

        $flag = openssl_verify($signStr, base64_decode($sign), $pay_public_key, OPENSSL_ALGO_MD5);




        if ($flag) {

            $ordeinfo = db::name("dotop")->where("orderNo", $merchantOrderNo)->find();
            if (!empty($ordeinfo)) {
                // 数据库查询有结果
                if ($ordeinfo['status'] == 0) {
                    \app\common\model\User::money($ordeinfo['money'], $ordeinfo['user_id'], "Recarga com sucesso".$ordeinfo['money']);
                    db::name("dotop")->where("id", $ordeinfo['id'])->update(['status' => 1, 'info' => "Recarga com sucesso", "uptime" => time()]);
                    db::name('mx')->insert([
                        'uid' => $ordeinfo['user_id'],
                        'info' => "Recarga com sucesso",
                        'type' => 1,
                        'money' => $ordeinfo['money'],
                        'addtime' => time(),
                    ]);

                }
            }
            echo "SUCCESS";
            error_log("验签成功" . " \r\n", 3, $file);
        } else {
            echo "Verification Error";
            error_log("验签失败".openssl_error_string()." \r\n", 3, $file);
        }

    }




    /**
    * 发送邮箱验证码或短信验证码
    * @param string  $username   邮箱&手机号
    * @param string $event 事件名称register或者login
    * @return boolean
    */
    public function send_email_mobile($code = null) {
        $username = $this->request->post('username');


        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($username && !Validate::is($username, "email")) {
                $this->error(__('Email is incorrect'));
            }

            $event = "reg";
            $code = is_null($code) ? mt_rand(100000, 999999) : $code;
            $tres = mailTo($username, "凭证", "您的验证码是".$code);
            db::name("ems")->insert(['email' => $username, 'event' => $event, 'code' => $code, 'times' => 0, 'createtime' => time(),]);
            if ($tres == 1) {
                $this->success(__('邮箱发送成功'));
            } else {
                $this->error(__($tres));
            }
        } else {

            $mobile = "55".$username;
            $event = $this->request->request("event");
            $event = $event ? $event : 'register';

            // if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            //     $this->error(__('手机号不正确'));
            // }
            $last = Smslib::get($mobile, $event);
            if ($last && time() - $last['createtime'] < 60) {
                $this->error(__('发送频繁'));
            }
            $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
            if ($ipSendTotal >= 5) {
                $this->error(__('发送频繁'));
            }
            if ($event) {
                $userinfo = \app\common\model\User::getByMobile($mobile);
                if ($event == 'register' && $userinfo) {
                    //已被注册
                    $this->error(__('已被注册'));
                } elseif (in_array($event, ['changemobile']) && $userinfo) {
                    //被占用
                    $this->error(__('已被占用'));
                } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                    //未注册
                    $this->error(__('未注册'));
                }
            }

            $ret = Smslib::send($mobile, mt_rand(100000, 999999), $event, 86);
            if ($ret) {
                $this->success(__('短信发送成功'),[],200);
            } else {
                $this->error(__('发送失败，请检查短信配置是否正确'));
            }
        }
        $this->error(__('非法请求'));

    }

    /**
    * 获取内容详情
    *@param string $type 1tg内容 2充值内容 3充值送礼金内容 4推广下方内容
    * @ApiMethod (POST)

    */
    public function get_contents() {
        $type = $this->request->post('type');
        $data = db::name("contents")->where("type", $type)->find();

        if ($data) {
            $this->success("list", $data, 200);
        } else {
            $this->error("not data");
        }


    }
    
    
    
    /**
    * 返利记录
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $startDateTime 开始时间
    * @param string $endDateTime 结束时间
    * @param string $page 从1开始
    * @param string $list 分页长度,默认10
    */
    public function rebate_list() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $startDateTime = $this->request->post('startDateTime');
        $endDateTime = $this->request->post('endDateTime');
        $page = $this->request->post('page') ?? 1;
        $list = $this->request->post('list') ?? 10;
        $where['user_id'] = $user->id;
        $where['type'] = 1;
        if ($startDateTime) {
            $where['create_at'] = ['>=',
                $startDateTime];
            $where['create_at'] = ['<=',
                $endDateTime];
        }

        $ret = db::name("user_money_log")->where($where)->order('id desc')->page($page, $list)->select();
        
        foreach ($ret as &$v) {
            $v['createtime']=date("Y-m-d H:i:s",$v['createtime']);
        }
        $retcount = db::name("user_money_log")->count();
        $data['data'] = $ret;
        $data['tal'] = $retcount;
        if ($ret) {
            $this->success('返利记录', $data, 200);
        } else {
            $this->error('no data');
        }
    }




    /**
    * 流水记录
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $startDateTime 开始时间
    * @param string $endDateTime 结束时间
    * @param string $page 从1开始
    * @param string $list 分页长度,默认10
    */
    public function find_bets() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $startDateTime = $this->request->post('startDateTime');
        $endDateTime = $this->request->post('endDateTime');
        $page = $this->request->post('page') ?? 1;
        $list = $this->request->post('list') ?? 10;
        $where['uid'] = $user->id;

        if ($startDateTime) {
            $where['create_at'] = ['>=',
                $startDateTime];
            $where['create_at'] = ['<=',
                $endDateTime];
        }

        $ret = db::name("game_record")->where($where)->order('id desc')->page($page, $list)->select();
        $retcount = db::name("game_record")->count();
        $data['data'] = $ret;
        $data['tal'] = $retcount;
        if ($ret) {
            $this->success('投注记录', $data, 200);
        } else {
            $this->error('no data');
        }
    }



    /**
    * 获取三级客户信息
    * @ApiMethod (POST)
    * type 0充值1提现
    */
    public function allThirdLevelUsers() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }

        $tier1Uids = db::name("user")->where('pid', $user->id)->column("id")??"";
        if (!$tier1Uids) {
            $tier2 = [];
            $tier3 = [];
        } else {
            $tier2 = db::name("user")->whereIn('pid', $tier1Uids)->column("id");
            if (!$tier2) {
                $tier3 = [];
            } else {
                $tier3 = db::name("user")->whereIn('pid', $tier2)->column("id");
            }
        }

        if ($tier1Uids) {
            foreach ($tier1Uids as $k => $v) {
                $tier1Uidss[$k]['id'] = $v;
                $tier1Uidss[$k]['mobile'] = db::name("user")->where('id', $v)->value('username');
                $tier1Uidss[$k]['createtime'] = date("Y-m-d H:i:s", db::name("user")->where('id', $v)->value('createtime'));
                $tier1Uidss[$k]['total_recharge'] = db::name("dotop")->where(['status' => 1, 'user_id' => $v, 'type' => 0])->sum("money");
                $tier1Uidss[$k]['total_bet'] = db::name("game_record")->where(['uid' => $v])->sum("valid_bet_amount");
            }

        }

        if ($tier2) {

            foreach ($tier2 as $k => $v) {
                $tier22[$k]['id'] = $v;
                $tier22[$k]['mobile'] = db::name("user")->where('id', $v)->value('username');
                $tier22[$k]['createtime'] = date("Y-m-d H:i:s", db::name("user")->where('id', $v)->value('createtime'));
                $tier22[$k]['total_recharge'] = db::name("dotop")->where(['status' => 1, 'user_id' => $v, 'type' => 0])->sum("money");
                $tier22[$k]['total_bet'] = db::name("game_record")->where(['uid' => $v])->sum("valid_bet_amount");

            }

        }
        if ($tier3) {
            foreach ($tier3 as $k => $v) {
                $tier33[$k]['id'] = $v;
                $tier33[$k]['mobile'] = db::name("user")->where('id', $v)->value('username');
                $tier33[$k]['createtime'] = date("Y-m-d H:i:s", db::name("user")->where('id', $v)->value('createtime'));
                $tier33[$k]['total_recharge'] = db::name("dotop")->where(['status' => 1, 'user_id' => $v, 'type' => 0])->sum("money");
                $tier33[$k]['total_bet'] = db::name("game_record")->where(['uid' => $v])->sum("valid_bet_amount");
            }

        }

        $item['tier1'] = $tier1Uidss??[]; //一级人数
        $item['tier2'] = $tier22??[]; //二级人数
        $item['tier3'] = $tier33??[]; //三级人数

        $item['one_num'] = count($tier1Uids);
        $item['one_total_recharge'] = db::name("dotop")->whereIn("user_id", $tier1Uids)->where(['status' => 1, 'type' => 0])->sum("money");
        $item['one_total_wih'] = db::name("dotop")->whereIn("user_id", $tier1Uids)->where(['status' => 1, 'type' => 1])->sum("money");

        $item['two_num'] = count($tier2);
        $item['two_total_recharge'] = db::name("dotop")->whereIn("user_id", $tier2)->where(['status' => 1, 'type' => 0])->sum("money");
        $item['two_total_wih'] = db::name("dotop")->whereIn("user_id", $tier2)->where(['status' => 1, 'type' => 1])->sum("money");

        $item['three_num'] = count($tier3);
        $item['three_total_recharge'] = db::name("dotop")->whereIn("user_id", $tier3)->where(['status' => 1, 'type' => 0])->sum("money");
        $item['three_total_wih'] = db::name("dotop")->whereIn("user_id", $tier3)->where(['status' => 1, 'type' => 1])->sum("money");

        if ($item) {
            $this->success("list", $item, 200);
        } else {
            $this->error("not data");
        }


    }


    /**
    * 获取我的vip信息内容
    * @ApiMethod (POST)
    */
    public function get_vips() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $vips = db::name("vips")->select();
        foreach ($vips as &$v) {
            $user->level >= $v['id']?$v['is_vip'] = 1:$v['is_vip'] = 0;
        }
        if ($vips) {
            $this->success("list", $vips, 200);
        } else {
            $this->error("not data");
        }


    }
    /**
    * 获取我的vip信息内容
    * @ApiMethod (POST)
    */
    public function vips_info() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $id = $this->request->post('level');
        $vips = db::name("vips")->where("level", $id)->find();

        $vips['total_recharge'] = db::name("dotop")->where(['status' => 1, 'user_id' => $user->id, 'type' => 0])->sum("money");
        $vips['total_bet'] = db::name("game_record")->where(['uid' => $user->id])->sum("valid_bet_amount");

        $vips['recharge'] = round($vips['total_recharge'], 2) >= round($vips['running_money'], 2)?
        round($vips['running_money'], 2)."/".round($vips['running_money'], 2):
        round($vips['total_recharge'], 2)."/".round($vips['running_money'], 2);
        $vips['bet'] = round($vips['total_bet'], 2) >= round($vips['running_water'], 2)?
        round($vips['running_water'], 2)."/".round($vips['running_water'], 2):
        round($vips['running_water'], 2)."/".round($vips['total_bet'], 2);

        if ($vips['running_money'] != 0) {
            $recharge_b = ($vips['total_recharge'] / $vips['running_money']) * 100;
        } else {
            $recharge_b = 0; // 或者其他你认为适合的默认值
        }

        if ($vips['running_water'] != 0) {
            $bet_b = ($vips['total_bet'] / $vips['running_water']) * 100;
        } else {
            $bet_b = 0;
        }


        $vips['recharge_b'] = $recharge_b >= 100?100: $recharge_b;
        $vips['bet_b'] = $bet_b >= 100?100: $bet_b;


        if ($vips) {
            $this->success("list", $vips, 200);
        } else {
            $this->error("not data");
        }
    }




    /**
    * 获取充值配置
    * @ApiMethod (POST)
    */
    public function get_rechlist() {

        $vips = db::name("rechlist")->select();
        if ($vips) {
            $this->success("list", $vips, 200);
        } else {
            $this->error("not data");
        }
    }

    /**
    * 获取充值&提现 记录
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string  $status 0提现 1充值
    * @param string $page 从1开始
    * @param string $list 分页长度,默认10
    */
    public function Wit_rech_list() {
        $status = $this->request->post('status');
        $page = $this->request->post('page') ?? 1;
        $list = $this->request->post('list') ?? 10;
        $ret = db::name("dotop")->where("type", $status)->order('id desc')->page($page, $list)->select();
        $retcount = db::name("dotop")->where("type", $status)->count();

        foreach ($ret as $k => $v) {
            $ret[$k]['addtime_txt'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        $data['data'] = $ret??[];
        $data['tal'] = $retcount;
        if ($ret) {
            $this->success(__('ok'), $data, 200);
        } else {
            $this->success(__('ok'), $data, 200);
        }
    }



    /**
    * 获取通知列表
    * @return boolean
    */
    public function get_msg() {

        $ret = db::name("msg")->select();
        foreach ($ret as $k => $v) {
            $ret[$k]['addtime_txt'] = date("Y-m-d H:i:s", $v['addtime']);
        }
        if ($ret) {
            $this->success(__('ok'), $ret, 200);
        } else {
            $this->error(__('暂无数据'));
        }
    }

    /**
    * 获取分享链接
    */
    public function get_links() {
        $user['share_link'] = "https://pg8808.top/#/?pid=".$this->auth->id;
        $user['share_code'] = $this->auth->id;

        return $this->success("success", $user, 200);
    }


    /**
    * 三方游戏记录入库方法
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $plat_type 游戏平台编号
    */
    public function game_record() {

        $random = $this->generateRandomString(); // 生成随机字符串
        $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
        $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
        $contentType = "application/json";
        // dump("99vip_".$user->id);die;
        // 组装请求数据
        $timezoneOffset = 8; // UTC +8 for China Standard Time
        $startTime = date('Y-m-d H:i:s', strtotime("-6 hours") + ($timezoneOffset * 3600));
        $endTime = date('Y-m-d H:i:s', strtotime("now") + ($timezoneOffset * 3600));
        $data = array(
            "currency" => "CNY",
            "startTime" => $startTime,
            "endTime" => $endTime,
            "pageNo" => '1',
            "pageSize" => '500'
        );
        // 将请求数据转换为JSON字符串
        $jsonData = json_encode($data);
        // 计算签名
        $sign = md5($random . $sn . $secretKey);
        // 设置请求头
        $headers = array(
            "sign: $sign",
            "random: $random",
            "sn: $sn",
            "Content-Type: $contentType"
        );
        // 发起POST请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ap.api-bet.net/api/server/recordAll');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $responses = curl_exec($ch);
        $res = json_decode($responses, 1);
        $ret = $res['data']['list'];
        foreach ($ret as $key => $value) {
            $datas['bet_id'] = $value['gameOrderId'];
            $datas['uid'] = explode("99vip", $value['playerId'])[1];
            $datas['game_type_name'] = $value['platType'];
            $datas['bet_amount'] = floatval($value['betAmount']);
            $datas['valid_bet_amount'] = floatval($value['validAmount']);
            $datas['net_amount'] = $value['settledAmount'];
            $datas['pumping_amount'] = $value['betAmount'] - $value['validAmount'];
            // $datas['pay_amount'] = $value['validAmount'];
            $datas['currency'] = 'cny';
            $datas['create_at'] = $value['betTime'];
            $datas['net_at'] = $value['lastUpdateTime'];
            $where['bet_id'] = $value['gameOrderId'];
            $where['game_type_name'] = $value['platType'];

            if (!db::name("game_record")->where($where)->find()) {
                db::name("game_record")->insert($datas);
            }
        }
        // die;
        $this->success('成功', $ret);


    }
    public function in_game() {


        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $ispc = $this->request->post('ispc');
        $back = $this->request->post('back');
        $code = $this->request->post('gameCode');
        $gameType = $this->request->post('gameType');
        $game = $this->request->post('platType');

        // 准备请求参数
        $random = $this->generateRandomString(); // 生成随机字符串
        $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
        $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
        $contentType = "application/json";
        // dump("99vip_".$user->id);die;
        // 组装请求数据
        $data = array(
            "platType" => $game,
            "currency" => "CNY",
            "lang" => "23",
            "playerId" => "99vip".$user->id,
            "gameType" => $gameType,
            "gameCode" => $code,
            "returnUrl" => "https://pg8808.top",
            "ingress" => $ispc,
            "walletType" => "2",
            // 根据接口需要添加请求参数

        );


        // 将请求数据转换为JSON字符串
        $jsonData = json_encode($data);

        // 计算签名
        $sign = md5($random . $sn . $secretKey);

        // 设置请求头
        $headers = array(
            "sign: $sign",
            "random: $random",
            "sn: $sn",
            "Content-Type: $contentType"
        );

        // 发起POST请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ap.api-bet.net/api/server/create'); // 创建玩家
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $responses = curl_exec($ch);

        curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/gameUrl"); // 进入游戏
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);

        // 处理响应数据

        $responseData = json_decode($response, true);
        // dump($responseData);die;
        if ($responseData['code'] == 10000) {
            $url = $responseData['data']['url'];

            $this->success("list", $url, 200);
        } else {
            $this->error($responseData['msg']);
        }
    }
    /**
    * 获取免转钱包
    *
    * @ApiMethod (POST)
    */
    public function get_wallet() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }

        //   dump($user->id);die;
        // 准备请求参数
        $random = $this->generateRandomString(); // 生成随机字符串
        $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
        $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
        $contentType = "application/json";
        $data = array(
            "playerId" => "99vip".$user->id,
            "currency" => "CNY",
        );
        $jsonData = json_encode($data);
        $sign = md5($random . $sn . $secretKey);
        $headers = array(
            "sign: $sign",
            "random: $random",
            "sn: $sn",
            "Content-Type: $contentType"
        );
        // 发起POST请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/walletBalance"); // 替换为实际的接口URL
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        curl_close($ch);
        $res_code = json_decode($response, true);
        if ($res_code['code'] == 10000) {
            $money['balance'] = $res_code['data']['balance'];
            $money['money'] = $user->money;
            $this->success($res_code['msg'], $money, 200);
        } else {
            $this->error($res_code['msg'], []);
        }
    }

    /**
    * 免转钱包
    *
    * @ApiMethod (POST)
    * @param string $status.  1:转入、2:中心钱包转出、3:游戏转出
    * @param string $money 金额
    */
    public function walletTransfer() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $status = $this->request->post('status');
        $money = $this->request->post('money');
        $plat_type = $this->request->post('plat_type');
        if ($status != 3) {
            if ($money == '' || $status == '') {
                $this->error('Os parâmetros obrigatórios não podem estar vazios');
            }
        }


        // 准备请求参数
        $random = $this->generateRandomString(); // 生成随机字符串
        $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
        $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
        $contentType = "application/json";

        //   dump($user->id);die;
        if ($status == 2) {
            $data = array(
                'orderId' => date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).rand(100000, 999999),
                "playerId" => "99vip".$user->id,
                "currency" => "CNY",
                "type" => $status,
                "amount" => $money,

            );
            $jsonData = json_encode($data);
            $sign = md5($random . $sn . $secretKey);
            $headers = array(
                "sign: $sign",
                "random: $random",
                "sn: $sn",
                "Content-Type: $contentType"
            );
            // 发起POST请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/walletTransfer"); // 替换为实际的接口URL
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            curl_close($ch);

            $res_code = json_decode($response, true);
            // dump($res_code);die;
            if ($res_code['code'] == 10000) {
                \app\common\model\User::where('id', $user->id)->setInc('money', $money);
                $result = db::name('convert')->insert([
                    'user_id' => $user->id,
                    'balance' => $money,
                    'orderId' => $data['orderId'],
                    'type' => $status,
                    'currency' => "CNY",
                    'addtime' => time(),
                ]);

                $this->success($res_code['msg'], $res_code['data']['balance'], 200);
            } else {
                $this->error($res_code['msg'], []);
            }
        } elseif ($status == 3) {
            //一键转出
            $random = $this->generateRandomString(); // 生成随机字符串
            $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
            $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
            $contentType = "application/json";

            $data = array(
                "platType" => $plat_type,
                "playerId" => "99vip".$user->id,
                "currency" => "CNY",
                "type" => 2,
                "amount" => $money,

            );
            $jsonData = json_encode($data);
            $sign = md5($random . $sn . $secretKey);
            $headers = array(
                "sign: $sign",
                "random: $random",
                "sn: $sn",
                "Content-Type: $contentType"
            );
            // 发起POST请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/transferAll"); // 替换为实际的接口URL
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            curl_close($ch);

            $res_code = json_decode($response, true);
            // dump($res_code);die;
            if ($res_code['code'] == 10000) {
                \app\common\model\User::where('id', $user->id)->setInc('money', $res_code['data']['balanceAll']);
                $this->success('conversão bem-sucedida', 200, 200);
            } else {
                $this->error($res_code['msg'], []);
            }


        } else {

            //转入
            if ($user->money < $money) {
                $this->error('Saldo insuficiente');
            }
            $data = array(
                'orderId' => date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8).rand(100000, 999999),
                "playerId" => "99vip".$user->id,
                "currency" => "CNY",
                "type" => $status,
                "amount" => $money,
            );
            $jsonData = json_encode($data);
            $sign = md5($random . $sn . $secretKey);
            $headers = array(
                "sign: $sign",
                "random: $random",
                "sn: $sn",
                "Content-Type: $contentType"
            );

            // 发起POST请求
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/walletTransfer"); // 替换为实际的接口URL
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            curl_close($ch);
            $res_code = json_decode($response, true);
            // dump($res_code);die;
            if ($res_code['code'] == 10000) {
                $result = db::name('convert')->insert([
                    'user_id' => $user->id,
                    'balance' => $money,
                    'orderId' => $data['orderId'],
                    'type' => $status,
                    'currency' => "CNY",
                    'addtime' => time(),
                ]);
                \app\common\model\User::where('id', $user->id)->setDec('money', $money);
                $this->success($res_code['msg'], $res_code['data']['balance'], 200);
            } else {
                $this->error($res_code['msg'], []);
            }
        }
    }



    // 生成指定长度的随机字符串
    function generateRandomString($length = 16) {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }




    /**
    * 获取活动
    *
    * @ApiMethod (POST)

    */
    public function get_ac() {

        $data = db::name("ac")->select();

        if ($data) {
            $this->success("list", $data, 200);
        } else {
            $this->error("not data");
        }



    }

    /**
    * 获取活动详情
    *@param string $id 活动id
    * @ApiMethod (POST)

    */
    public function get_acdtai() {
        $id = $this->request->post('id');
        $data = db::name("ac")->where("id", $id)->find();

        if ($data) {
            $this->success("list", $data, 200);
        } else {
            $this->error("not data");
        }


    }


    public function game_index() {
        $status = $this->request->post('status')??1;
        // dump($status);die;
        if ($status == 1) {
            $pg_slots = db::name("gamelist")->where("is_show", 1)->where("is_show", 1)->where("platType", "pg")->order("id desc")->limit(0, 10)->select();

            $hot_jogos = db::name("gamelist")->where("is_show", 1)->where("is_hot", 1)->select();

            $slots = db::name("gamelist")->where("is_show", 1)->where("is_hot", 2)->select();

            $fishing = db::name("gamelist")->where("is_show", 1)->where("gameType", 6)->limit(0, 10)->select();

            $Chess = db::name("gamelist")->where("is_show", 1)->where("gameType", 7)->limit(0, 10)->select();
            $data['Hot jogos'] = $hot_jogos;
            $data['pg_slots'] = $pg_slots;
            $data['slots'] = $slots;
            $data['fishing'] = $fishing;
            $data['Chess'] = $Chess;
        } elseif ($status == 2) {
            $pg_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "pg")->where("gameType", 2)->limit(0, 3)->select();

            $pp_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "pp")->where("gameType", 2)->limit(0, 3)->select();

            $jdb_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "jdb")->where("gameType", 2)->limit(0, 3)->select();

            $fc_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "fc")->where("gameType", 2)->limit(0, 3)->select();

            $ag_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "ag")->where("gameType", 2)->limit(0, 3)->select();

            $cq9_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "cq9")->where("gameType", 2)->limit(0, 3)->select();



            $data['pg_slots'] = $pg_slots;
            $data['pp_slots'] = $pp_slots;
            $data['jdb_slots'] = $jdb_slots;
            $data['fc_slots'] = $fc_slots;
            $data['ag_slots'] = $ag_slots;
            $data['cq9_slots'] = $cq9_slots;
        } elseif ($status == 3) {
            $pg_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "pg")->where("gameType", 2)->limit(0, 3)->select();

            $pp_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "pp")->where("gameType", 2)->limit(0, 3)->select();

            $jdb_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "jdb")->where("gameType", 2)->limit(0, 3)->select();

            $fc_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "fc")->where("gameType", 2)->limit(0, 3)->select();

            $ag_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "ag")->where("gameType", 2)->limit(0, 3)->select();

            $cq9_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "cq9")->where("gameType", 2)->limit(0, 3)->select();



            $data['pg_slots'] = $pg_slots;
            $data['pp_slots'] = $pp_slots;
            $data['jdb_slots'] = $jdb_slots;
            $data['fc_slots'] = $fc_slots;
            $data['ag_slots'] = $ag_slots;
            $data['cq9_slots'] = $cq9_slots;
        } elseif ($status == 4) {

            $jdb_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "jdb")->where("gameType", 7)->limit(0, 3)->select();

            $fc_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "fc")->where("gameType", 6)->limit(0, 3)->select();

            $ag_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "ag")->where("gameType", 6)->limit(0, 3)->select();

            $cq9_slots = db::name("gamelist")->where("is_show", 1)->where("platType", "cq9")->where("gameType", 6)->limit(0, 3)->select();


            $data['jdb fishing'] = $jdb_slots;
            $data['fc fishing'] = $fc_slots;
            $data['ag fishing'] = $ag_slots;
            $data['cq9 fishing'] = $cq9_slots;
        } elseif ($status == 5) {
            $datas = db::name("gamelist")->where("is_show", 1)->where("gameType", 1)->limit(0, 4)->select();
            $data['Live'] = $datas;
        }
        // dump($data);die;
        if ($data) {
            $this->success("list", $data, 200);
        } else {
            $this->error("not data");
        }



    }

    public function distribution() {

        if (isset($users['pid'])) {
            $this->Tertiary_distribution($users['pid'], $truesv['truesv'], $total_money, $v['id']);
        }

    }


    //三级分销收益分发
    public function Scheduled_Tasks() {
        $result = false;
        Db::startTrans();
        try {
            Db::name('game_record')->where('is_fl',
                '=',
                0)->chunk(100,
                function($users) {
                    // $dayrate = Db::name('config')->where('name', 'dayrate')->value("value");
                    foreach ($users as $k => $v) {
                        $pid = Db::name('user')->where('id', $v['uid'])->value("pid");
                        // dump($v);
                        if (isset($pid)) {
                            $this->Tertiary_distribution($pid, $v['valid_bet_amount'], $v['id']);
                        }
                    }
                });
            Db::commit();
        } catch (ValidateException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }


    }


    public function Tertiary_distribution($pid,
        $money,
        $order_id) {
        $user = db::name("user")->where("id",
            $pid)->field('id,pid')->find();

        if (!isset($user)) {
            return;
        }

        $one = db::name("config")->where("name", "one")->value("value")/100;
        $two = db::name("config")->where("name", "two")->value("value")/100;
        $three = db::name("config")->where("name", "three")->value("value")/100;
        
        $rebateRates = [0,
            $one,
            $two,
            $three]; // 返利比例，按照[0级, 1级, 2级, 3级]顺序

        $this->processRebate($money, $user['pid'], $rebateRates, $order_id, 1); // 从1级开始返利
    }

    private function processRebate($money, $userId, $rebateRates, $orderId, $startLevel) {
        if (!$userId) {
            return; // 没有上级，停止返利处理
        }

        $user = db::name("user")->where("id", $userId)->field('id,pid')->find();

        foreach ($rebateRates as $level => $rate) {
            if ($level >= $startLevel) {
                if ($user['pid']) {
                    $rebateAmount = $money * $rate;
                    \app\common\model\User::money($rebateAmount, $user['pid'], "{$level}desconto de nível", 1);

                    $user = db::name("user")->where("id", $user['pid'])->field('id,pid')->find();
                } else {
                    break; // 如果没有更高级别的上级，停止返利处理
                }
            }
        }

        db::name("game_record")->where("id", $orderId)->update(["is_fl" => 1]);
    }


    /**
    * 模糊查询游戏
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $keyword
    * @param string $page 从1开始
    * @param string $list 分页长度,默认10
    */
    public function game_like() {

        $keyword = $this->request->post('keyword');
        $page = $this->request->post('page') ?? 1;
        $list = $this->request->post('list') ?? 10;
        $list = [];
        if ($keyword == '') {
            $this->error('Os parâmetros obrigatórios não podem estar vazios');
        }

        $list = db::name("gamelist")->where("is_show", 1)->where('gameName', 'like', '%' . $keyword . '%')->page($page, $list)->select();
        $listcont = db::name("gamelist")->where('gameName', 'like', '%' . $keyword . '%')->count();
        $data['data'] = $list;
        $data['tal'] = $listcont;
        $this->success('list', $data, 200);


    }

    /**
    * 游戏详情
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)
    * @param string $gameType
    * @param string $code
    * @param string $page 从1开始
    * @param string $list 分页长度,默认10
    */
    public function game_detai() {

        $gameCode = $this->request->post('platType');
        $code = $this->request->post('gameType');
        $list = [];
        $where = [];
        if ($code) {
            $where['gameType'] = $code;
        }
        if ($gameCode) {
            $where['platType'] = $gameCode;
        }
        $list = db::name("gamelist")->where("is_show", 1)->where($where)->select();
        $listcont = db::name("gamelist")->where($where)->count();
        $data['data'] = $list;
        $data['tal'] = $listcont;
        $this->success('list', $data, 200);


    }



}
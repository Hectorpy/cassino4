<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    public function rech() {
        
        return $this->view->fetch();
    }
        public function rechs() {
        
        return $this->view->fetch();
    }
       public function contact() {
           
           
        $chat = db::name("config")->where("name", "chat")->value("value");
        $chats = db::name("config")->where("name", "chats")->value("value");

        $this->assign('chat', $chat);
        $this->assign('chats', $chats);
        
        return $this->view->fetch();
    }
    
    
    
    
    
    public function index() {
        // die;
        // //         // 准备请求参数
        //         $random = $this->generateRandomString(); // 生成随机字符串
        //         $sn = "o62"; // 商户前缀，请替换为实际的商户前缀
        //         $secretKey = "7KmoPK477z4pUXa0ioBnD2436C0J92t4"; // 请替换为实际的密钥
        //         $contentType = "application/json";

        //         // 组装请求数据
        //         $data = array(
        //             "platType" => "cq9",
        //             // "currency" => "CNY",
        //             // // 	"playerId"=>"test001",

        //             // "gameType" => "1",
        //             // "ingress" => "device1"
        //             // 根据接口需要添加请求参数
        //             // ...
        //         );


        //         // 将请求数据转换为JSON字符串
        //         $jsonData = json_encode($data);

        //         // 计算签名
        //         $sign = md5($random . $sn . $secretKey);

        //         // 设置请求头
        //         $headers = array(
        //             "sign: $sign",
        //             "random: $random",
        //             "sn: $sn",
        //             "Content-Type: $contentType"
        //         );

        //         // 发起POST请求
        //         $ch = curl_init();
        //         curl_setopt($ch, CURLOPT_URL, "https://ap.api-bet.net/api/server/gameCode"); // 替换为实际的接口URL
        //         curl_setopt($ch, CURLOPT_POST, 1);
        //         curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //         curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        //         $response = curl_exec($ch);
        //         curl_close($ch);

        //         $responseData = json_decode($response, true);
        //         //  dump($responseData); die;
        //         foreach ($responseData['data'] as $value) {

        //             $datas['platType']=$value['platType'];
        //             $datas['gameType']=$value['gameType'];
        //             $datas['gameCode']=$value['gameCode'];
        //             $datas['gameName']=$value['gameName']['en'];

        //             if(!isset($value['imageUrl']['en']['square'])){
        //              continue;
        //             }else{
        //               $datas['imageUrl']=$value['imageUrl']['en']['square'];
        //             }
        //             dump($datas);
        //           db::name("gamelist")->insert($datas);
        //         }

        //         ;die;
        // dump($response); die;
        // 处理响应数据

        //  dump($responseData['data']);die;
        // $ress=array_slice($responseData['data'],0,15);

        $ress = db::name("gamelist")->where("platType", "pg")->order("id desc")->limit(0, 9)->select();
        $hot = db::name("gamelist")->where("is_hot", 1)->select();
        $slo = db::name("gamelist")->where("is_hot", 2)->select();
        $fishing = db::name("gamelist")->where("gameType", 6)->limit(0, 10)->select();
        $Chess = db::name("gamelist")->where("gameType", 7)->limit(0, 10)->select();



 $conts = db::name("conts")->where("id",1)->find();
   $this->assign('conts', $conts);
        $lb = db::name("lb")->select();
        $chat = db::name("config")->where("name", "chat")->value("value");

        $this->assign('chat', $chat);
        $this->assign('lb', $lb);
        $this->assign('Chess', $Chess);
        $this->assign('fishing', $fishing);
        $this->assign('slo', $slo);
        $this->assign('hot', $hot);
        $this->assign('pglist', $ress);

        return $this->view->fetch();
    }



    public function in_game() {


        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        $is_pc = $_POST['ispc'];
        $back = $_POST['back'];
        
        $code = $_POST['gameCode'];
        $gameType = $_POST['gameType'];
        $game = $_POST['platType'];
        // 准备请求参数
        $random = $this->generateRandomString(); // 生成随机字符串
        $sn = "o62"; // 商户前缀，请替换为实际的商户前缀
        $secretKey = "7KmoPK477z4pUXa0ioBnD2436C0J92t4"; // 请替换为实际的密钥
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
            "returnUrl" => "https://77vipgame.com/index/index/".$back,
            "ingress" => $is_pc,
            "walletType" => "1",
            // 根据接口需要添加请求参数
            // ...
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

            $this->success("list", $url);
        } else {
            $this->error($responseData['msg']);
        }
    }


    public function pc() {
        
 $conts = db::name("conts")->where("id",1)->find();
   $this->assign('conts', $conts);
  $lb = db::name("lb")->select();
        $chat = db::name("config")->where("name", "chat")->value("value");





        $ret = db::name("msg")->select();
        $this->assign('msg', $ret);
        
        
        
        $this->assign('chat', $chat);
        $this->assign('lb', $lb);
        return $this->view->fetch();
    }

    public function my() {
        $chat = db::name("config")->where("name", "chat")->value("value");

        $this->assign('chat', $chat);
        return $this->view->fetch();
    }

    public function message() {
        $chat = db::name("config")->where("name", "chat")->value("value");

        $this->assign('chat', $chat);

        $ret = db::name("msg")->select();
        $this->assign('msg', $ret);
        return $this->view->fetch();
    }

    public function invitereward() {
        $chat = db::name("config")->where("name", "chat")->value("value");

        $this->assign('chat', $chat);
        return $this->view->fetch();
    }

    public function setting() {
        return $this->view->fetch();
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








}
<?php

namespace app\api\controller;
use think\Db;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;
use app\common\library\Sms as Smslib;
use fast\Random;
use think\Config;
use think\Validate;

/**
* 会员接口
*/
class User extends Api
{
    protected $noNeedLogin = ['login',
        'mobilelogin',
        'register',
        'resetpwd',
        'changeemail',
        'changemobile',
        'third','change_pwd'];
    protected $noNeedRight = '*';

    public function _initialize() {
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

    }

    /**
    * 会员中心
    */
    public function index() {
        $this->success('', ['welcome' => $this->auth->nickname]);
    }
    /**
    * 获取个人信息
    *
    * @ApiHeaders (name=token, type=string, required=true, description="请求的Token")
    * @ApiMethod (POST)

    */
    public function getUserinfo() {
        $user = $this->auth->getUser();
        if (! $user) {
            $this->error('token 失效', '', 422);
        }
        if ($this->auth->id) {
            $this->success("获取成功", $this->auth->getUserinfo(),200);
        } else {
            $this->error("提交失败");
        }
    }

    /**
    * 会员登录
    *
    * @ApiMethod (POST)
    * @param string $account  账号
    * @param string $password 密码
    */
    public function login() {
        $account = $this->request->post('username');
        $password = $this->request->post('password');
        // $code = $this->request->post('code');
        if (!$account || !$password) {
            $this->error(__('parâmetro não está vazio'));
        }

        // if (filter_var($account, FILTER_VALIDATE_EMAIL)) {

        //     $retsm = db::name("ems")->where('email', $account)->order("id desc")->value("code");
        //     if ($retsm !== $code) {
        //         $this->error('邮箱验证码错误');
        //     }
        // } else {
        //     $retsms = db::name("sms")->where('mobile', $account)->order("id desc")->value("code");
        //     if ($retsms !== $code) {
        //         $this->error(__('Captcha is incorrect'));
        //     }
        // }

        $ret = $this->auth->login($account, $password);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data,200);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
    * 手机验证码登录
    *
    * @ApiMethod (POST)
    * @param string $mobile  手机号
    * @param string $captcha 验证码
    */
    public function mobilelogin() {
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::getByMobile($mobile);
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        } else {
            $ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }
    /**
    * 注册会员
    *
    * @ApiMethod (POST)
    * @param string $username 邮箱&手机号
    * @param string $password 密码
    * @param string $codes    验证码
    * @param string $invitation_code   邀请码
    */
    public function register() {
        $username = $this->request->post('username');
        $password = $this->request->post('password');
        $invitation_code = $this->request->post('invitation_code');
        $code = $this->request->post('codes');
        if (!$username || !$password) {
            $this->error(__('parâmetro não está vazio'));
        }
        if (!$invitation_code) {
            $this->error("O código de convite não está vazio");
        }

        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            if ($username && !Validate::is($username, "email")) {
                $this->error(__('erro de e-mail'));
            }
            $extend['email'] = $username;
            $retsm = db::name("ems")->where('email', $username)->order("id desc")->value("code");
            if ($retsm !== $code) {
                $this->error('邮箱验证码错误');
            }
        } else {
            if ($username && !Validate::regex($username, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            
            $extend['mobile'] = $username;

            $retsms = db::name("sms")->where('mobile', $username)->order("id desc")->value("code");
            if ($retsms !== $code) {
                $this->error(__('Captcha is incorrect'));
            }

        }
        
        
        $inv = db::name("user")->where("id", $invitation_code)->find();
        if (!$inv) {
            $this->error("O código do convite está errado ou não existe");
        }
        
        
        $extend['pid']=$invitation_code;

        $ret = $this->auth->register($username, $password,'','', $extend);
        if ($ret) {
            $data = ['userinfo' => $this->auth->getUserinfo()];
            $this->success(__('Registro bem sucedido'), $data,200);
        } else {
            $this->error($this->auth->getError());
        }
    }
    
     /**
    * 忘记密码-找回
    *
    * @ApiMethod (POST)
    * @param string $username  邮箱&手机号
    * @param string $password 密码
    * @param string $code 验证码
    */
    public function change_pwd() {
        $mobile= $this->request->request('username');
         $code = $this->request->request('code');
     
         $ret = Sms::check($mobile, $code, 'register');
        // if (!$ret) {
        //     $this->error(__('Captcha is incorrect'));
        //   }
        $user = db::name("user")->where("username", $mobile)->find();
          if (!$user) {
            $this->error(__('手机号不存在'));
          }
        // dump($user);die;
        $password = $this->request->post('password');
        $Random = Random::alnum();
        $updata['password'] = $this->getEncryptPassword($password, $Random);
        $updata['salt'] = $Random;
        $updata['updatetime'] = time();
        $result = db::name("user")->where("id", $user['id'])->update($updata);
        if ($result !== false) {
            $this->success(['successfully modified']);
        } else {
            $this->error(['fail to edit']);
        }
    }

    public function getEncryptPassword($password, $salt = '') {
        return md5(md5($password) . $salt);
    }


    // /**
    //  * 注册会员
    //  *
    //  * @ApiMethod (POST)
    //  * @param string $username 用户名
    //  * @param string $password 密码
    //  * @param string $email    邮箱
    //  * @param string $mobile   手机号
    //  * @param string $code     验证码
    //  */
    // public function register()
    // {
    //     $username = $this->request->post('username');
    //     $password = $this->request->post('password');
    //     $email = $this->request->post('email');
    //     $mobile = $this->request->post('mobile');
    //     $code = $this->request->post('code');
    //     if (!$username || !$password) {
    //         $this->error(__('Invalid parameters'));
    //     }
    //     if ($email && !Validate::is($email, "email")) {
    //         $this->error(__('Email is incorrect'));
    //     }
    //     if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
    //         $this->error(__('Mobile is incorrect'));
    //     }
    //     $ret = Sms::check($mobile, $code, 'register');
    //     if (!$ret) {
    //         $this->error(__('Captcha is incorrect'));
    //     }
    //     $ret = $this->auth->register($username, $password, $email, $mobile, []);
    //     if ($ret) {
    //         $data = ['userinfo' => $this->auth->getUserinfo()];
    //         $this->success(__('Sign up successful'), $data);
    //     } else {
    //         $this->error($this->auth->getError());
    //     }
    // }

    /**
    * 退出登录
    * @ApiMethod (POST)
    */
    public function logout() {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
    * 修改会员个人信息
    *
    * @ApiMethod (POST)
    * @param string $avatar   头像地址
    * @param string $username 用户名
    * @param string $nickname 昵称
    * @param string $bio      个人简介
    */
    public function profile() {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if ($nickname) {
            $exists = \app\common\model\User::where('nickname', $nickname)->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Nickname already exists'));
            }
            $user->nickname = $nickname;
        }
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success();
    }

    /**
    * 修改邮箱
    *
    * @ApiMethod (POST)
    * @param string $email   邮箱
    * @param string $captcha 验证码
    */
    public function changeemail() {
        $user = $this->auth->getUser();
        $email = $this->request->post('email');
        $captcha = $this->request->post('captcha');
        if (!$email || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::is($email, "email")) {
            $this->error(__('Email is incorrect'));
        }
        if (\app\common\model\User::where('email', $email)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Email already exists'));
        }
        $result = Ems::check($email, $captcha, 'changeemail');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->email = 1;
        $user->verification = $verification;
        $user->email = $email;
        $user->save();

        Ems::flush($email, 'changeemail');
        $this->success();
    }

    /**
    * 修改手机号
    *
    * @ApiMethod (POST)
    * @param string $mobile  手机号
    * @param string $captcha 验证码
    */
    public function changemobile() {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('captcha');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exists'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success();
    }

    /**
    * 第三方登录
    *
    * @ApiMethod (POST)
    * @param string $platform 平台名称
    * @param string $code     Code码
    */
    public function third() {
        $url = url('user/index');
        $platform = $this->request->post("platform");
        $code = $this->request->post("code");
        $config = get_addon_config('third');
        if (!$config || !isset($config[$platform])) {
            $this->error(__('Invalid parameters'));
        }
        $app = new \addons\third\library\Application($config);
        //通过code换access_token和绑定会员
        $result = $app-> {
            $platform
        }->getUserInfo(['code' => $code]);
        if ($result) {
            $loginret = \addons\third\library\Service::connect($platform, $result);
            if ($loginret) {
                $data = [
                    'userinfo' => $this->auth->getUserinfo(),
                    'thirdinfo' => $result
                ];
                $this->success(__('Logged in successful'), $data);
            }
        }
        $this->error(__('Operation failed'), $url);
    }

    /**
    * 重置密码
    *
    * @ApiMethod (POST)
    * @param string $mobile      手机号
    * @param string $newpassword 新密码
    * @param string $captcha     验证码
    */
    public function resetpwd() {
        $type = $this->request->post("type");
        $mobile = $this->request->post("mobile");
        $email = $this->request->post("email");
        $newpassword = $this->request->post("newpassword");
        $captcha = $this->request->post("captcha");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        //验证Token
        if (!Validate::make()->check(['newpassword' => $newpassword], ['newpassword' => 'require|regex:\S{6,30}'])) {
            $this->error(__('Password must be 6 to 30 characters'));
        }
        if ($type == 'mobile') {
            if (!Validate::regex($mobile, "^1\d{10}$")) {
                $this->error(__('Mobile is incorrect'));
            }
            $user = \app\common\model\User::getByMobile($mobile);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Sms::check($mobile, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Sms::flush($mobile, 'resetpwd');
        } else {
            if (!Validate::is($email, "email")) {
                $this->error(__('Email is incorrect'));
            }
            $user = \app\common\model\User::getByEmail($email);
            if (!$user) {
                $this->error(__('User not found'));
            }
            $ret = Ems::check($email, $captcha, 'resetpwd');
            if (!$ret) {
                $this->error(__('Captcha is incorrect'));
            }
            Ems::flush($email, 'resetpwd');
        }
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }
}
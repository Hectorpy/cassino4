<?php

namespace app\admin\controller;
use think\db;
use app\common\controller\Backend;
use fast\Random;
/**
* 会员管理
*
* @icon fa fa-users
*/
class Users extends Backend
{

    /**
    * Users模型对象
    * @var \app\admin\model\Users
    */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Users;
        $this->view->assign("statusList", $this->model->getStatusList());
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


    //一键转出
    public function Transfer() {
        if ($this->request->isPost()) {


            $params = $this->request->get();
           
            
            //一键转出
            $random = $this->generateRandomString(); // 生成随机字符串
            $sn = "zg0"; // 商户前缀，请替换为实际的商户前缀
            $secretKey = "16C52JusD6FC997r628Ga6y0PQVtz24F"; // 请替换为实际的密钥
            $contentType = "application/json";

            $data = array(
                "playerId" => "jack123",//"99vip".$params['id'],
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
                \app\common\model\User::where('id', $params['id'])->setInc('money', $res_code['data']['balanceAll']);
                $this->success('共回收'.$res_code['data']['balanceAll']."分");
            } else {
                $this->error($res_code['msg']."游戏未创建");
            }

        }

    }




    /**
    * 批量操作方法
    */
    public function multipop($ids = "") {
        if ($this->request->isPost()) {

            $this->token();
            $params = $this->request->param();

            //   dump($params);die;
            $userIds = $params['ids'];
            $money = $params['row']['money'];
            $bz = $params['row']['bz'];
            // $money = str_replace(',', '', $money);

            $userIds = explode(',', $userIds);
            $nowTime = time();
            Db::startTrans();
            try {

                foreach ($userIds as $uId) {
                    db::table('fa_user')->where('id', $uId)->setinc("money", $money);
                    $result = db::name('mx')->insert([
                        'uid' => $uId,
                        'info' => $bz, //"批量上分成功",
                        'type' => 0,
                        'money' => $money,
                        'addtime' => time(),
                    ]);
                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success("批量上分成功");
        }
        return $this->view->fetch();
    }
    /**
    * 查看
    */
    public function index() {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $uid = input('uid/d');
            //   dump($_SESSION['think']['admin']);die;
            if ($_SESSION['think']['admin']['id'] == 1) {


                // dump($_SESSION['think']['admin']['user_id']);die;
                if ($uid) {
                    $list = $this->model
                    ->where($where)
                    ->where("pid", $uid)
                    ->order($sort, $order)
                    ->paginate($limit);

                } else {
                    $list = $this->model
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

                }
            } else {

                if ($uid) {
                    $list = $this->model
                    ->where($where)
                    ->where("pid", $uid)
                    ->order($sort, $order)
                    ->paginate($limit);

                } else {
                    $list = $this->model
                    ->where($where)
                    ->where("pid", $_SESSION['think']['admin']['user_id'])

                    ->order($sort, $order)
                    ->paginate($limit);

                }


            }
            $result = array("total" => $list->total(), "rows" => $list->items(), "info" => $_SESSION['think']['admin']['id']);
            return json($result);
        }
        return $this->view->fetch();
    }


    //充值操作
    public function accesss() {
        $params = $this->request->post();

        $member = db::table('fa_user')->where('id', $params['uid'])->find();

        if ($params['range'] == 0) {
            $wheres = "setinc";
            $info = "pontuado com sucesso".$params['money'];
        } else {
            if ($member['money'] < $params['money']) {
                $this->error('余额不足，不能扣取');
            }
            $info = "Sucesso".$params['money'];
            $wheres = "setdec";
        }
        $result = false;
        // dump($member['gec']);die;
        Db::startTrans();
        try {
            db::table('fa_user')->where('id', $params['uid'])->$wheres("money", $params['money']);
            //是否采用模型验证
            $result = db::name('mx')->insert([
                'uid' => $params['uid'],
                'info' => $info,
                'type' => $params['range'],
                'money' => $params['money'],
                'addtime' => time(),
            ]);
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
        if ($result !== false) {
            $this->success("操作成功");
        } else {
            $this->error(__('操作失败'));
        }







    }
    
    
    
     /**
    * 查看
    */
    public function detail($ids = null) {
       
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        
     
        
        $row->pushnum=db::name("user")->where("pid",$ids)->count();
        
        $row->cz= db::name("dotop")->whereIn("user_id", $ids)->where(['status' => 1, 'type' => 0])->sum("money");
        $row->tx=db::name("dotop")->whereIn("user_id", $ids)->where(['status' => 1, 'type' => 1])->sum("money");
        $row->xz=db::name("game_record")->whereIn("uid", $ids)->sum("bet_amount");
        
        
        
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
    }

    

    //充值操作
    public function access() {
        $params = $this->request->post();


        $member = db::table('fa_user')->where('id', $params['uid'])->find();
        $members = db::table('fa_user')->where('id', $_SESSION['think']['admin']['user_id'])->find();

        if ($params['range'] == 0) {
            $wheres = "setinc";
            $info = "Enviar".$params['money'];
        } else {
            if ($member['money'] < $params['money']) {
                $this->error('余额不足，不能扣取');
            }
            $info = "Sucesso".$params['money'];
            $wheres = "setdec";
        }
        $result = false;
        // dump($params);die;
        Db::startTrans();
        try {
            //是否采用模型验证
            $result = db::name('dotop')->insert([
                'user_id' => $params['uid'],
                'info' => $params['coin'],
                'username' => $member['username'],
                'me_user' => $members['username'],
                'dai_id' => $_SESSION['think']['admin']['user_id'],
                'info' => $info,
                'status' => 0,
                'type' => $params['range'],
                'money' => $params['money'],
                'addtime' => time(),
            ]);
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
        if ($result !== false) {
            $this->success("上分申请提交成功");
        } else {
            $this->error(__('操作失败'));
        }

    }
    
    
      /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
          
                if(isset($params['password'])&& !empty($params['password'])){
                   $salt=Random::alnum();
                   $params['salt']=$salt;
                   $params['password']=md5(md5($params['password']) . $salt);  
                }else{
                    if(isset($params['password'])){
                      unset($params['password']);  
                    }
                    
                   
                }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    } 



    /**
    * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
    * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
    * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
    */


}
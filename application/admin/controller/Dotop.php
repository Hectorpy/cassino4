<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\db;
/**
 * 上分审核管理
 *
 * @icon fa fa-circle-o
 */
class Dotop extends Backend
{

    /**
     * Dotop模型对象
     * @var \app\admin\model\Dotop
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Dotop;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("statusLists", $this->model->getStatusLists());
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
//   dump($_SESSION['think']['admin']);die;
            if ($_SESSION['think']['admin']['id'] == 1) {


                    $list = $this->model
                    ->where($where)
                    ->where("type",0)
                    ->order($sort, $order)
                    ->paginate($limit);
                    

            } else {

                    $list = $this->model
                    ->where($where)
                    ->where("type",0)
                    ->where("dai_id", $_SESSION['think']['admin']['user_id'])

                    ->order($sort, $order)
                    ->paginate($limit);



            }
            
            foreach ($list as $k => $row) {
                
                if($row->type==0){
                  $row->moneys= ($row->money*5);  
                }else{
                  $row->moneys= ($row->money/5);    
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
                    'moneys' => $params['money'],
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


    public function adopt() {
        $post = $this->request->request();
        if (!$post['id']) {
            $this->error('参数错误');
        }
        $cashrecord = db::name("dotop")->where("id", $post['id'])->find();

        // dump($post);die;
        if ($cashrecord['status'] == 1) {
            $this->error("已上分");
        }
        if ($cashrecord['status'] == 2) {
            $this->error("已拒绝上分");
        }
        
        if($cashrecord['type']==0){
            
               \app\common\model\User::money($cashrecord['money'], $cashrecord['user_id'], "pontuado com sucesso".$cashrecord['money']);
        }
     
        $result = db::name("dotop")->
                where("id", $post['id'])->update(['status' => 1, 'info' => "pontuado com sucesso", "uptime" => time()]);
        
           db::name('mx')->insert([
                    'uid' => $cashrecord['user_id'],
                    'info' =>"pontuado com sucesso",
                    'type' => 1,
                    'money' => $cashrecord['money'],
                    'addtime' => time(),
                ]);
                
                
                
        if ($result) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }

    public function cancel() {
        $post = $this->request->request();
        if (!$post['uid']) {
            $this->error('参数错误');
        }
        $cashrecord = db::name("dotop")->where("id", $post['uid'])->find();
   
        Db::startTrans();
        try {
            $result = db::name("dotop")->where("id", $post['uid'])->update(['status' => 2, 'info' => $post['remark'], "uptime" => time()]);
            // 提交事务
            
            
              if($cashrecord['type']==1){
            
               \app\common\model\User::money($cashrecord['money'], $cashrecord['user_id'], $post['remark'].$cashrecord['money']);
                }
             
            
           db::name('mx')->insert([
                    'uid' => $cashrecord['user_id'],
                    'info' =>$post['remark'],
                    'type' => 0,
                    'money' => $cashrecord['money'],
                    'addtime' => time(),
                ]);
            Db::commit();
        } catch (\Exception $e) {
            // dump($e->getMessage());die;
            $this->error('数据错误' & $e->getMessage());
            // 回滚事务
            Db::rollback();
        }
        $this->success("拒绝成功");
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


}

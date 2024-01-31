<?php

namespace app\admin\controller;
use think\db;
use app\common\controller\Backend;

/**
* 上分审核管理
*
* @icon fa fa-circle-o
*/
class Dotops extends Backend
{

    public function _initialize() {
        parent::_initialize();
        $this->model = new \app\admin\model\Dotops;
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
                ->where("type", 1)
                ->order($sort, $order)
                ->paginate($limit);


            } else {

                $list = $this->model
                ->where($where)
                ->where("type", 1)
                ->where("dai_id", $_SESSION['think']['admin']['user_id'])

                ->order($sort, $order)
                ->paginate($limit);



            }

            foreach ($list as $k => $row) {

                if ($row->type == 0) {
                    $row->moneys = ($row->money*5);
                } else {
                    $row->moneys = ($row->money/5);
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







    /**
    * 批量操作方法
    */
    public function multipop($ids = "") {
        if ($this->request->isPost()) {

            $this->token();
            $params = $this->request->param();

            //   dump($params);die;
            $userIds = $params['ids'];
            $bz = $params['row']['bz'];
            // $money = str_replace(',', '', $money);

            $userIds = explode(',', $userIds);

            $nowTime = time();
            Db::startTrans();
            try {

                foreach ($userIds as $uId) {


                    $cashrecord = db::name("dotop")->where("id", $uId)->find();

                    if ($cashrecord['status'] == 0) {
                        
                        

                                $merchantNo = "3018230613001";
                                $merchantOrderNo = 'Bpay'.date("YmdHis").rand(100000, 999999);
                                $countryCode = "BRA";
                                $currencyCode = "BRL";
                                $transferType = "900410285001";
                                $transferAmount = $cashrecord['money'];
                                $feeDeduction = "1";
                                $remark = "代付-";
                                $notifyUrl = "https://server.pg8808.top/api/index/BetcatPay_notify";

                                //  $extendedParams = 'payerFirstName^'.$payerFirstName.'|payerLastName^'.$payerLastName.'|payerEmail^'.$payerEmail.'|payerPhone^'.$payerPhone.'|payerCPF^'.$payerCPF;
                                $extendedParams = 'payeeName^xiaom|PIX^123|pixType^CPF|payeePhone^5549292|payeeEmail^123@qq。com|payeeCPF^8884399';

                                //组装好签名数据
                                $json_data = array(
                                    'merchantNo' => $merchantNo,
                                    'merchantOrderNo' => $merchantOrderNo,
                                    'countryCode' => $countryCode,
                                    'currencyCode' => $currencyCode,
                                    'transferType' => $transferType,
                                    'transferAmount' => $transferAmount,
                                    'feeDeduction' => $feeDeduction,
                                    'remark' => $remark,
                                    'notifyUrl' => $notifyUrl,
                                    'extendedParams' => $extendedParams
                                );
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
                                $sign = "";
                                $signStr = asc_sort($json_data); //排序
                                //组装好签名数据


                                //商户私钥
                                    $merchant_private_key = '-----BEGIN PRIVATE KEY-----
                    MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALMNUSkKzZraPQTUEQIFOSCiaOGL234RzRbmJ4xDzBHy2RAyNdaapM+l3X6P9rmxEZ3Rj+0X/ljqYIkGUeBFagAmitza0Eb4n7Xnfur9T8U3+NkTS9Ed9Z34nlBzC92/Z5GMG2pp6mTf70XC8bFdqlBsW8nPVQsw9jBc4SRCZk1HAgMBAAECgYBCP1MgFFcuTED3YF9KmBQi9vRHPy/e3Uc8ibtoMk129ptJWsqAtIb2LTBee3WWDuWttrPBzXbV/yHokOYKTKSC+fMznFBf2Ulke4Swvd/GlVDdOeMgSoZfFBfZrVX4zSTwallmYsr0w5WqM4D3jDlOZLkANQUkkpgHDbXZrSu1wQJBAPQov+2qgVcFXY6nwQ5To5QQYhnXIkNmwbBHMQ3BljLfbCrohrno+cI3AhnHtxzIz0yfHoYDk9feXPiPWKoswgsCQQC7vEIYGI1c7GE/fWv0Yoj3XGb7eqgfJ3a8Jab8DlUHnV8S4nmlLi/stP5kCrHmf8GA9R8ErgSnQUdLsJEIFAM1AkEAuN0lvLiNp6rLVJjVhphzUUc6T+Bg8/GYk3TDwmuh4rDhwHdAkwDAInnt4EEj9upgct5DiSqqRRb7A8PdWTP8UwJAamYU84EexTZ3GzujLnuV8tOczhRDKnz8Tz/rttkMmec4FgTjOpnFsZsWvm5NSzzG16aU8NsLahuWI7CrUe+9rQJAAWRb0AOajY3Yy+GocB6e3Z/VmIjwx2I+S1rCiFSy6S1XeBW2HviDiiyP9caEypn1Fqg+/Mm98y1rAPnjWde5NQ==
                    -----END PRIVATE KEY-----';
                                $merchant_private_key = openssl_get_privatekey($merchant_private_key);
                                openssl_sign($signStr, $sign_info, $merchant_private_key, OPENSSL_ALGO_MD5);
                                $sign = base64_encode($sign_info);


                                //提交到支付网关
                                $postdata = array(
                                    'merchantNo' => $merchantNo,
                                    'merchantOrderNo' => $merchantOrderNo,
                                    'countryCode' => $countryCode,
                                    'currencyCode' => $currencyCode,
                                    'transferType' => $transferType,
                                    'transferAmount' => $transferAmount,
                                    'feeDeduction' => $feeDeduction,
                                    'remark' => $remark,
                                    'notifyUrl' => $notifyUrl,
                                    'extendedParams' => $extendedParams,
                                    'sign' => $sign
                                );
                                $curl = curl_init();
                                curl_setopt($curl, CURLOPT_URL, "https://api.bpay.tv/api/v2/transfer/order/create");
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
                                
                                $result = db::name("dotop")->where("id",$uId)->update(['status' => 3, 'orderNo' => $merchantOrderNo, 'info' => "pontuado com sucesso", "uptime" => time()]);

                    }

                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            $this->success("批量操作成功");
        }
        return $this->view->fetch();
    }


    // /**
    // * 批量操作方法
    // */
    // public function multipop($ids = "") {
    //     if ($this->request->isPost()) {

    //         $this->token();
    //         $params = $this->request->param();

    //           dump($params);die;
    //         $userIds = $params['ids'];
    //         $money = $params['row']['money'];
    //         $bz = $params['row']['bz'];
    //         // $money = str_replace(',', '', $money);

    //         $userIds = explode(',', $userIds);
    //         $nowTime = time();
    //         Db::startTrans();
    //         try {

    //             foreach ($userIds as $uId) {
    //                 db::table('fa_user')->where('id', $uId)->setinc("money", $money);
    //                 $result = db::name('mx')->insert([
    //                     'uid' => $uId,
    //                     'info' => $bz, //"批量上分成功",
    //                     'type' => 0,
    //                     'money' => $money,
    //                     'addtime' => time(),
    //                 ]);
    //             }
    //             Db::commit();
    //         } catch (Exception $e) {
    //             Db::rollback();
    //             $this->error($e->getMessage());
    //         }
    //         $this->success("批量上分成功");
    //     }
    //     return $this->view->fetch();
    // }





    public function adopt_tx() {
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
        if ($cashrecord['status'] == 3) {
            $this->error("等待回调通知");
        }


        if ($cashrecord['type'] == 1) {



            $merchantNo = "3018230613001";
            $merchantOrderNo = 'Bpay'.date("YmdHis").rand(100000, 999999);
            $countryCode = "BRA";
            $currencyCode = "BRL";
            $transferType = "900410285001";
            $transferAmount = $cashrecord['money'];
            $feeDeduction = "1";
            $remark = "代付-";
            $notifyUrl = "https://server.pg8808.top/api/index/BetcatPay_notify";

            //  $extendedParams = 'payerFirstName^'.$payerFirstName.'|payerLastName^'.$payerLastName.'|payerEmail^'.$payerEmail.'|payerPhone^'.$payerPhone.'|payerCPF^'.$payerCPF;
            $extendedParams = 'payeeName^xiaom|PIX^123|pixType^CPF|payeePhone^5549292|payeeEmail^123@qq。com|payeeCPF^8884399';

            //组装好签名数据
            $json_data = array(
                'merchantNo' => $merchantNo,
                'merchantOrderNo' => $merchantOrderNo,
                'countryCode' => $countryCode,
                'currencyCode' => $currencyCode,
                'transferType' => $transferType,
                'transferAmount' => $transferAmount,
                'feeDeduction' => $feeDeduction,
                'remark' => $remark,
                'notifyUrl' => $notifyUrl,
                'extendedParams' => $extendedParams
            );
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
            $sign = "";
            $signStr = asc_sort($json_data); //排序
            //组装好签名数据


            //商户私钥
            $merchant_private_key = '-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBALMNUSkKzZraPQTUEQIFOSCiaOGL234RzRbmJ4xDzBHy2RAyNdaapM+l3X6P9rmxEZ3Rj+0X/ljqYIkGUeBFagAmitza0Eb4n7Xnfur9T8U3+NkTS9Ed9Z34nlBzC92/Z5GMG2pp6mTf70XC8bFdqlBsW8nPVQsw9jBc4SRCZk1HAgMBAAECgYBCP1MgFFcuTED3YF9KmBQi9vRHPy/e3Uc8ibtoMk129ptJWsqAtIb2LTBee3WWDuWttrPBzXbV/yHokOYKTKSC+fMznFBf2Ulke4Swvd/GlVDdOeMgSoZfFBfZrVX4zSTwallmYsr0w5WqM4D3jDlOZLkANQUkkpgHDbXZrSu1wQJBAPQov+2qgVcFXY6nwQ5To5QQYhnXIkNmwbBHMQ3BljLfbCrohrno+cI3AhnHtxzIz0yfHoYDk9feXPiPWKoswgsCQQC7vEIYGI1c7GE/fWv0Yoj3XGb7eqgfJ3a8Jab8DlUHnV8S4nmlLi/stP5kCrHmf8GA9R8ErgSnQUdLsJEIFAM1AkEAuN0lvLiNp6rLVJjVhphzUUc6T+Bg8/GYk3TDwmuh4rDhwHdAkwDAInnt4EEj9upgct5DiSqqRRb7A8PdWTP8UwJAamYU84EexTZ3GzujLnuV8tOczhRDKnz8Tz/rttkMmec4FgTjOpnFsZsWvm5NSzzG16aU8NsLahuWI7CrUe+9rQJAAWRb0AOajY3Yy+GocB6e3Z/VmIjwx2I+S1rCiFSy6S1XeBW2HviDiiyP9caEypn1Fqg+/Mm98y1rAPnjWde5NQ==
-----END PRIVATE KEY-----';
            $merchant_private_key = openssl_get_privatekey($merchant_private_key);
            openssl_sign($signStr, $sign_info, $merchant_private_key, OPENSSL_ALGO_MD5);
            $sign = base64_encode($sign_info);


            //提交到支付网关
            $postdata = array(
                'merchantNo' => $merchantNo,
                'merchantOrderNo' => $merchantOrderNo,
                'countryCode' => $countryCode,
                'currencyCode' => $currencyCode,
                'transferType' => $transferType,
                'transferAmount' => $transferAmount,
                'feeDeduction' => $feeDeduction,
                'remark' => $remark,
                'notifyUrl' => $notifyUrl,
                'extendedParams' => $extendedParams,
                'sign' => $sign
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.bpay.tv/api/v2/transfer/order/create");
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
            // dump($recode);die;
            if ($recode->code == 200) {
                $result = db::name("dotop")->where("id", $post['id'])->update(['status' => 3, 'orderNo' => $merchantOrderNo, 'info' => "pontuado com sucesso", "uptime" => time()]);


                $this->success("代付已提交，等待回调通知");
            } else {
                $this->error("操作失败");
            }


            // \app\common\model\User::money($cashrecord['money'], $cashrecord['user_id'], "pontuado com sucesso".$cashrecord['money']);
        }





        $this->error("请选择提现操作");
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

        if ($cashrecord['type'] == 0) {

            \app\common\model\User::money($cashrecord['money'], $cashrecord['user_id'], "pontuado com sucesso".$cashrecord['money']);
        }

        $result = db::name("dotop")->
        where("id", $post['id'])->update(['status' => 1, 'info' => "pontuado com sucesso", "uptime" => time()]);

        db::name('mx')->insert([
            'uid' => $cashrecord['user_id'],
            'info' => "pontuado com sucesso",
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


            if ($cashrecord['type'] == 1) {

                \app\common\model\User::money($cashrecord['money'], $cashrecord['user_id'], $post['remark'].$cashrecord['money']);
            }


            db::name('mx')->insert([
                'uid' => $cashrecord['user_id'],
                'info' => $post['remark'],
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
}
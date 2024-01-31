<?php

namespace app\admin\model;

use think\Model;


class Dotop extends Model
{

    

    

    // 表名
    protected $name = 'dotop';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'addtime_text',
        'status_text',
        'type_text'
    ];
    
   public function getStatusLists()
    {
        return ['0' => __('上分'),'1' => __('下分'),];
    }

    
    public function getStatusList()
    {
        return ['0' => __('待审核'),'1' => __('已同意'),'2' => __('已拒绝'),];
    }


    public function getAddtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['addtime']) ? $data['addtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getStatusLists();
        return isset($list[$value]) ? $list[$value] : '';
    }
    
    protected function setAddtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}

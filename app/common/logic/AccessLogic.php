<?php

namespace app\common\logic;

use think\facade\Validate;
use app\common\validate\Access as AccessValidate;
use think\exception\ValidateException;
use app\common\lib\ReturnData;
use app\common\model\Access;

class AccessLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Access();
    }

    public function getValidate()
    {
        return validate(AccessValidate::class);
    }

    //列表
    public function getList($where = array(), $order = '', $field = '*', $offset = 0, $limit = 15)
    {
        $res = $this->getModel()->getList($where, $order, $field, $offset, $limit);

        if ($res['list']) {
            foreach ($res['list'] as $k => $v) {
                //$res['list'][$k] = $this->getDataView($v);
            }
        }

        return $res;
    }

    //分页html
    public function getPaginate($where = array(), $order = '', $field = '*', $limit = 15)
    {
        $res = $this->getModel()->getPaginate($where, $order, $field, $limit);

        $res = $res->each(function ($item, $key) {
            //$item = $this->getDataView($item);
            return $item;
        });

        return $res;
    }

    //全部列表
    public function getAll($where = array(), $order = '', $field = '*', $limit = '')
    {
        $res = $this->getModel()->getAll($where, $order, $field, $limit);

        /* if($res)
        {
            foreach($res as $k=>$v)
            {
                //$res[$k] = $this->getDataView($v);
            }
        } */

        return $res;
    }

    //详情
    public function getOne($where = array(), $field = '*')
    {
        $res = $this->getModel()->getOne($where, $field);
        if (!$res) {
            return false;
        }

        //$res = $this->getDataView($res);

        return $res;
    }

    //添加
    public function add($data = array(), $type = 0)
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        try {
            $this->getValidate()->scene('add')->check($data);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $e->getError());
        }

        if ($this->getModel()->getOne($data)) {
            return ReturnData::create(ReturnData::FAIL, null, '已经存在');
        }

        $res = $this->getModel()->add($data, $type);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //修改
    public function edit($data, $where = array())
    {
        if (empty($data)) {
            return ReturnData::create(ReturnData::SUCCESS);
        }

		try {
            $this->getValidate()->scene('edit')->check($data);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $e->getError());
        }

        $res = $this->getModel()->edit($data, $where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    //删除
    public function del($where)
    {
        if (empty($where)) {
            return ReturnData::create(ReturnData::PARAMS_ERROR);
        }

        try {
            $this->getValidate()->scene('del')->check($where);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ReturnData::create(ReturnData::PARAMS_ERROR, null, $e->getError());
        }

        $res = $this->getModel()->del($where);
        if (!$res) {
            return ReturnData::create(ReturnData::FAIL);
        }

        return ReturnData::create(ReturnData::SUCCESS, $res);
    }

    /**
     * 数据获取器
     * @param array $data 要转化的数据
     * @return array
     */
    private function getDataView($data = array())
    {
        return getDataAttr($this->getModel(), $data);
    }
}
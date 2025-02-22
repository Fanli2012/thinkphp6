<?php

namespace app\common\logic;

use think\facade\Validate;
use app\common\validate\Kuaidi as KuaidiValidate;
use think\exception\ValidateException;
use app\common\lib\ReturnData;
use app\common\model\Kuaidi;

class KuaidiLogic extends BaseLogic
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function getModel()
    {
        return new Kuaidi();
    }

    public function getValidate()
    {
        return validate(KuaidiValidate::class);
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
                $res[$k] = $this->getDataView($v);
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

        //判断快递公司名称
        if (isset($data['name']) && !empty($data['name'])) {
            if ($this->getModel()->getOne(['name' => $data['name']])) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '快递公司名称已经存在');
            }
        }

        //判断公司编码
        if (isset($data['code']) && !empty($data['code'])) {
            if ($this->getModel()->getOne(['code' => $data['code']])) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '公司编码已经存在');
            }
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

        $record = $this->getModel()->getOne($where);
        if (!$record) {
            return ReturnData::create(ReturnData::RECORD_NOT_EXIST);
        }

        //判断快递公司名称
        if (isset($data['name']) && !empty($data['name'])) {
			$where2[] = ['name', '=', $data['name']];
            $where2[] = ['id', '<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where2)) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '快递公司名称已经存在');
            }
        }

        //判断公司编码
        if (isset($data['code']) && !empty($data['code'])) {
			$where2[] = ['code', '=', $data['code']];
            $where2[] = ['id', '<>', $record['id']]; //排除自身
            if ($this->getModel()->getOne($where2)) {
                return ReturnData::create(ReturnData::PARAMS_ERROR, null, '公司编码已经存在');
            }
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
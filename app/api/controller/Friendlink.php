<?php

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use app\common\lib\Token;
use app\common\lib\Helper;
use app\common\lib\ReturnData;
use app\common\logic\FriendlinkLogic;

class Friendlink extends Common
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new FriendlinkLogic();
    }

    //列表
    public function index()
    {
        //参数
        $where = array();
        $limit = input('limit', 10);
        $offset = input('offset', 0);
        if (input('group_id', null) !== null) {
			$where[] = array('group_id', '=', input('group_id'));
        }
        $orderby = input('orderby', 'listorder asc');

        $res = $this->getLogic()->getList($where, $orderby, '*', $offset, $limit);

        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }

    //详情
    public function detail()
    {
        //参数
        if (!checkIsNumber(input('id/d', 0))) {
            Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));
        }
        $where['id'] = input('id');

        $res = $this->getLogic()->getOne($where);
        if (!$res) {
            Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));
        }

        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $res = $this->getLogic()->add($_POST);

            Util::echo_json($res);
        }
    }

    //修改
    public function edit()
    {
        if (Helper::isPostRequest()) {
            if (!checkIsNumber(input('id/d', 0))) {
                Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));
            }
            $where['id'] = input('id');
            unset($_POST['id']);

            $res = $this->getLogic()->edit($_POST, $where);

            Util::echo_json($res);
        }
    }

    //删除
    public function del()
    {
        if (Helper::isPostRequest()) {
            if (!checkIsNumber(input('id/d', 0))) {
                Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));
            }
            $where['id'] = input('id');

            $res = $this->getLogic()->del($where);

            Util::echo_json($res);
        }
    }
}
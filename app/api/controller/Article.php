<?php

namespace app\api\controller;

use think\facade\Db;
use think\facade\Request;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ArticleLogic;
use app\common\model\Article as ArticleModel;

class Article extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLogic()
    {
        return new ArticleLogic();
    }

    //列表
    public function index()
    {
        //参数
        $limit = input('limit/d', 10);
        $offset = input('offset/d', 0);
        $where = array();
        if (input('type_id', '') !== '') {
            $where[] = array('type_id', '=', input('type_id'));
        }
        if (input('keyword', '') !== '') {
            $where[] = array('title', 'like', '%' . input('keyword') . '%');
        }
        if (input('tuijian', '') !== '') {
            $where[] = array('tuijian', '=', input('tuijian'));
        }
        if (input('status', '') === '') {
            $where[] = array('status', '=', ArticleModel::ARTICLE_STATUS_NORMAL);
        } else {
            if (input('status') != -1) {
                $where[] = array('status', '=', input('status'));
            }
        }
        $orderby = input('orderby', 'update_time desc');
        if ($orderby == 'rand()') {
            $orderby = array('orderRaw', 'rand()');
        }

        $res = $this->getLogic()->getList($where, $orderby, ['content'], $offset, $limit);
        if ($res['count'] > 0) {
            foreach ($res['list'] as $k => $v) {
                if (!empty($v->litpic)) {
                    $res['list'][$k]->litpic = get_site_cdn_address() . $v->litpic;
                }
            }
        }

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
        if (input('status', '') === '') {
            $where['status'] = ArticleModel::ARTICLE_STATUS_NORMAL;
        } else {
            if (input('status') != -1) {
                $where['status'] = input('status');
            }
        }

        $res = $this->getLogic()->getOne($where);
        if (!$res) {
            Util::echo_json(ReturnData::create(ReturnData::PARAMS_ERROR));
        }

        if ($res['content']) {
            $res['content'] = str_replace(' style=""', '', preg_replace('/src=\"\/uploads\//', 'src="' . get_site_cdn_address() . '/uploads/', $res['content']));
        }
        if ($res['litpic']) {
            $res['litpic'] = get_site_cdn_address() . $res['litpic'];
        }

        Util::echo_json(ReturnData::create(ReturnData::SUCCESS, $res));
    }

    //添加
    public function add()
    {
        if (Helper::isPostRequest()) {
            $_POST['add_time'] = $_POST['update_time'] = time();
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
            $_POST['update_time'] = time();

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
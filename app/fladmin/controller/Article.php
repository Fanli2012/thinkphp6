<?php

namespace app\fladmin\controller;

use think\facade\Db;
use think\facade\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;
use app\common\logic\ArticleLogic;

class Article extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function getLogic()
    {
        return new ArticleLogic();
    }

    public function index()
    {
        $where = array();
        if (!empty($_REQUEST["keyword"])) {
            $where[] = ['title', 'like', '%' . $_REQUEST['keyword'] . '%'];
        }
        if (!empty($_REQUEST["type_id"]) && $_REQUEST["type_id"] > 0) {
            $where[] = ['type_id', '=', $_REQUEST["type_id"]];
        }
        $where[] = ['delete_time', '=', 0]; //0未删除
        $where[] = ['status', '=', 0];//审核过的文章
        if (!empty($_REQUEST["status"])) {
            $where[] = ['status', '=', $_REQUEST["status"]];//未审核过的文章
        }

        $list = $this->getLogic()->getPaginate($where, ['update_time' => 'desc'], ['content']);

        $assign_data['page'] = $list->render(); // 获取分页显示
        $assign_data['list'] = $list;
        //echo '<pre>';print_r($list);exit;

        //分类列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $assign_data['article_type_list'] = $article_type_list;

        return view('', $assign_data);
    }

    public function add()
    {
        if (Helper::isPostRequest()) {
            //表单令牌验证
			$validate = Validate::rule('__token__', 'token');
			if (!$validate->check($_POST)) {
				$this->error('非法提交|请不要重复提交表单');
			}

			//缩略图
            $litpic = "";
            if (!empty($_POST["litpic"])) {
                $litpic = $_POST["litpic"];
            } else {
                $_POST['litpic'] = "";
            }
			//description
            if (empty($_POST["description"])) {
                if (!empty($_POST["content"])) {
                    $_POST['description'] = cut_str($_POST["content"]);
                }
            }
            $content = "";
            if (!empty($_POST["content"])) {
                $content = $_POST["content"];
            }

            $update_time = time();
            if ($_POST['update_time']) {
                $update_time = strtotime($_POST['update_time']);
            } // 更新时间
            $_POST['add_time'] = $_POST['update_time'] = $update_time;
			$_POST['admin_id'] = $this->admin_info['id']; // 管理员发布者ID
			
            //关键词
            if (!empty($_POST["keywords"])) {
                $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
            } else {
                if (!empty($_POST["title"])) {
                    $title = $_POST["title"];
                    $title = str_replace("，", "", $title);
                    $title = str_replace(",", "", $title);
                    $_POST['keywords'] = get_participle($title); // 标题分词
                }
            }

            if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
                $_POST['keywords'] = mb_strcut($_POST['keywords'], 0, 60, 'UTF-8');
            }
            if (isset($_POST["dellink"]) && $_POST["dellink"] == 1 && !empty($content)) {
                $content = logic('Article')->replacelinks($content, array(http_host()));
            } //删除非站内链接
            $_POST['content'] = $content;

            // 提取第一个图片为缩略图
            if (isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic)) {
                $litpic = logic('Article')->getBodyFirstPic($content);
                if ($litpic) {
                    $_POST['litpic'] = $litpic;
                }
            }

            $res = $this->getLogic()->add($_POST);
            if ($res['code'] == ReturnData::SUCCESS) {
                //Tag添加
                if (isset($_POST['tags']) && $_POST["tags"] != '') {
                    $tags = $_POST['tags'];
                    $tags = explode(',', str_replace('，', ',', $tags));
                    foreach ($tags as $row) {
                        $tag_id = model('Tag')->getFieldValue(array('name' => $row), 'id');
                        if ($tag_id) {
                            $data2['tag_id'] = $tag_id;
                            $data2['article_id'] = $res['data'];
                            logic('Taglist')->add($data2);
                        }
                    }
                }

                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        //文章添加到哪个栏目下
        $assign_data['type_id'] = input('type_id/d', 0);

        //栏目列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $assign_data['article_type_list'] = $article_type_list;

        return view('', $assign_data);
    }

    public function edit()
    {
        if (Helper::isPostRequest()) {
            $id = $where['id'] = $_POST['id'];
            unset($_POST['id']);

            $litpic = "";
            if (!empty($_POST["litpic"])) {
                $litpic = $_POST["litpic"];
            } else {
                $_POST['litpic'] = "";
            } //缩略图
            if (empty($_POST["description"])) {
                if (!empty($_POST["content"])) {
                    $_POST['description'] = cut_str($_POST["content"]);
                }
            } //description
            $content = "";
            if (!empty($_POST["content"])) {
                $content = $_POST["content"];
            }

            $update_time = time();
            if ($_POST['update_time']) {
                $update_time = $_POST['add_time'] = strtotime($_POST['update_time']);
            } // 更新时间
            $_POST['update_time'] = $update_time;

            //关键词
            if (!empty($_POST["keywords"])) {
                $_POST['keywords'] = str_replace("，", ",", $_POST["keywords"]);
            } else {
                if (!empty($_POST["title"])) {
                    $title = $_POST["title"];
                    $title = str_replace("，", "", $title);
                    $title = str_replace(",", "", $title);
                    $_POST['keywords'] = get_participle($title); // 标题分词
                }
            }

            if (isset($_POST['keywords']) && !empty($_POST['keywords'])) {
                $_POST['keywords'] = mb_strcut($_POST['keywords'], 0, 60, 'UTF-8');
            }
            if (isset($_POST["dellink"]) && $_POST["dellink"] == 1 && !empty($content)) {
                $content = logic('Article')->replacelinks($content, array(http_host()));
            } //删除非站内链接
            $_POST['content'] = $content;

            // 提取第一个图片为缩略图
            if (isset($_POST["autolitpic"]) && $_POST["autolitpic"] && empty($litpic)) {
                $litpic = logic('Article')->getBodyFirstPic($content);
                if ($litpic) {
                    $_POST['litpic'] = $litpic;
                }
            }

            $res = $this->getLogic()->edit($_POST, $where);
            if ($res['code'] == ReturnData::SUCCESS) {
                //Tag添加
                if (isset($_POST['tags']) && $_POST["tags"] != '') {
                    $tags = $_POST['tags'];
                    $tags = explode(',', str_replace('，', ',', $tags));
                    model('Taglist')->del(array('article_id' => $id));
                    foreach ($tags as $row) {
                        $tag_id = model('Tag')->getFieldValue(array('name' => $row), 'id');
                        if ($tag_id) {
                            $data2['tag_id'] = $tag_id;
                            $data2['article_id'] = $id;
                            logic('Taglist')->add($data2);
                        }
                    }
                }

                $this->success($res['msg'], url('index'), '', 1);
            }

            $this->error($res['msg']);
        }

        if (!checkIsNumber(input('id', null))) {
            $this->error('参数错误');
        }
        $where['id'] = input('id');
        $assign_data['id'] = $where['id'];

        $post = $this->getLogic()->getOne($where);
        $assign_data['post'] = $post;

        //Tag标签
        $tags = '';
        $taglist = model('Taglist')->getAll(['article_id' => $where['id']]);
        if ($taglist) {
            foreach ($taglist as $k => $v) {
                $tmp[] = model('Tag')->getFieldValue(['id' => $v['tag_id']], 'name');
            }
            if (!empty($tmp)) {
                $tags = implode(',', $tmp);
            }
        }
        $assign_data['tags'] = $tags;

        //栏目列表
        $article_type_list = model('ArticleType')->tree_to_list(model('ArticleType')->list_to_tree());
        $assign_data['article_type_list'] = $article_type_list;

        return view('', $assign_data);
    }

    //删除
    public function del()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('参数错误', url('index'), '', 3);
        }

        $res = model('Article')->del("id in ($id)");
        if ($res) {
            $this->success("$id ,删除成功", url('index'), '', 1);
        }

        $this->error('删除失败');
    }

    //文章重复列表
    public function repetarc()
    {
		$assign_data['list'] = Db::query("select title,count(*) AS count from " . env('database.prefix') . "article group by title HAVING count>1 order by count DESC");
		return view('', $assign_data);
    }

    //文章推荐
    public function recommendarc()
    {
        if (!empty($_GET["id"])) {
            $id = $_GET["id"];
        } else {
            $this->error('参数错误', url('index'), '', 3);
        } //if(preg_match('/[0-9]*/',$id)){}else{exit;}

        $data['tuijian'] = 1;
        $res = model('Article')->edit($data, "id in ($id)");
        if ($res) {
            $this->success("$id ,推荐成功");
        }

        $this->error("$id ,推荐失败！请重新提交");
    }

    //文章是否存在
    public function articleexists()
    {
        $map = [];
        if (!empty($_GET["title"])) {
			$map[] = ['title', '=', $_GET["title"]];
        }

        if (!empty($_GET["id"])) {
			$map[] = ['id', '<>', $_GET["id"]];
        }

        echo model('Article')->getCount($map);
    }

	// 批量添加文章标题
    public function batch_add_article_title()
    {
		$time = time();
        if (Helper::isPostRequest()) {
			if (empty($_POST['titles'])) {
				$this->error('标题不能为空');
			}
			// 最新文章
			$zuixin_article = db('article')->order(['add_time'=>'desc'])->find();

			$data = [];
			$_POST['titles'] = str_replace(" ","",str_replace("\n",",",$_POST['titles']));
            $titles = explode(",", $_POST['titles']);
			foreach ($titles as $row) {
				if (empty(trim($row))) {
					continue;
				}
				// 判断标题是否存在
				$article = model('Article')->getOne(['title'=>$row]);
				if ($article) {
					continue;
				}
				$add_time = $zuixin_article['add_time'] + rand(72000,9000);
				$data[] = [
					'type_id' => 1,
					'click' => rand(200, 500),
					'title' => $row,
					'keywords' => $row,
					'description' => $row,
					'status' => 1,
					'add_time' => $add_time,
					'update_time' => $add_time,
				];
			}
			if (!$data) {
				$this->error('没有数据');
			}
			$res = db('article')->strict(false)->insertAll($data);
			if (!$res) {
				$this->error('操作失败');
			}
            $this->success('操作成功');
        }

        return view();
    }
}
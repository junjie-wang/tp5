<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/15 0015
 * Time: 下午 5:15
 */
namespace  app\admin\controller;
\think\Loader::import('controller/Controller', \think\Config::get('traits_path'), EXT);

use app\admin\Controller;
use app\common\model\DbModel;
use think\Db;
use think\Loader;
use think\Config;
use think\exception\HttpException;
class Test extends Controller{
    use \app\admin\traits\controller\Controller;

    protected static $blacklist = [];

    /**
     * 重写 index方法
     * @return mixed
     */
    public function index()
    {
        $map=[];
        if(isset($_GET['phone'])){
            if(!empty($_GET['phone'])){
                $map['phone']=$_GET['phone'];
            }
        }
        if(isset($_GET['ways_type'])){
            if(!empty($_GET['ways_type'])){
                $map['ways_type']=$_GET['ways_type'];
            }
        }
        if(isset($_GET['is_success'])){
            if(!empty($_GET['is_success'])){
                $map['is_success']=$_GET['is_success'];
            }
        }
        // 每页数据数量
        $listRows = $this->request->param('numPerPage') ?: Config::get("paginate.list_rows");
        $list = Db::name("creditcard_packet_record")
            ->where($map)
            ->paginate($listRows, false, ['query' => $this->request->get()]);
        // 模板赋值显示
        $this->view->assign('list', $list);
        $this->view->assign("count", $list->total());
        $this->view->assign("page", $list->render());
        $this->view->assign('numPerPage', $list->listRows());
        return $this->view->fetch();
    }
    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            // 插入
            $data = $this->request->post();
            Db::name("creditcard_packet_record")->insertGetId($data);
            return ajax_return_adv('添加成功');
        } else {
            // 添加
            return $this->view->fetch(isset($this->template) ? $this->template : 'edit');
        }
    }

    /**
     * 修改数据
     */
    public function edit()
    {
        if ($this->request->isAjax()) {
            // 更新
            $data = $this->request->post();
            if (!$data['id']) {
                return ajax_return_adv_error("缺少参数ID");
            }
            // 简单的直接使用db更新
            Db::startTrans();
            try {
                $model = Db::name("creditcard_packet_record");
                $ret = $model->where('id', $data['id'])->update($data);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                return ajax_return_adv_error($e->getMessage());
            }
            return ajax_return_adv("编辑成功");
        } else {
            // 编辑
            $id = $this->request->param('id');
            if (!$id) {
                throw new HttpException(404, "缺少参数ID");
            }
            $vo =  Db::name("creditcard_packet_record")->find($id);
            if (!$vo) {
                throw new HttpException(404, '该记录不存在');
            }
            $this->view->assign("vo", $vo);
            return $this->view->fetch();
        }
    }


    /**
     * 默认更新字段方法
     *
     * @param string     $field 更新的字段
     * @param string|int $value 更新的值
     * @param string     $msg   操作成功提示信息
     * @param string     $pk    主键，默认为主键
     * @param string     $input 接收参数，默认为主键
     */
    protected function updateField($field, $value, $msg = "操作成功", $pk = "", $input = "")
    {
        $model = new DbModel("creditcard_packet_record");
        if (!$pk) {
            $pk = $model->getPk();
        }
        if (!$input) {
            $input = $model->getPk();
        }
        $ids = $this->request->param($input);
        $where[$pk] = ["in", $ids];
        if (false === $model->where($where)->update([$field => $value])) {
            return ajax_return_adv_error($model->getError());
        }

        return ajax_return_adv($msg, '');
    }
    /**
     * 清空回收站
     */
    public function clear()
    {
        $model = new DbModel("creditcard_packet_record");
        $where[$this->fieldIsDelete] = 1;
        if (false === $model->where($where)->delete()) {
            return ajax_return_adv_error($model->getError());
        }

        return ajax_return_adv("清空回收站成功");
    }
    /**
     * 永久删除
     */
    public function deleteForever()
    {
        $model = new DbModel("creditcard_packet_record");
        $pk = $model->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === $model->where($where)->delete()) {
            return ajax_return_adv_error($model->getError());
        }

        return ajax_return_adv("删除成功");
    }
    public function  match(){
        echo "正在开发中.....";
    }

    public function  test(){
        $circle_rs=Db::name("circle_user")
            ->where("phone",'like',$str)
            ->where("apply_credit_result",1)
            ->select();
    }
}
<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author    yuan1994 <tianpian0805@gmail.com>
 * @link      http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace app\admin\traits\controller;

use app\common\model\DbModel;
use think\Db;
use think\Loader;
use think\exception\HttpException;
use think\Config;

trait AdminController
{
    abstract  function  getTableName();
    /**
     * 首页
     * @return mixed
     */
    public function index()
    {
        $tablename=$this->getTableName();
        $model = $this->getModel($tablename);
        // 列表过滤器，生成查询Map对象
        $map = $this->search($model, [$this->fieldIsDelete => $this::$isdelete]);

        // 特殊过滤器，后缀是方法名的
        $actionFilter = 'filter' . $this->request->action();
        if (method_exists($this, $actionFilter)) {
            $this->$actionFilter($map);
        }

        // 自定义过滤器
        if (method_exists($this, 'filter')) {
            $this->filter($map);

         // show($map);exit;
        }

        $this->datalist($model, $map);

        return $this->view->fetch();
    }

    /**
     * 回收站
     * @return mixed
     */
    public function recycleBin()
    {
        $this::$isdelete = 1;

        return $this->index();
    }

    /**
     * 添加
     * @return mixed
     */
    public function add()
    {
        $controller = $this->request->controller();
        //将控制器转换为小写
        $controller=strtolower($controller);
        $module=$this->request->module();
        $action=$this->request->action()."_html";
        $str="/".$module."/".$controller."/".$action;
        if ($this->request->isAjax()) {
            // 插入
            $data = $this->request->except(['id',$str]);

            // 验证
            if (class_exists($validateClass = Loader::parseClass(Config::get('app.validate_path'), 'validate', $controller))) {
                $validate = new $validateClass();
                if (!$validate->check($data)) {
                    return ajax_return_adv_error($validate->getError());
                }
            }

            // 写入数据
            // 简单的直接使用db写入
            Db::startTrans();
            try {
                $tableName=$this->getTableName();
                $model=$this->getModel($tableName);
                $ret = $model->insert($data);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                return ajax_return_adv_error($e->getMessage());
            }

            return ajax_return_adv('添加成功');
        } else {
            // 添加
            return $this->view->fetch(isset($this->template) ? $this->template : 'edit');
        }
    }

    /**
     * 编辑
     * @return mixed
     */
    public function edit()
    {
        $controller = $this->request->controller();

        if ($this->request->isAjax()) {
            // 更新
            $data = $this->request->post();
            if (!$data['id']) {
                return ajax_return_adv_error("缺少参数ID");
            }

            // 验证
            if (class_exists($validateClass = Loader::parseClass(Config::get('app.validate_path'), 'validate', $controller))) {
                $validate = new $validateClass();
                if (!$validate->check($data)) {
                    return ajax_return_adv_error($validate->getError());
                }
            }

            // 简单的直接使用db更新
            Db::startTrans();
            try {
                $tableName=$this->getTableName();
                $model = $this->getModel($tableName);
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
            $tableName=$this->getTableName();
            $model = $this->getModel($tableName);
            $vo = $model->find($id);
            if (!$vo) {
                throw new HttpException(404, '该记录不存在');
            }

            $this->view->assign("vo", $vo);

            return $this->view->fetch();
        }
    }

    /**
     * 默认删除操作
     */
    public function delete()
    {
        $tableName=$this->getTableName();
        return $this->updateField($tableName,$this->fieldIsDelete, 1, "移动到回收站成功");
    }

    /**
     * 从回收站恢复
     */
    public function recycle()
    {
        $tableName=$this->getTableName();
        return $this->updateField($tableName,$this->fieldIsDelete, 0, "恢复成功");
    }

    /**
     * 默认禁用操作
     */
    public function forbid()
    {
        $tableName=$this->getTableName();
        return $this->updateField($tableName,$this->fieldStatus, 0, "禁用成功");
    }


    /**
     * 默认恢复操作
     */
    public function resume()
    {
        $tableName=$this->getTableName();

        return $this->updateField($tableName,$this->fieldStatus, 1, "恢复成功");
    }


    /**
     * 永久删除
     */
    public function deleteForever()
    {
        $tableName=$this->getTableName();
        $model = $this->getModel($tableName);
        $pk = $model->getPk();
        $ids = $this->request->param($pk);
        $where[$pk] = ["in", $ids];
        if (false === $model->where($where)->delete()) {
            return ajax_return_adv_error($model->getError());
        }

        return ajax_return_adv("删除成功");
    }

    /**
     * 清空回收站
     */
    public function clear()
    {
        $tableName=$this->getTableName();
        $model = $this->getModel($tableName);
        $where[$this->fieldIsDelete] = 1;
        if (false === $model->where($where)->delete()) {
            return ajax_return_adv_error($model->getError());
        }

        return ajax_return_adv("清空回收站成功");
    }

    /**
     * 保存排序
     */
    public function saveOrder()
    {
        $param = $this->request->param();
        if (!isset($param['sort'])) {
            return ajax_return_adv_error('缺少参数');
        }

        $tableName=$this->getTableName();
        $model = $this->getModel($tableName);
        foreach ($param['sort'] as $id => $sort) {
            $model->where('id', $id)->update(['sort' => $sort]);
        }

        return ajax_return_adv('保存排序成功', '');
    }
}

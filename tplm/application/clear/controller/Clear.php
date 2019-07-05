<?php
namespace app\clear\controller;


class Clear{

    public function session(){
        session_start();
        session_destroy();
        echo "清除缓存成功！清除时间：".date('Y-m-d H:i:s',time());
    }
}


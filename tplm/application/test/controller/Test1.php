<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28 0028
 * Time: 下午 1:58
 */
namespace test;

class  Test1  {
     public function test(){
         $m=[['a'=>'name','b'=>'zhagnsan'],['a'=>'age','b'=>20],['a'=>'address','b'=>'guiyang']];

      //   var_dump($m);

         $n=[];
         //code 补充代码
        foreach($m as $key=>$value){
           // show($value[$key]);
            $n[$value['a']]=$value['b'];

        }
         show($n);
       //  var_dump($n);

         die;
     }
    public function  tt(){
        echo ";123";
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20 0020
 * Time: 上午 8:58
 */

namespace app\common\controller;
/**
 * 操作excel的工具类
 * Class UploadExcelUtil
 * @package app\common\controller
 * @param author 栀青
 * @param time 2018-04-20
 */
require_once EXTEND_PATH.'excel/PHPExcel/IOFactory.php';
require_once EXTEND_PATH.'excel/PHPExcel.php';
//require_once VENDOR_PATH."phpoffice/phpexcel/Classes/PHPExcel/IOFactory.php";
//require_once VENDOR_PATH."phpoffice/phpexcel/Classes/PHPExcel.php";
class ExcelUtil
{
   /**
    * 这是一个上传excel的方法
    * 友情提示，将上传的excel文件
    * 存放到服务器upload目录中，调用此上
    * 传方法需要用form 表单上传，type=file，最大上传文件不可以大于5M哦
    * @param $file 表单控件的name值  $inputName,默认为file
    * @param $file 获得表单上传的文件对象  传入$_FILES['file']对象，前提input表单的name值是file才可以这么写哦
    * @param $filepathname 上传文件保存的位置，如果选择不传，则默认保存在upload/circle/年月日的文件夹内
    * @return bool|string 验证通过返回true，验证错误返回错误信息。（调用方需要用===判断）
    */
    public static  function  uploadExcel($file,$filepathname=''){
        //step1:获取上传文件的信息
        if(!isset($file)){
            return "非法请求";
        }
        //判断是否选择了要上传的表格
        if (empty($file)) {
            return  "<script>alert('您未选择上传的csv文件上传!');history.go(-1);</script>";
        }

//获取表格的大小，限制上传表格的大小5M
        $file_size = $file['size'];
        if ($file_size>5*1024*1024) {
            return  "<script>alert('上传失败，上传的表格不能超过5M的大小');history.go(-1);</script>";
        }
        $tmp_file = $file ['tmp_name'];
        $file_name=$file['name'];//客户端文件的原名称
        $file_types = explode ( ".", $file['name'] );
        $file_type = $file_types [count ( $file_types ) - 1];
        $file_type=strtolower ($file_type);
        /*判别是不是.xls文件，判别是不是excel文件*/
        if ($file_type!="csv"&&$file_type!="xls"&&$file_type!="xlsx")
        {
            return  "<script>alert('不是csv或者excel文件，请选择csv文件重新上传!');history.go(-1);</script>";
        }
        $name=$file['name'];//客户端文件的原名称
         //判断上传文件路径是否为空
        if(empty($filepathname)){
            //如果上传文件路径为空，则创建保存文件的路径
                //创建保存上传excel的文件路径
                $date=date("Ymd",time());
                $path="circle".DS.$date;
                $filepathname=ROOT_PATH."upload".DS.$path.DS;
                //判断文件夹是否存在
                $dir = iconv("UTF-8", "GB2312//IGNORE", $filepathname);
                if (!file_exists($dir)){
                    mkdir ($dir,0777,true);//创建文件夹
                }
        }

        move_uploaded_file($tmp_file,$filepathname.$file_name);//将上传到服务器临时文件夹的文件重新移动到新位置
        $error=$file["error"];//上传后系统返回的值,0表示上传成功
        if($error!=0){
            return  "<script>alert('上传失败');history.go(-1);</script>";
        }
        return true;
    }
    /**
     * 逐行读取excel文件
     * @param $file_path excel文件的保存路径
     * @param $sheetIndex 读取哪个工作表,默认为第一个
     * @return $result 返回读取数据的结果
     */
    public static function ReadexcelToArray($file_path, $sheetIndex = 0){
        if(empty($file_path) || !file_exists($file_path)){
            return apiData(-1,"读取exlce数据错误!请确定文件是否存在!");
        }
        $inputFileType = \PHPExcel_IOFactory::identify($file_path);//判断用哪一个类读取文件
        $reader = \PHPExcel_IOFactory::createReader($inputFileType);//获取读取的对象
       // show($reader);exit;
        $PHPExcel = $reader->load($file_path); // 载入excel文件
        $sheet = $PHPExcel->getSheet($sheetIndex); // 读取第一個工作表
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumm = $sheet->getHighestColumn(); // 取得总列数
        $highestColumm= \PHPExcel_Cell::columnIndexFromString($highestColumm); //字母列转换为数字列 如:AA变为27

        /** 循环读取每个单元格的数据 */
        $data=[];
        for ($row = 1; $row <= $highestRow; $row++){//行数是以第1行开始
            for ($column = 0; $column < $highestColumm; $column++) {//列数是以第0列开始
                $columnName = \PHPExcel_Cell::stringFromColumnIndex($column);
                $cell= $sheet->getCellByColumnAndRow($column, $row);
                $value=$cell->getValue();
                //判断是否为日期类型
                if($cell->getDataType()==\PHPExcel_Cell_DataType::TYPE_NUMERIC){
                    $cellstyleformat=$cell->getParent()->getStyle( $cell->getCoordinate() )->getNumberFormat();
                    $formatcode=$cellstyleformat->getFormatCode();
                    if (preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatcode)) {
                        $value=gmdate("Y/m/d", \PHPExcel_Shared_Date::ExcelToPHP($value));
                    }else{
                        $value=\PHPExcel_Style_NumberFormat::toFormattedString($value,$formatcode);
                    }
//                    echo $value,$formatcode,'<br>';

                }
                $data[$row][]=$value;
            }
        }
        return apiData(1,"读取成功",$data);
    }
    /**
     * 创建(导出)csv数据表格
     * @param  array   $list 要导出的数组格式的数据
     * @param  string  $filename 导出的csv表格数据表的文件名
     * @param  array   $header Excel表格的表头
     * @param  array   $index $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
     * 比如: $header = array('编号','姓名','性别','年龄');
     *       $index = array('id','username','sex','age');
     *       $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
     *       $filename="圈子营销贺卡数据";
     * @return [array] [数组]
     */
    public static function createCSV($list,$filename,$header=array(),$index=array()){
        $teble_header = implode(",",$header);
        $teble_header=$teble_header.",";
        $strexport = $teble_header."\r";
        foreach ($list as $row){
            foreach($index as $val){
                $strexport.=$row[$val].",";
            }
            $strexport.="\r";

        }
       // $strexport=iconv('UTF-8',"GB2312//IGNORE",$strexport);
        header("Content-type:text/csv;charset=utf-8");
        header("Content-Disposition:filename=".$filename);
        exit($strexport);

    }
    /**
     * 创建(导出)Excel数据表格
     * @param  array   $list 要导出的数组格式的数据
     * @param  string  $filename 导出的Excel表格数据表的文件名
     * @param  array   $header Excel表格的表头
     * @param  array   $index $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
     * 比如: $header = array('编号','姓名','性别','年龄');
     *       $index = array('id','username','sex','age');
     *       $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
     * @return [array] [数组]
     */
    /**
     * 创建(导出)Excel数据表格
     * @param  array   $list 要导出的数组格式的数据
     * @param  string  $filename 导出的Excel表格数据表的文件名
     * @param  array   $header Excel表格的表头
     * @param  array   $index $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
     * 比如: $header = array('编号','姓名','性别','年龄');
     *       $index = array('id','username','sex','age');
     *       $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
     * @return [array] [数组]
     */
    public static  function createTable($list,$filename,$header=array(),$index = array()){
        $teble_header = implode("\t",$header);
        $strexport = $teble_header."\r";
        foreach ($list as $row){
            foreach($index as $val){
                $strexport.=$row[$val]."\t";
            }
            $strexport.="\r";

        }
        $strexport=iconv('UTF-8',"GB2312//IGNORE",$strexport);
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename=".$filename);
        exit($strexport);
    }

    /**
     * 读取csv文件的内容
     * @param $path 路径
     * @return array 文件中的内容
     */
    public static function  readCSV($path){
        $fh = @fopen($path,"r") or die("打开csv文件出错！");
// if条件避免无效指针
        $data=[];
        if($fh){
            while(!feof($fh)) {
                $row=fgets($fh);//读取一行文件
                $row = eval('return '.iconv('gbk','utf-8',var_export($row,true)).';');//将读取出来的第一行文件进行重新编码，解决乱码的问题
                $row=trimzq($row);//去掉第行字符中间的空格
                $row=explode(",",$row);//将每行数据转换为数组
                if($row){
                    foreach($row as $key=>$value){
                        if(is_string($value)){
                            $value=str_replace("\""," ",$value);
                            $value=trimzq($value);
                            $row[$key]=$value;
                        }
                    }

                }
                $data[]=$row;

            }
            fclose($fh);//关闭文件流
        }

        return $data;
    }

}
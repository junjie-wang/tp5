<?php
namespace app\common\controller;
/**
 * 密文处理类
 *
 * Project: Weibo Developer Framwork
 * This is NOT a freeware, use is subject to license terms!
 *
 * Site: http://www.yxgz.CN
 *
 * <Exemple>
 *
 *	$process = new Cipher();
 *
 *  $str = $process->encrypt('15180719363');    // 加密处理
 *	$str = $process->decrypt($str);             // 解密处理
 *
 * <Exemple>
 *
 * $Id: Cipher.class.php 467 2014-07-24 09:29:17Z wgw $
 *
 * Copyright (C) 2007-2011 yxgz.CN Developer Team. All Rights Reserved.
 */
class Cipher {

    /**
     * 加密钥匙
     *
     * @access private
     */
    private $key;

    /**
     * 构造函数
     *
     * @access public
     */
    public function __construct( $key = '' ) {

        /* 初始化加密钥匙 */
        if( empty($key) ) {
            $this->key = '6EmNJb4v2cAPm9JC';
        } else {
            $this->key = $key;
        }
    }


    /**
     * 数据加密处理
     *
     * @param string $string 待加密的字符串
     *
     * @access public
     *
     * @return string
     */
    public function encrypt( $string,$key=null ) {
        if(!empty($key)){
          $this->key=$key;
        }

        $ctr = 0;
        $tmp = '';

        /* 生成随机加密键 */
        srand((double)microtime() * 1000000);
        $encrypt_key = md5( rand(0, 32000) );


        /* 生成密文 */
        for($i = 0;$i<strlen($string);$i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($string[$i]^$encrypt_key[$ctr++]);
        }


        /* 返回加密后的字符串 */
        return base64_encode(self::__key($tmp, $this->key));
    }


    /**
     * 数据解密处理
     *
     * @param string $string 待解密的字符串
     *
     * @access public
     *
     * @return string
     */
    public function decrypt( $string ) {

        $tmp = '';
        $string = self::__key(base64_decode($string),  $this->key);


        /* 循环解密 */
        for($i=0; $i<strlen($string); $i++) {
            $md5  = $string[$i];
            $tmp .= $string[++$i] ^ $md5;
        }

        return $tmp;
    }


    /**
     * 数据译码处理
     *
     * @param string $txt           字符串
     * @param string $encrypt_key   译码键
     *
     * @access private
     *
     * @return string
     */
    private function __key($txt, $encrypt_key) {

        $ctr = 0;
        $tmp = '';

        $encrypt_key = md5($encrypt_key);
        for($i = 0; $i < strlen($txt); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }


    /**
     * 加密函数
     *
     * @param strng   $txt 文本
     * @param boolean $key 键值
     *
     * @return  string 加密后的字符串
     */
    private function __jiami($txt, $key = null) {

        if(empty($key)) $key = config('SECURE_CODE');
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=+";

        $nh = rand(0,64);
        $ch = $chars[$nh];
        $mdKey = md5($key.$ch);

        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = base64_encode($txt);

        $tmp = '';
        $i=0;$j=0;$k = 0;

        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = ($nh+strpos($chars,$txt[$i])+ord($mdKey[$k++]))%64;
            $tmp .= $chars[$j];
        }
        return $ch.$tmp;
    }

    /**
     * 解密函数
     */
    private function __jiemi($txt,$key=null) {

        if(empty($key)) $key = config('SECURE_CODE');
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-=+";

        $ch = $txt[0];
        $nh = strpos($chars,$ch);
        $mdKey = md5($key.$ch);

        $mdKey = substr($mdKey,$nh%8, $nh%8+7);
        $txt = substr($txt,1);

        $tmp = '';
        $i=0;$j=0; $k = 0;

        for ($i=0; $i<strlen($txt); $i++) {
            $k = $k == strlen($mdKey) ? 0 : $k;
            $j = strpos($chars,$txt[$i])-$nh - ord($mdKey[$k++]);
            while ($j<0) $j+=64;
            $tmp .= $chars[$j];
        }
        return base64_decode($tmp);
    }
    private function __desencrypt($input,$key) {

        $size = mcrypt_get_block_size('des', 'ecb');
        $input = pkcs5_pad($input, $size);

        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);

        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        $data = base64_encode($data);

        return $data;
    }
    private function __desdecrypt($encrypted,$key) {
        $encrypted = base64_decode($encrypted);

        /* 使用MCRYPT_DES算法,cbc模式  */
        $td = mcrypt_module_open('des','','ecb','');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);

        /* 初始处理 */
        @mcrypt_generic_init($td, $key, $iv);

        /* 解密 */
        $decrypted = mdecrypt_generic($td, $encrypted);

        /* 结束 */
        mcrypt_generic_deinit($td);

        mcrypt_module_close($td);

        return pkcs5_unpad($decrypted);;
    }
    private function __pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    private function __pkcs5_unpad($text) {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text))
            return false;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad)
            return false;
        return substr($text, 0, -1 * $pad);
    }
    /**
     * 析构器
     *
     * @access public
     */
    public function __destruct() {
        $this->key = null;
    }
}

<?php
namespace app\common\controller;
/**
 * The model class file of Tiwer Developer Framework.
 *
 * Tiwer Developer Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Tiwer Developer Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Tiwer Developer Framework.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright (C) 2007-2011 Tiwer Studio. All Rights Reserved.
 * @author      wgw8299 <wgw8299@gmail.com>
 * @package     Tiwer Developer Framework
 * @version     $Id: DesUtil.class.php 4218 2016-01-19 07:37:10Z zzy $
 * @link        http://www.tiwer.cn
 *
 * des 加密解密类
 */
class Des
{

    var $key;
    var $iv; //偏移量

    function Des() {
        //key长度8例如:1234abcd

    }

    function encrypt($str,$key) {

        if(strlen($key)<8){
            return '';
        }

        $this->key = substr($key,0,4).substr($key,-4);
        $this->iv = $this->key; //默认以$key 作为 iv
        $str=base64_encode($str);

        //加密，返回大写十六进制字符串
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );

        $str = $this->pkcs5Pad ( $str, $size );
        //$strcc=strtoupper(bin2hex( mcrypt_encrypt(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv ) ) );
        $strcc = strtoupper(bin2hex(mcrypt_encrypt(MCRYPT_DES, $this->key,  $str, MCRYPT_MODE_CBC, $this->iv)));
        return base64_encode($strcc);
    }

    function decrypt($str,$key) {
        if(strlen($key)<8){
            return '';
        }
        $this->key = substr($key,0,4).substr($key,-4);
        $this->iv = $this->key; //默认以$key 作为 iv
        //解密
        $str=base64_decode($str);
        $strBin = $this->hex2bin( strtolower( $str ) );
        $str = mcrypt_decrypt( MCRYPT_DES, $this->key, $strBin, MCRYPT_MODE_CBC, $this->iv );
        $str = $this->pkcs5Unpad( $str );
        return base64_decode($str);
    }

    function hex2bin($hexData) {
        $binData = "";
        for($i = 0; $i < strlen ( $hexData ); $i += 2) {
            $binData .= chr ( hexdec ( substr ( $hexData, $i, 2 ) ) );
        }
        return $binData;
    }

    function pkcs5Pad($text, $blocksize) {
        $blocksize =$blocksize==0?1:$blocksize;
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }

    function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}
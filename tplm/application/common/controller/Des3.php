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
 * 3des 加密解密类
 */

class Des3
{

    private $key;

    /**
     * 设置3des 加密 key 值
     * @param unknown $key
     */
    public function key($key){
        $this->key = $key;
    }


    /**
     * 3des 数据加密
     *
     * @param unknown $input
     *
     * @return unknown
     */
    public function encrypt($input,$key=null) {
        if(!empty($key)) $this->key = $key;

        $size = mcrypt_get_block_size ( MCRYPT_3DES, 'ecb' );
        $input = $this->pkcs5_pad ( $input, $size );
        $key = str_pad ( $this->key, 24, '0' );
        $td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
        $iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
        @mcrypt_generic_init ( $td, $key, $iv );
        $data = mcrypt_generic ( $td, $input );
        mcrypt_generic_deinit ( $td );
        mcrypt_module_close ( $td );
        $data = base64_encode ( $data );
        return $data;
    }
    /**
     * 3des 数据解密
     *
     * @param string $encrypted
     * @param string $key
     *
     * @return boolean
     */
    public function decrypt($encrypted, $key) {

        if(!empty($key)) $this->key = $key;

        $encrypted = str_replace(" ","+",$encrypted);
        $encrypted = base64_decode ( $encrypted );
        $key = str_pad ( $this->key, 24, '0' );
        $td = mcrypt_module_open ( MCRYPT_3DES, '', 'ecb', '' );
        $iv = @mcrypt_create_iv ( mcrypt_enc_get_iv_size ( $td ), MCRYPT_RAND );
        $ks = mcrypt_enc_get_key_size ( $td );
        @mcrypt_generic_init ( $td, $key, $iv );
        $decrypted = mdecrypt_generic ( $td, $encrypted );
        mcrypt_generic_deinit ( $td );
        mcrypt_module_close ( $td );
        $y = $this->pkcs5_unpad ( $decrypted );
        return $y;
    }
    private function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
    private function pkcs5_unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text )) {
            return false;
        }
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad) {
            return false;
        }
        return substr ( $text, 0, - 1 * $pad );
    }
}
<?php
/**
 * tpAdmin [a web admin based ThinkPHP5]
 *
 * @author    yuan1994 <tianpian0805@gmail.com>
 * @link      http://tpadmin.yuan1994.com/
 * @copyright 2016 yuan1994 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

use \think\Request;

$basename = Request::instance()->root();
if (pathinfo($basename, PATHINFO_EXTENSION) == 'php') {
    $basename = dirname($basename);
}


return [
    // 模板参数替换
    'view_replace_str' => [
        '__ROOT__'   => $basename,
        '__STATIC__' => $basename . '/public/static/admin',
        '__LIB__'    => $basename . '/public/static/admin/lib',
    ],

    // traits 目录
    'traits_path'      => APP_PATH . 'admin' . DS . 'traits' . DS,

    // 异常处理 handle 类 留空使用 \think\exception\Handle
    'exception_handle' => '\\TpException',

    'template' => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'            => 'Think',
        // 模板路径
        'view_path'       => '',
        // 模板后缀
        'view_suffix'     => '.html',
        // 预先加载的标签库
        'taglib_pre_load' => 'app\\admin\\taglib\\Tp',
        // 默认主题
        'default_theme'   => '',
    ],
    "admin_ways_type"=>[
        "jsdx"=>"江苏连云开港电信微厅",
        "bst"=>"贵州百视通电视",
        "gzwifi"=>"贵州wifi弹窗",
        "yg"=>"翼Go",
        "bj"=>"北京分销渠道",
        "ynwh"=>"云南工投外呼",
        "jsgzh"=>"江苏号百微信公众号",
        "jswifi"=>"江苏号百wifi",
        "schj_jt"=>"四川红茄地推",
        "scqy_jt"=>"四川钱院线上",
        "wskj"=>"志森科技",
         "zfxm"=>"周锋新疆通讯联盟"
    ],
    "admin_prize_type"=>[
        "packetcash"=>"现金红包",
        "rechargefee"=>"话费"
    ],
    "admin_bank_type"=>[
        "jiaotongyinhang"=>"交通银行",
        "zhongxinyinhang"=>"中信银行",
        "xingyeyinhang"=>"兴业银行",
        "pinganyinhang"=>"平安银行",
        "pufayinhang"=>"浦发银行",
        "guangdayinhang"=>"光大银行",
        "minshengyinhang"=>"民生银行"
    ]
];

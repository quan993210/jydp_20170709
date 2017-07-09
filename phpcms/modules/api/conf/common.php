<?php

/** 通用参数 */
//define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']));
define('APP_PATH', dirname(dirname(__FILE__)));
define('CLASS_EXT', '.class.php');
define('LIB_PATH', APP_PATH.'pay/lib/');

//var_dump(dirname(APP_PATH));



/** 支付参数 */

// 微信支付参数
define('APPID', 'wx845515db72c790d0');
define('MCHID', '1232112202');
define('APP_KEY', 'c8ae91205e46d5ee301c8c88aa139cab');
/** 微信退款证书 */
define('SSLCERT_PATH', APP_PATH.'/key/cert-2/apiclient_cert.pem');
define('SSLKEY_PATH', APP_PATH.'/key/cert-2/apiclient_key.pem');
define('WE_NOTIFY_URL', 'https://api.yzhcb.com/order/wx_notify/');

// 支付宝支付参数
define('APP_ID', '2017060507424556');
//define('APP_ID', '2016080600183694');//沙箱环境
define('PID', '2088521106670046');
define('PUBLIC_KEY', '/key/alipay_public_key.pem');
define('PRIVATE_KEY', '/key/app_private_key.pem');
define('ALIPAY_PUBLIC_KEY', '/key/alipay_public_key.pem');
define('ALI_NOTIFY_URL', 'https://api.yzhcb.com/order/ali_notify/');
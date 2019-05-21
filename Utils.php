<?php
/**
 * Created by PhpStorm.
 * User: SuperJu
 * Date: 2017/12/28
 * Time: ����2:02
 */

namespace App\Utils;

use Hashids\Hashids;
use phpDocumentor\Reflection\Types\Self_;

class Utils
{

    public static function isMobile($string)
    {
        return preg_match("/^1[0-9]{2}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/", $string);
    }

    /**
     * @param        $url
     * @param string $method
     * @param bool   $data
     * @param bool   $headers
     * @param bool   $returnInfo
     * @param bool   $auth
     *
     * @return array|mixed
     * @requestGetExample $data = Utils::curl("https://api.ipify.org");
     * @requestPostExample
     * $CurlPOST = Utils::curl("http://jsonplaceholder.typicode.com/posts", $method = "POST", $data = array(
     * "title"  => 'foo',
     * "body"   => 'bar',
     * "userId" => 1,
     * ));
     * @authCurlExample
     * $curlBasicAuth = Utils::curl(
     * "http://jsonplaceholder.typicode.com/posts",
     * $method = "GET",
     * $data = false,
     * $header = false,
     * $returnInfo = false,
     * $auth = array(
     * 'username' => 'your_login',
     * 'password' => 'your_password',
     * )
     * );
     * @CustomHeaders:
     *  $curlWithHeaders = Recipe::curl("http://jsonplaceholder.typicode.com/posts", $method = "GET", $data = false,
     *     $header = array(
     * "Accept" => "application/json",
     * ), $returnInfo = true);
     */
    public static function curl($url,
        $method = 'GET',
        $data = FALSE,
        $headers = FALSE,
        $returnInfo = FALSE,
        $auth = FALSE
    ) {
        $ch = curl_init();
        $info = NULL;
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            if ($data !== FALSE) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } else {
            if ($data !== FALSE) {
                if (is_array($data)) {
                    $dataTokens = [];
                    foreach ($data as $key => $value) {
                        array_push($dataTokens, urlencode($key) . '=' . urlencode($value));
                    }
                    $data = implode('&', $dataTokens);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $data);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($headers !== FALSE) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($auth !== FALSE && strlen($auth['username']) > 0 && strlen($auth['password']) > 0) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth['username'] . ':' . $auth['password']);
        }
        $contents = curl_exec($ch);
        if ($returnInfo) {
            $info = curl_getinfo($ch);
        }
        curl_close($ch);
        if ($returnInfo) {
            return ['contents' => $contents, 'info' => $info];
        }

        return $contents;
    }

    /*
        *	规范化 URL
        *	判断是否使用 HTTPS 链接，当是 HTTPS 访问时候自动添加
        *	自动添加链接前面的 http://
        *	$slash 是判断是否要后面添加斜杠
        */
    public static function format_url($url, $slash = FALSE)
    {

        if (substr($url, 0, 4) != 'http') {
            @$if_https = $_SERVER['HTTPS'];    //这样就不会有错误提示
            if ($if_https) {    //如果是使用 https 访问的话就添加 https
                $url = 'https:' . $url;
            } else {
                $url = 'http:' . $url;
            }
        }
        if ($slash) {
            $url = rtrim($url, '/') . '/';
        }

        return $url;
    }

    /**
     * 去掉所有的空格
     *
     * @param $_str
     *
     * @return string|string[]|null
     */
    public static function trim($_str)
    {
        return $_str = preg_replace("/\s/", "", $_str);

    }

    /**
     * @param        $phone
     * @param        $msg
     * @param bool   $report
     * @param int    $send_time
     * @param string $uid
     *
     * {
     * "account" : "N6000001", //用户在253云通讯平台上申请的API账号
     * "password" : "123456", //用户在253云通讯平台上申请的API账号对应的API密钥
     * "msg" : "【253】您的验证码是：2530", //短信内容。长度不能超过536个字符
     * "phone" : "15800000000", //手机号码。多个手机号码使用英文逗号分隔
     * "sendtime" : "201704101400", //定时发送短信时间。格式为yyyyMMddHHmm，值小于或等于当前时间则立即发送，不填则默认为立即发送（选填参数）
     * "report" : "true", //是否需要状态报告（默认为false）（选填参数）
     * "extend" : "555", //用户自定义扩展码，纯数字，建议1-3位（选填参数）
     * "uid" : "批次编号-场景名（英文或者拼音）" //自助通系统内使用UID判断短信使用的场景类型，可重复使用，可自定义场景名称，示例如 VerificationCode（选填参数）
     * }
     */
    public static function sendSms253($phone, $msg, $report = TRUE, $send_time = 0, $uid = 'VerificationCode')
    {
        $url = 'http://smssh1.253.com/msg/send/json';
        $account = env('SMS_ACCOUNT_253');
        $password = env('SMS_PASSWORD_253');
        $data = [
            'account' => $account,
            'password' => $password,
            'phone' => $phone,
            'msg' => '【师大教科文】' . $msg,
            'report' => $report,
            'sendtime' => $send_time,
            'uid' => $uid,
        ];
        info('sms_data', $data);

        $res = self::curl($url,'POST',$data);
        if ($res['code'] === 0) {
            return TRUE;
        }

        info('sms_error:' . json_encode($res));

        return FALSE;
    }

}

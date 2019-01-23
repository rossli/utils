<?php
/**
 * Created by PhpStorm.
 * User: SuperJu
 * Date: 2017/12/28
 * Time: ����2:02
 */

namespace App\Utils;

use Hashids\Hashids;

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
    "title"  => 'foo',
    "body"   => 'bar',
    "userId" => 1,
    ));
     * @authCurlExample
     * $curlBasicAuth = Utils::curl(
    "http://jsonplaceholder.typicode.com/posts",
    $method = "GET",
    $data = false,
    $header = false,
    $returnInfo = false,
    $auth = array(
    'username' => 'your_login',
    'password' => 'your_password',
    )
    );
     * @CustomHeaders:
     *  $curlWithHeaders = Recipe::curl("http://jsonplaceholder.typicode.com/posts", $method = "GET", $data = false, $header = array(
    "Accept" => "application/json",
    ), $returnInfo = true);
     */
    public static function curl($url, $method = 'GET', $data = false, $headers = false, $returnInfo = false, $auth = false)
    {
        $ch = curl_init();
        $info = null;
        if (strtoupper($method) == 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== false) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } else {
            if ($data !== false) {
                if (is_array($data)) {
                    $dataTokens = [];
                    foreach ($data as $key => $value) {
                        array_push($dataTokens, urlencode($key).'='.urlencode($value));
                    }
                    $data = implode('&', $dataTokens);
                }
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$data);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($headers !== false) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($auth !== false && strlen($auth['username']) > 0 && strlen($auth['password']) > 0) {
            curl_setopt($ch, CURLOPT_USERPWD, $auth['username'].':'.$auth['password']);
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
    public static function format_url($url,$slash=false)
    {

        if (substr($url,0,4)!='http') {
            @$if_https = $_SERVER['HTTPS'];	//这样就不会有错误提示
            if ($if_https) {	//如果是使用 https 访问的话就添加 https
                $url='https:'.$url;
            } else {
                $url='http:'.$url;
            }
        }
        if ($slash) {
            $url = rtrim($url,'/').'/';
        }
        return $url;
    }



}
<?php
/**
 * Created by PhpStorm.
 * User: SuperJu
 * Date: 2017/12/28
 * Time: 下午2:02
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

    /**
     * 设置中奖概率
     * @param $value  中奖概率
     *
     * @return bool
     */
    public static function lottery($value)
    {
        $rand = rand(0,100);
        if ($rand < $value) {
            return true;
        }
        return false;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: SuperJu
 * Date: 2017/12/28
 * Time: ����2:02
 */

namespace App\Utils;

class Utils
{

    public static function isMobile($string)
    {
        return preg_match("/^1[0-9]{2}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/", $string);
    }

    //验证身份证-强度高
    public static function checkIdCard($idcard)
    {

        // 只能是18位
        if (strlen($idcard) != 18) {
            return FALSE;
        }

        // 取出本体码
        $idcard_base = substr($idcard, 0, 17);

        // 取出校验码
        $verify_code = substr($idcard, 17, 1);

        // 加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

        // 校验码对应值
        $verify_code_list = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idcard_base, $i, 1) * $factor[ $i ];
        }

        // 取模
        $mod = $total % 11;

        // 比较校验码
        if ($verify_code == $verify_code_list[ $mod ]) {
            return TRUE;
        } else {
            return FALSE;
        }
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
    public function trim($_str)
    {
        return $_str = preg_replace("/\s/", "", $_str);

    }

    public static function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

}
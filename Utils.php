<?php
/**
 * Created by PhpStorm.
 * User: SuperJu
 * Date: 2017/12/28
 * Time: ����2:02
 */

namespace App\Utils;

use Hashids\Hashids;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use phpDocumentor\Reflection\Types\Self_;

class Utils
{

    /**
     * 手机号码验证 (非严格)
     * 更新时间 2019-03-06
     *
     * @param $string
     *
     * @return false|int
     */
    public static function isMobile($string)
    {
        return preg_match("/^1[3|4|5|6|7|8|9][0-9]{9}$/", $string);
    }

    public static function isRealMobile($string)
    {
        return preg_match("/^[1](([3][0-9])|([4][5-9])|([5][0-3,5-9])|([6][5,6])|([7][0-8])|([8][0-9])|([9][1,8,9]))[0-9]{8}$/",
            $string);
    }

    //验证身份证-强度高
    public static function checkIdCard($idcard)
    {
        $idcard = strtoupper($idcard);
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
     * 'Accept: application/json',
     * 'content_type: application/json',
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
    public static function sendSms253($phone, $msg, $report = TRUE, $send_time = '', $uid = 'VerificationCode')
    {
        $url = 'http://smssh1.253.com/msg/send/json';
        $account = env('SMS_ACCOUNT_253');
        $password = env('SMS_PASSWORD_253');
        $data = [
            'account'  => $account,
            'password' => $password,
            'phone'    => $phone,
            'msg'      => '【师大教科文】' . $msg,
            'report'   => $report,
            'sendtime' => $send_time,
            'uid'      => $uid,
        ];
        //开发环境不发短信
        if (env('APP_DEBUG')) {
            info('sms_data:DEBUG');

            return TRUE;
        }
        info('sms_data:', $data);

        $res = self::curl($url, 'POST', json_encode($data), [
            'Content-Type: application/json; charset=utf-8',
        ]);
        $res = json_decode($res, 1);
        info('sms_send_res:' . json_encode($res));
        if ($res['code'] == 0) {
            info('sms_send_ok');

            return TRUE;
        }

        return FALSE;
    }

    public static function randFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    public static function convertName($name)
    {
        $len = mb_strlen($name);
        if ($len === 2) {
            return mb_substr($name, 0, 1) . '*';
        }
        if ($len === 3) {
            return mb_substr($name, 0, 1) . '*' . mb_substr($name, 2, 1);
        }

        return $name;
    }

    /**
     * 将一个字符串部分字符用$re替代隐藏
     *
     * @param string $string 待处理的字符串
     * @param int    $start 规定在字符串的何处开始，
     *                            正数 - 在字符串的指定位置开始
     *                            负数 - 在从字符串结尾的指定位置开始
     *                            0 - 在字符串中的第一个字符处开始
     * @param int    $length 可选。规定要隐藏的字符串长度。默认是直到字符串的结尾。
     *                            正数 - 从 start 参数所在的位置隐藏
     *                            负数 - 从字符串末端隐藏
     * @param string $re 替代符
     *
     * @return string   处理后的字符串
     */
    public static function hidestr($string, $start = 0, $length = 0, $re = '*')
    {
        if (empty($string)) {
            return FALSE;
        }
        $strarr = [];
        $mb_strlen = mb_strlen($string);
        while ($mb_strlen) {//循环把字符串变为数组
            $strarr[] = mb_substr($string, 0, 1, 'utf8');
            $string = mb_substr($string, 1, $mb_strlen, 'utf8');
            $mb_strlen = mb_strlen($string);
        }
        $strlen = count($strarr);
        $begin = $start >= 0 ? $start : ($strlen - abs($start));
        $end = $last = $strlen - 1;
        if ($length > 0) {
            $end = $begin + $length - 1;
        } elseif ($length < 0) {
            $end -= abs($length);
        }
        for ($i = $begin; $i <= $end; $i++) {
            $strarr[ $i ] = $re;
        }
        if ($begin >= $end || $begin >= $last || $end > $last) {
            return FALSE;
        }

        return implode('', $strarr);
    }

    public static function parseUrl($url, $query)
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['path']) || $parsedUrl['path'] == NULL) {
            $url .= '/';
        }
        $separator = (!isset($parsedUrl['query'])) ? '?' : '&';
        $url .= $separator . $query;

        return $url;
    }

    public static function hashids_encode(int $id, $minHashLength = '', $alphabet = '')
    {
        $salt = env('HASHID_SALT', '');
        $minHashLength = $minHashLength ?: env('HASHID_MIN', 0);
        $alphabet = $alphabet ?: env('HASHID_ALPHABET',
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $hashids = new Hashids($salt, $minHashLength, $alphabet);

        return $hashids->encode($id);
    }

    /**
     * 返回 空数组 或者 [1]
     *
     * @param        $id
     * @param string $minHashLength
     * @param string $alphabet
     *
     * @return array
     */
    public static function hashids_decode($id, $minHashLength = '', $alphabet = '')
    {
        $salt = env('HASHID_SALT', '');
        $minHashLength = $minHashLength ?: env('HASHID_MIN', 0);
        $alphabet = $alphabet ?: env('HASHID_ALPHABET',
            'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $hashids = new Hashids($salt, $minHashLength, $alphabet);

        return $hashids->decode($id); // [1]
    }

    public static function code($length = 4)
    {
        $str = env('HASHID_ALPHABET', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $len = strlen($str) - 1;
        $randstr = '';
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);
            $randstr .= $str[ $num ];
        }

        return $randstr;
    }

    public static function birthday($card_id)
    {
        if (empty($card_id)) {
            return NULL;
        }
        $bir = substr($card_id, 10, 4);

        return $bir;
    }

    public static function unicodeDecode($sting)
    {
        $json = '{"str":"' . $sting . '"}';
        $arr = json_decode($json, TRUE);
        if (empty($arr)) {
            return '';
        }

        return $arr['str'];
    }

    public static function base64ToImage($base64,$filename)
    {
        preg_match("/^data:image\/(?<ext>(?:png|gif|jpg|jpeg));base64,(?<image>.+)$/", $base64, $matchings);
        $image = base64_decode($matchings['image']);
        Storage::disk('local')->put($filename, $image);
        $res_oss = Storage::disk('oss')->put($filename, Storage::disk('local')->get($filename));
        if (!$res_oss) {
            return -1;
        }
        return 200;
    }
    /**
     * 订单编号  当前时间(20190909112333)即19年9月9日11点23分33秒 + 时间戳
     * @return string
     */
    public static function makeSn($prefix = '')
    : string
    {
        return $prefix . date('YmdHis') . time();
    }

    /*
     * 上传图片到阿里云
     *
     * @param  string $path   要保存的路径
     * @param  string $file   上传的文件
     * @param  string $drive  要使用的驱动
     * @return  string url     图片完全路径
     */
    public static function uploadImage($path, $file, $drive = 'oss')
    {
        $disk = Storage::disk($drive);

        //将图片上传到OSS中，并返回图片路径信息 值如:avatar/WsH9mBklpAQUBQB4mL.jpeg
        $path = $disk->put($path, $file);

        //由于图片不在本地，所以我们应该获取图片的完整路径，
        //值如：https://test.oss-cn-hongkong.aliyuncs.com/avatar/8GdIcz1NaCZ.jpeg
        return $disk->url($path);
    }

    /**
     * 将数值金额转换为中文大写金额
     *
     * @param $amount float 金额(支持到分)
     * @param $type   int   补整类型,0:到角补整;1:到元补整
     *
     * @return mixed 中文大写金额
     */
    public static function convertAmountToCn($amount, $type = 1)
    {
        // 判断输出的金额是否为数字或数字字符串
        if (!is_numeric($amount)) {
            return '要转换的金额只能为数字!';
        }

        // 金额为0,则直接输出"零元整"
        if ($amount === 0) {
            return '人民币零元整';
        }

        // 金额不能为负数
        if ($amount < 0) {
            return '要转换的金额不能为负数!';
        }

        // 金额不能超过万亿,即12位
        if (strlen($amount) > 12) {
            return '要转换的金额不能为万亿及更高金额!';
        }

        // 预定义中文转换的数组
        $digital = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        // 预定义单位转换的数组
        $position = ['仟', '佰', '拾', '亿', '仟', '佰', '拾', '万', '仟', '佰', '拾', '元'];

        // 将金额的数值字符串拆分成数组
        $amountArr = explode('.', $amount);

        // 将整数位的数值字符串拆分成数组
        $integerArr = str_split($amountArr[0], 1);

        // 将整数部分替换成大写汉字
        $result = '人民币';
        $integerArrLength = count($integerArr);     // 整数位数组的长度
        $positionLength = count($position);         // 单位数组的长度
        $zeroCount = 0;                             // 连续为0数量
        for ($i = 0; $i < $integerArrLength; $i++) {
            // 如果数值不为0,则正常转换
            if ($integerArr[ $i ] !== 0) {
                // 如果前面数字为0需要增加一个零
                if ($zeroCount >= 1) {
                    $result .= $digital[0];
                }
                $result .= $digital[ $integerArr[ $i ] ] . $position[ $positionLength - $integerArrLength + $i ];
                $zeroCount = 0;
            } else {
                ++$zeroCount;
                // 如果数值为0, 且单位是亿,万,元这三个的时候,则直接显示单位
                if (($positionLength - $integerArrLength + $i + 1) % 4 === 0) {
                    $result .= $position[ $positionLength - $integerArrLength + $i ];
                }
            }
        }

        // 如果小数位也要转换
        if ($type === 0) {
            // 将小数位的数值字符串拆分成数组
            $decimalArr = str_split($amountArr[1], 1);
            // 将角替换成大写汉字. 如果为0,则不替换
            if ($decimalArr[0] !== 0) {
                $result .= $digital[ $decimalArr[0] ] . '角';
            }
            // 将分替换成大写汉字. 如果为0,则不替换
            if ($decimalArr[1] !== 0) {
                $result .= $digital[ $decimalArr[1] ] . '分';
            }
        } else {
            $result .= '整';
        }

        return $result;
    }

    // 截取文章的一部分内容
    public static function cutArticle($data, $str = '...', $percent = 3 / 10)
    {
        $data = strip_tags($data);//去除html标记
        $pattern = '/&[a-zA-Z]+;/';//去除特殊符号
        $data = preg_replace($pattern, '', $data);

        // 设置只加载三分之一的内容
        $cut = strlen($data) * $percent;

        $data = mb_strimwidth($data, 0, $cut, $str);

        return $data;
    }

}

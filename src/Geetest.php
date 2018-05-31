<?php

namespace Jormin\Geetest;

use GuzzleHttp\Client;

/**
 * 极验验证码
 *
 * @package Jormin\Geetest
 */
class Geetest{

    const GT_SDK_VERSION = 'php_3.0.0';

    private $response;

    public $geetestID, $geetestKey, $config;

    public static $defaultConfig = [
        'width' => '300px',
        'lang' => 'zh-cn',
        'product' => 'popup',
        'clientFailAlert' => '请完成验证码',
        'serverFailAlert' => '验证码校验失败',
    ];

    /**
     * Geetest constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $config = array_merge(self::$defaultConfig, $config);
        $this->geetestID = $config['geetestID'];
        $this->geetestKey = $config['geetestKey'];
        $this->config = $config;
    }

    /**
     * 判断极验服务器是否down机
     *
     * @param $param
     * @param int $newCaptcha
     * @return int
     */
    private function preProcess($param, $newCaptcha=1) {
        $data = array(
            'gt'=>$this->geetestID,
            'newCaptcha'=>$newCaptcha
        );
        $data = array_merge($data,$param);
        $query = http_build_query($data);
        $url = "http://api.geetest.com/register.php?" . $query;
        $client = new Client();
        $response = $client->get($url);
        $response = $response->getBody()->getContents();
        if (strlen($response) != 32) {
            $this->failbackProcess();
            return 0;
        }
        $this->successProcess($response);
        return 1;
    }

    /**
     * 成功回调
     *
     * @param $challenge
     */
    private function successProcess($challenge) {
        $challenge = md5($challenge.$this->geetestKey);
        $result = [
            'success' => 1,
            'gt' => $this->geetestID,
            'challenge' => $challenge,
            'newCaptcha' =>1
        ];
        $this->response = $result;
    }

    /**
     * 失败回调
     */
    private function failbackProcess() {
        $rnd1 = md5(rand(0, 100));
        $rnd2 = md5(rand(0, 100));
        $challenge = $rnd1 . substr($rnd2, 0, 2);
        $result = [
            'success' => 0,
            'gt' => $this->geetestID,
            'challenge' => $challenge,
            'newCaptcha' =>1
        ];
        $this->response = $result;
    }

    /**
     * 正常模式获取验证结果
     *
     * @param $challenge
     * @param $validate
     * @param $seccode
     * @param $param
     * @param int $jsonFormat
     * @return int
     */
    private function successValidate($challenge, $validate, $seccode,$param, $jsonFormat=1) {
        if (!$this->checkValidate($challenge, $validate)) {
            return 0;
        }
        $query = array(
            "seccode" => $seccode,
            "timestamp" => time(),
            "challenge" => $challenge,
            "captchaid" => $this->geetestID,
            "jsonFormat" => $jsonFormat,
            "sdk" => self::GT_SDK_VERSION
        );
        $query = array_merge($query,$param);
        $url = "http://api.geetest.com/validate.php";
        $client = new Client();
        $response = $client->post($url, [
            'body' => http_build_query($query)
        ]);
        $response = $response->getBody()->getContents();
        $obj = json_decode($response,true);
        if ($obj === false){
            return 0;
        }
        if ($obj['seccode'] == md5($seccode)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 宕机模式获取验证结果
     *
     * @param $challenge
     * @param $validate
     * @return int
     */
    private function failValidate($challenge, $validate) {
        if(md5($challenge) == $validate){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * 校验
     *
     * @param $challenge
     * @param $validate
     * @return bool
     */
    private function checkValidate($challenge, $validate) {
        if (strlen($validate) != 32) {
            return false;
        }
        if (md5($this->geetestKey . 'geetest' . $challenge) != $validate) {
            return false;
        }
        return true;
    }

    /**
     * 获取客户端IP
     *
     * @return array|false|string
     */
    private function getIp(){
        if(isset($_SERVER)){
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }else{
                $realip = $_SERVER['REMOTE_ADDR'];
            }
        }else{
            if(getenv("HTTP_X_FORWARDED_FOR")){
                $realip = getenv( "HTTP_X_FORWARDED_FOR");
            }elseif(getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            }else{
                $realip = getenv("REMOTE_ADDR");
            }
        }

        return $realip;
    }

    /**
     * 获取验证码配置
     *
     * @param string $userID
     * @param string $clientType
     * @return string
     */
    public function captcha($userID = 'test', $clientType = 'web'){
        !isset($_SESSION) && session_start();
        $data = array(
            "user_id" => $userID,
            "client_type" => $clientType,
            "ip_address" => $this->getIp()
        );
        $status = $this->preProcess($data, 1);
        $_SESSION['gtServer'] = $status;
        $_SESSION['gtUserID'] = $data['user_id'];
        return json_encode($this->response);
    }

    /**
     * 服务端校验
     */
    public function validate()
    {
        $geetestChallenge = $_POST['geetest_challenge'];
        $geetestValidate = $_POST['geetest_validate'];
        $geetestSeccode = $_POST['geetest_seccode'];
        if ($_SESSION['gtServer'] == 1) {
            if ($this->successValidate($geetestChallenge, $geetestValidate, $geetestSeccode, ['user_id' => $_SESSION['gtUserID']])) {
                return true;
            }
            return false;
        } else {
            if ($this->failValidate($geetestChallenge, $geetestValidate)) {
                return true;
            }
            return false;
        }
    }

    /**
     * 模板内容
     */
    public function view(){
        extract($this->config);
        include 'view.php';
    }
}
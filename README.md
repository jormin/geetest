极验验证码 v3.0 扩展包

## 安装

``` bash
$ composer require jormin/geetest -vvv
```

## 使用

1. 生成极验验证码对象

``` php
// $config 参数见下方[配置项]
$geetest = new \Jormin\Geetest\Geetest($config);
```

2. 在模板中需要使用验证码的地方增加下述代码渲染

``` php
<?= $geetest->view(); ?>
```
3. 在 `captchaUrl` 路由指定的操作中,获取验证码参数

```php
echo $geetest->captcha();
```
4. 随表单提交时,服务端校验验证码

```php
// 校验记过为 true 或 false
$geetest->validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode']);
```

## 配置项

| 配置项  | 说明  | 选项  | 默认值  |
| ------------ | ------------ | ------------ | ------------ |
| width | 按钮宽度  | 单位可以是 px, %, em, rem, pt  | 300px|
| lang | 语言，极验验证码免费版不支持多国语言  | zh-cn, en, zh-tw, ja, ko, th  | zh-cn  |
| product  | 验证码展示方式  | popup, float  | popup  |
| geetestID  | 极验验证码ID  |   |   |
| geetestKey  | 极验验证码KEY  |   |   |
| clientFailAlert  | 客户端失败提示语  |   | 请完成验证码  |
| serverFailAlert  | 服务端失败提示语  |   | 验证码校验失败  |
| captchaUrl  | 获取验证码初始化参数路由  |   |   |

## 参考图

![](https://qiniu.blog.lerzen.com/c7086810-2a14-11e7-a419-ed2a045e33b4.jpg)

## 参考项目

1. [Jormin/LaravelGeetest](https://github.com/jormin/laravel-geetest)

2. [GeeTeam/gt3-php-sdk](https://github.com/GeeTeam/gt3-php-sdk)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

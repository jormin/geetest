<div id="embed-captcha"></div>
<p id="wait" class="show">正在加载验证码......</p>
<p id="notice" class="hide"><?= $config['clientFailAlert'] ?></p>
<script>
    var handlerEmbed = function (captchaObj) {
        $("#embed-submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                $("#notice")[0].className = "show";
                setTimeout(function () {
                    $("#notice")[0].className = "hide";
                }, 2000);
                e.preventDefault();
            }
        });
        captchaObj.appendTo("#embed-captcha");
        captchaObj.onReady(function () {
            $("#wait")[0].className = "hide";
        });
    };
    $.ajax({
        url: "<?= $config['captchaUrl'] ?>", // 加随机数防止缓存
        type: "get",
        dataType: "json",
        success: function (data) {
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                lang: '<?= $config['lang'] ?>',
                width: '<?= $config['width'] ?>',
                product: '<?= $config['product'] ?>',
                offline: !data.success
            }, handlerEmbed);
        }
    });
</script>
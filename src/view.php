<div id="embed-captcha"></div>
<p id="wait">正在加载验证码......</p>
<p id="notice" style="display: none;"><?= $clientFailAlert ?></p>
<script>
    var handlerEmbed = function (captchaObj) {
        $("#embed-submit").click(function (e) {
            var validate = captchaObj.getValidate();
            if (!validate) {
                $("#notice").show();
                setTimeout(function () {
                    $("#notice").hide();
                }, 2000);
                e.preventDefault();
            }
        });
        captchaObj.appendTo("#embed-captcha");
        captchaObj.onReady(function () {
            $("#wait").hide();
        });
    };
    $.ajax({
        url: "<?= $captchaUrl ?>", // 加随机数防止缓存
        type: "get",
        dataType: "json",
        success: function (data) {
            initGeetest({
                gt: data.gt,
                challenge: data.challenge,
                new_captcha: data.new_captcha,
                lang: '<?= $lang ?>',
                width: '<?= $width ?>',
                product: '<?= $product ?>',
                offline: !data.success
            }, handlerEmbed);
        }
    });
</script>
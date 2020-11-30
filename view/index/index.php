<div>
    正在跳转授权：{:api_url('/wechat/index/oauth',[],'')}/appid/{$appid}
</div>
<script>
    location.href = "{:api_url('/wechat/index/oauth',[],'')}/appid/{$appid}?redirect_url=https%3A%2F%2Fbaidu.com"
</script>

<script>
  window.fbAsyncInit = function() {
    FB.init({
            appId            : '<?php echo $_appId; ?>',
            autoLogAppEvents : true,
            xfbml            : true,
            version          : '<?php echo $_version; ?>',
        });

        FB_Initialized = true; //init flag
    };

    FB_OnInit = function(callback) {
        if(!window.FB_Initialized)
            setTimeout(function() { FB_OnInit(callback); }, 50);
        else {
            if(callback)
                callback();
        }
    }
</script>
<script async defer src="https://connect.facebook.net/<?php echo $_langCode; ?>/sdk.js"></script>
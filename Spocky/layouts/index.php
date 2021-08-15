<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="<?php echo $_images['favicon']; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
        <link rel="apple-touch-icon" href="<?php echo $_images['appleTouchIcon']; ?>" />
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600&amp;subset=latin-ext" rel="stylesheet">

        <?php $eHolders->postHead; ?>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php $eHolders->init; ?>

        <div id="site"></div>
        <script>
            window.addEventListener('load', function() {
                jsLibs.require('<?php echo $_sitePackage; ?>').init(<?php echo EDEBUG ? 'true' : 'false'; ?>);
            });
        </script>

        <?php $eHolders->debug; ?>
    </body>
</html>

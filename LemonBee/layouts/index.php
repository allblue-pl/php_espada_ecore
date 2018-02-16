<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="<?php echo $_images['favicon']; ?>" rel="shortcut icon" type="image/vnd.microsoft.icon" />
        <link rel="apple-touch-icon" href="<?php echo $_images['appleTouchIcon']; ?>" />
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600&amp;subset=latin-ext" rel="stylesheet">

        <?php $eHolders->header; ?>
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <?php $eHolders->init; ?>

        <!-- Main panel -->
        <?php if ($_layout === 'panel'): ?>
            <!-- <div class="mg-fill-screen lb-background-main"></div>  -->
          	<div class="lb-content <?php echo $_panelClass; ?>">

          		<!-- side menu background -->
          		<div class="col-lg-2 col-sm-3 mg-absolute lb-bg-white" style="top:0;bottom:0;left:0;right:0;"></div>

          		<div class="mg-relative lb-topbar">
	          		<!-- logo -->
	          		<div class="col-lg-2 col-sm-3 mg-bg-white">
	          			<div class="lb-logo">
							<a href="<?php echo E\Uri::Base(); ?>">
		                        <img src="<?php echo E\Uri::File('LemonBee:images/logo_main.png'); ?>" alt="logo" />
		                    </a>
	                    </div>
	          		</div>
	          		<!-- user info -->
					<div class="col-lg-10 col-sm-9 bg-primary">
	          			<?php $eHolders->userInfo; ?>
	          		</div>
	          		<div class="mg-clear"></div>
          		</div>
          		<!-- side menu -->
				<div class="col-lg-2 col-sm-3 mg-no-padding-horizontal">
          			<?php $eHolders->topMenu; ?>
          		</div>

          		<!-- main content -->
				<div class="col-lg-10 col-sm-9 lb-bg-gray-lightest" style="min-height: 800px;">
          			<?php $eHolders->content; ?>
					<div class="backToTop mg-spacer-top">
                        <hr />
						<a id="backToTop" class="spScrollToTop lb-back-to-top btn btn-default" href="">
							<?php echo EC\HText::_('LemonBee:sys.texts_backToTop'); ?>
                                <i class="fa fa-chevron-up"></i>
						</a>
					</div>
	          	</div>
	          	<div class="mg-clear"></div>
              	<div class="lb-povered-by pull-right bg-primary">
              		Powered by <a href="http://allblue.pl">AllBlue</a>
              	</div>
            </div>

		<!-- Log in panel -->
        <?php elseif ($_layout === 'logIn'): ?>
            <div class="mg-fill-screen lb-background-login"></div>
            <div class="magda-holder">
	            <div class="col-lg-5 col-sm-6 col-sm-8 lb-login">
	            	<div class="lb-login-content">
	            		<div class="lb-login-logo col-sm-5 col-sm-5 ">
	            			<img src="<?php echo E\Uri::File('LemonBee:images/logo.png'); ?>" />
                            <div class="mg-clear"></div>
                        </div>
                        <div class="lb-login-form col-sm-7 col-sm-7">
                            <h3>
                                <?php echo EC\HText::_('LemonBee:sys.texts_logInMessage'); ?>
                            </h3>
                            <?php $eHolders->content; ?>
                        </div>
                        <div class="mg-clear"></div>
	            	</div>
	            	<div class="lb-povered-by">
	            		Powered by <a href="https://allblue.pl" class="text-primary">AllBlue</a>
	            	</div>
	            </div>
            </div>
        <?php else: ?>
            Unknown layout.
        <?php endif; ?>

        <script type="text/javascript">
            $('#backToTop').click(function(evt) {
                $('html,body').animate({ scrollTop: 0 }, 'fast');
                return false;
            });
        </script>

        <?php $eHolders->debug; ?>
    </body>
</html>

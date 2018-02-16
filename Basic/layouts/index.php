<!DOCTYPE html>
<html lang="<?php echo $_lang; ?>" dir="ltr" >
    <head>
        <meta charset="utf-8">

        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?php $eHolders->header; ?>
    </head>
    <body>
        <?php $eHolders->init; ?>

        <?php $eHolders->test; ?>

		<div class="container">
			<?php $eHolders->content; ?>
		</div>

        <?php $eHolders->debug; ?>
    </body>
</html>

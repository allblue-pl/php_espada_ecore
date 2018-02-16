
<div class="lb-menu-top mg-bg-white">
	<div class="navbar-wrapper">
		<div class="navbar navbar-inverse" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<div class="navbar-collapse collapse mg-no-padding-horizontal">
				<ul class="nav nav-pills nav-stacked">
				<?php foreach ($_menuItems as $item): ?>
				  <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
					<a href="<?php echo $item['uri']; ?>">
						<img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" />
			            <?php echo $item['title']; ?>&nbsp;
			        </a>
				  </li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>
<div class="mg-clear"></div>

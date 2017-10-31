<?php get_header(); ?>
<?php
if (!function_exists('page_content')) {
    function page_content() {
        global $post;
        
        ?>
		<div class="row">
			<div class="col-md-3">
				<div class="dropdown">
					<button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Actions <i class="fa fa-caret-down"></i></button>
					<ul class="dropdown-menu">
						<?php if ($post->post_name == 'read') { ?>
						<li><a href="?delete=<?php echo $_GET['email']; ?>">Delete</a></li>
						<?php } else { ?>
						<li><a href="#" data-email-action="mark-read">Mark as Read</a></li>
						<li><a href="#" data-email-action="delete">Delete</a></li>
						<?php } ?>
					</ul>
				</div>
			</div>
			<div class="col-md-4 col-md-offset-5">
				<form action="" method="POST" id="emailSearchForm">
					<div class="input-group">
						<input type="text" class="form-control" name="search" placeholder="Search e-mail" value="<?php if (isset($_POST['search'])) echo esc_html($_POST['search']); ?>">
						<a class="input-group-addon" href="javascript:search();"><i class="fa fa-search"></i></a>
					</div>
				</form>
			</div>
			<div class="clearfix"></div>
		</div>
		<?php email_content();
    }
}

if (isset($_GET['delete'])) {
	if (get_post_meta($_GET['delete'], '_user_' . get_current_user_id() . '_state')) {
		update_post_meta($_GET['delete'], '_user_' . get_current_user_id() . '_state', 'trash');
	}
}
?>
<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="fx-header-title">
				<h1>Your Contact</h1>
				<p>Check Below for your available contact</p>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-3">
									<?php
									if (current_user_can('administrator')) {
									?>
									<a href="<?php bloginfo('url'); ?>/my-account/inbox/compose" title="Compose" class="btn btn-danger block ">Compose Mail</a>
									<?php
									}
									?>
									<ul class="fx-inbox-nav">
										<li <?php if ($post->post_name == 'inbox') { ?> class="active" <?php } ?>><a href="<?php bloginfo('url'); ?>/my-account/inbox"><i class="fa fa-inbox"></i> Inbox <span class="label label-danger pull-right" id="unreadCount">0</span></a></li>
										<?php
										if (current_user_can('administrator')) {
										?>
										<li <?php if ($post->post_name == 'sent') { ?> class="active" <?php } ?>><a href="<?php bloginfo('url'); ?>/my-account/inbox/sent/"><i class="fa fa-envelope-o"></i> Sent</a></li>
										<?php
										}
										?>
										<li <?php if ($post->post_name == 'trash') { ?> class="active" <?php } ?>><a href="<?php bloginfo('url'); ?>/my-account/inbox/trash/"><i class=" fa fa-trash-o"></i> Trash</a></li>
									</ul>
								</div>
								<div class="col-md-9">
								    <?php page_content(); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
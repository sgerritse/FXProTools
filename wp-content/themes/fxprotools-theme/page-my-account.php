<?php 
set_query_var('acc_id', get_current_user_id());
if(isset($_POST['user_login'])){
	session_start();
	$_SESSION["sec_password"] = "^%fxpro%$#@56&";
	$_SESSION["sec_user_id"]  = get_query_var('acc_id');
	$_SESSION["sec_redir"]  = get_option('home') . $_SERVER['REQUEST_URI'];
	$_SESSION["sec_login"] = 1;
}
?>
<?php 
if( $_SERVER['REQUEST_METHOD'] === 'POST'){
	foreach($_POST as $key => $value){
		if($key == "user_email_subs" || $key == "user_sms_subs")
		{
			if($value == "on"){
				update_user_meta( get_query_var('acc_id'), $key,  "yes" );
			}
			else{
				update_user_meta( get_query_var('acc_id'), $key,  "no" );
			}
		}
		elseif($key == "user_login" || $key == 'user_email'){
			$wpdb->update($wpdb->users, array($key => $value), array('ID' => get_query_var('acc_id')));
		}
		else{
			update_user_meta( get_query_var('acc_id'), $key,  $value );
		}
	}
	//for onboard checklist
	if( !$checklist['verified_profile'] ){
		$checklist['verified_profile'] = true;
		update_user_meta( get_query_var('acc_id'), '_onboard_checklist', $checklist );
	}
	wp_redirect( home_url() . '/autologin?user_id=' . get_query_var('acc_id') );
}

get_header(); 
$checklist = get_user_checklist();
?>

<?php get_template_part('inc/templates/nav-marketing'); ?>

<div class="container woocommerce">
	<div class="row">
		<div class="col-md-12">
			<div class="fx-header-title">
				<h1>Your Contact</h1>
				<p>Check Below for your available contact</p>
			</div>
			<div class="panel panel-default fx-contact-panel">
				<div class="panel-body">
					<div class="media">
						<div class="media-left">
							<img src="<?php echo get_avatar_url(get_current_user_id()); ?>" class="media-object">
						</div>
						<div class="media-body">
							<div class="info">
								<h4 class="media-heading text-normal">
									<?php  
										if(get_the_author_meta('first_name', get_current_user_id())){
											echo get_the_author_meta('first_name', get_current_user_id()) . ' ' . get_the_author_meta('last_name', get_current_user_id());
										}else{
											echo get_the_author_meta('user_login', get_current_user_id());
										}
									?>
								</h4>
								<ul class="info-list">
									<li><i class="fa fa-envelope-o"></i> <?php echo get_the_author_meta('email', get_current_user_id()); ?></li>
									<li><i class="fa fa-mobile"></i> <?php echo get_the_author_meta('billing_phone', get_current_user_id()); ?></li>
									<li><i class="fa fa-home"></i> <?php echo get_the_author_meta('billing_city', get_current_user_id()); ?>, <?php echo get_the_author_meta('billing_state', get_current_user_id()); ?></li>
								</ul>
								<p>IP Address: 192.168.8.1</p>
							</div>
							<div class="action">
								<div>
									<i class="fa fa-inbox block"></i>
									<a href="#">Send Message</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="fx-tabs-vertical marketing-contacts">
						<ul class="nav nav-tabs">
							<li class="active"><a href="#a" data-toggle="tab">Your Information</a></li>
							<li><a href="#b" data-toggle="tab">Edit Contact</a></li>
							<li><a href="#c" data-toggle="tab">Purchases</a></li>
							<li><a href="#d" data-toggle="tab">Memberships</a></li>
							<li><a href="#e" data-toggle="tab">Genealogy</a></li>
							<li><a href="#f" data-toggle="tab">Recent Activity</a></li>
							<li><a href="#g" data-toggle="tab">Your Sponsor</a></li>
							<li><a href="<?php echo wp_logout_url('/login/'); ?>">Logout</a></li>
						</ul>
						<div class="tab-content">
							<div class="tab-pane fade in active" id="a">
								<form action="<?php echo get_the_permalink(); ?>" method="POST" class="<?php echo ( isset($_GET['action']) && $_GET['action'] == 'edit') ? 'form-edit' : ''; ?>">
									<div class="row">
										<div class="col-md-6 m-b-lg">
											<p class="text-bold text-center">General Information</p>
											<ul class="list-info list-info-fields">
												<li><span>First Name:</span> <?php echo get_the_author_meta('first_name', get_current_user_id()) ?></li>
												<li><span>Last Name:</span> <?php echo get_the_author_meta('last_name', get_current_user_id()); ?></li>
												<li><span>Website:</span> <?php echo get_the_author_meta('website', get_current_user_id()) ?></li>
												<li><span>Facebook:</span> <?php echo get_the_author_meta('facebook', get_current_user_id()); ?></li>
												<li><span>Twitter:</span> <?php echo get_the_author_meta('twitter', get_current_user_id()); ?></li>
												<li><span>Google Plus:</span> <?php echo get_the_author_meta('googleplus', get_current_user_id()); ?></li>
											</ul>
										</div>
										<div class="col-md-6 m-b-lg">
											<p class="text-bold text-center">Account Information</p>
											<ul class="list-info list-info-fields">
												<li><span>Affiliate ID:</span> <?php echo affwp_get_affiliate_id( get_current_user_id() ) ?></li>
												<li><span>Username:</span> <?php echo get_the_author_meta('user_login', get_current_user_id()) ?></li>
												<li><span>Email:</span> <?php echo get_the_author_meta('user_email', get_current_user_id()) ?></li>
												<li><span>SMS/Text Messaging:</span> <?php
													$sub = get_the_author_meta('user_sms_subs', get_current_user_id());
													
													if (strlen($sub) == 0) {
														echo 'no';
													} else {
														echo $sub;
													}
												?></li>
												<li><span>Email Updates:</span> <?php
													$sub = get_the_author_meta('user_email_subs', get_current_user_id());
													
													if (strlen($sub) == 0) {
														echo 'no';
													} else {
														echo $sub;
													}
												?></li>
											</ul>
										</div>
										<div class="clearfix"></div>
										<div class="col-md-6">
											<p class="text-bold text-center">Billing Information</p>
											<ul class="list-info list-info-fields">
												<li><span>Business Name:</span> <?php echo get_the_author_meta('billing_company', get_current_user_id()) ?></li>
												<li><span>House # & Street Name:</span> <?php echo get_the_author_meta('billing_address_1', get_current_user_id()) ?></li>
												<li><span>Apt.,suite,unit,etc.:</span> <?php echo get_the_author_meta('billing_address_2', get_current_user_id()) ?></li>
												<li><span>City:</span> <?php echo get_the_author_meta('billing_city', get_current_user_id()) ?></li>
												<li><span>State:</span> <?php echo get_the_author_meta('billing_state', get_current_user_id()) ?></li>
												<li><span>Zip Code:</span> <?php echo get_the_author_meta('billing_postcode', get_current_user_id()) ?></li>
											</ul>
										</div>
										<div class="col-md-6">
											<p class="text-bold text-center">Shipping Information</p>
											<ul class="list-info list-info-fields">
												<li><span>Business Name:</span> <?php echo get_the_author_meta('shipping_company', get_current_user_id()) ?></li>
												<li><span>House # & Street Name:</span> <?php echo get_the_author_meta('shipping_address_1', get_current_user_id()) ?></li>
												<li><span>Apt.,suite,unit,etc.:</span> <?php echo get_the_author_meta('shipping_address_2', get_current_user_id()) ?></li>
												<li><span>City:</span> <?php echo get_the_author_meta('shipping_city', get_current_user_id()) ?></li>
												<li><span>State:</span> <?php echo get_the_author_meta('shipping_state', get_current_user_id()) ?></li>
												<li><span>Zip Code:</span> <?php echo get_the_author_meta('shipping_postcode', get_current_user_id()) ?></li>
											</ul>
										</div>
									</div>
								</form>
							</div>
							<div class="tab-pane fade" id="b">
								<?php get_template_part('inc/templates/my-account/form-edit'); ?>
							</div>
							<div class="tab-pane fade" id="c">
								<?php get_template_part('inc/templates/my-account/purchases'); ?>
							</div>
							<div class="tab-pane fade" id="d">
								<?php get_template_part('inc/templates/my-account/membership'); ?>
							</div>
							<div class="tab-pane fade" id="e">
								<p class="text-bold">Genealogy Section</p>
							</div>
							<div class="tab-pane fade" id="f">
								<?php get_template_part('inc/templates/my-account/recent-activity'); ?>
							</div>
							<div class="tab-pane fade" id="g">
								<p class="text-bold">Your Sponsor</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php get_template_part('inc/templates/my-account/modal'); ?>

<div aria-hidden="true" aria-labelledby="modalComposeLabel" role="dialog" tabindex="-1" id="modalCompose" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
				<h4 class="modal-title">Compose</h4>
			</div>
			<div class="modal-body">
				<form role="form" class="form-horizontal">
					<div class="form-group">
						<label class="col-md-2 control-label">To</label>
						<div class="col-md-10">
							<input type="text" placeholder="" id="inputEmail1" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Cc / Bcc</label>
						<div class="col-md-10">
						<input type="text" placeholder="" id="cc" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Subject</label>
						<div class="col-md-10">
						<input type="text" placeholder="" id="inputPassword1" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label class="col-md-2 control-label">Message</label>
						<div class="col-md-10">
						<textarea rows="10" cols="30" class="form-control" id="" name=""></textarea>
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-12 text-right">
							<button class="btn btn-send" type="submit">Send</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>

<script type="text/javascript">
	$(document).ready(function(){
		$('.view-purchase-details').click(function(e){
			e.preventDefault();
			var html = $('#'+$(this).attr('data-target'))[0].outerHTML;
			$('#view-purchase-details .purchase-details-info').html('');
			$('#view-purchase-details .purchase-details-info').prepend(html);
			$('#table-purchases').hide();
			$('#view-purchase-details').fadeIn();
		});
		$('#close-purchase-details').click(function(e){
			e.preventDefault();
			$('#view-purchase-details').hide();
			$('#table-purchases').fadeIn();
		});
		//check username
		var textInput = document.getElementById('user_login');
		var timeout = null;
		textInput.onkeyup = function (e) {
			clearTimeout(timeout);
			$('.form-edit button[type="submit"]').attr('disabled','disabled');

			timeout = setTimeout(function () {
				var username = $('#user_login').val();
				var id = $('#user_login').attr('id');
		        $.ajax({
			        url: "<?php echo get_option('home'); ?>/wp-admin/admin-ajax.php ?>",
			        data: {
			            'action':'check_valid_username',
			            'new_username' : username
			        },
			        beforeSend: function(){
			        	$('#' + 'validation-'+ id).remove();
			        	$('#user_login').parent().after('<li id="validation-'+ id +'" class="validation-field"></li>');
			        	$('#' + 'validation-'+ id).append('<span class="alert alert-warning">Verifying your new username...</span>');
			        },
			        success:function(data) {
			            if(data == "0"){
			            	$('#' + 'validation-'+ id + ' .alert').remove();
			            	$('#' + 'validation-'+ id).append('<span class="alert alert-danger"><i class="fa fa-times"></i> username "'+ username +'" is already in use. Please enter a different Username. (You might try adding a number to the end of the name entered.)</span>');
			            	$('.form-edit button[type="submit"]').attr('disabled','disabled');
			            }else if(data == "2"){
			            	$('#' + 'validation-'+ id + ' .alert').remove();
			            	$('#' + 'validation-'+ id).append('<span class="alert alert-danger"><i class="fa fa-times"></i> Your Username must be between 3 and 30 characters long. Your Username cannot include spaces or characters other than letters, numbers, and the following punctuation: !#%&()*+,-./:; =?@[]^_`{}~.</span>');
			            	$('.form-edit button[type="submit"]').attr('disabled','disabled');
			            }else{
			            	$('#' + 'validation-'+ id + ' .alert').remove();
			            	$('#' + 'validation-'+ id).append('<span class="alert alert-success"><i class="fa fa-check"></i> username "'+ username +'" is available</span>');
			            	$('.form-edit button[type="submit"]').removeAttr('disabled');
			            }
			        },
			        error: function(errorThrown){
			            console.log(errorThrown);
			        }
			    }); 
		    }, 1000);
		};
	});
</script>

<?php 
if(isset($_GET['cancel']) && isset($_GET['order_type'])){
	if($_GET['cancel'] == "yes" && $_GET['order_type'] == "purchase"){ ?>
		<script type="text/javascript">
			$(document).ready(function(){
				$('.marketing-contacts a[href="#c"]').click();
				$('.tab-pane#c').addClass('tab-pane-cancellation');
			});
		</script>
<?php 
	} 
}
?>


<?php 
if(isset($_GET['cancel']) && isset($_GET['order_type'])){
	if($_GET['cancel'] == "yes" && $_GET['order_type'] == "membership"){ ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('.marketing-contacts a[href="#d"]').click();
		$('.tab-pane#d').addClass('tab-pane-cancellation');
	});
</script>
<?php 
	}
} 
?>

<?php if(isset($_GET['order_id'])){ ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('.marketing-contacts a[href="#c"]').click();
		$('.tab-pane#c .my_account_orders').hide();
		$('#back-to-purchases').click(function(e){
			e.preventDefault();
			$('.tab-pane#c .my_account_orders').fadeIn();
			$('.purchases-view-order').hide();
		});
	});
</script>
<?php } ?>

<?php if(isset($_GET['subs_id'])){ ?>
<script type="text/javascript">
	$(document).ready(function(){
		$('.marketing-contacts a[href="#d"]').click();
		$('.tab-pane#d .my_account_subscriptions').hide();
		$('#back-to-memberships').click(function(e){
			e.preventDefault();
			$('.tab-pane#d .my_account_subscriptions').fadeIn();
			$('.membership-view-subs').hide();
		});
	});
</script>
<?php } ?>
<?php 
/*
Template Name: Direct Upline
*/
get_header(); 
?>

	<?php get_template_part('inc/templates/nav-team'); ?>

	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<div class="fx-header-title">
					<h1>Direct Upline / Referrer</h1>
					<p>Check Below Direct Upline / Referrer</p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<?php echo do_shortcode('[affiliate_info_referred]Your referrer is [affiliate_info_name][/affiliate_info_referred]'); ?>
			</div>
		</div>
	</div>

	

<?php get_footer(); ?>

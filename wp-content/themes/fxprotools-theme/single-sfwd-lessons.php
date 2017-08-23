<?php get_header(); ?>

	<?php get_template_part('inc/templates/nav-products'); ?>

	<div class="container">
		<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body">
						<h5 class="text-bold">Course Progress</h5>
						<div class="progress">
						 	<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 50%">
								50%
						 	</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default fx-course-navigation">
					<div class="panel-body">
						<h5 class="text-bold">Course Navigation</h5>
						<ul>
							<li><a href="product-course-lesson.html">Lesson #1</a></li>
							<li><a href="product-course-lesson.html">Lesson #2</a></li>
							<li><a href="product-course-lesson.html">Lesson #3</a></li>
							<li><a href="product-course-lesson.html">Lesson #4</a></li>
							<li><a href="product-course-lesson.html">Lesson #5</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="col-md-9">
				<div class="row">
					<div class="col-md-12">
						<div class="fx-header-title">
							<h1>Advance Expert Course</h1>
							<p>Course subtitle goes here</p>
						</div>
					</div>
					<div class="col-md-12">
						<div class="fx-video-container"></div>
						<br/>
					</div>
					<div class="clearfix"></div>
					<div class="col-md-12">
						<div class="panel panel-default fx-course-outline">
							<div class="panel-body">
								<h3>Course Description</h3>
								<p>This is an example of a free course demonstrating some of the core of lorem ipsum dolor.</p>
								<p>Key Concepts Include:</p>
								<ul>
									<li>Accepted media files</li>
									<li>Example quiz questions</li>
									<li>Timer Description</li>
									<li>Badges &amp; points demonstration</li>
									<li>Available shortcodes</li>
								</ul>
								<p>Advanced functionality such as learner engagement notifications assignments, lorem ipsum dolor sit amet.</p>
							</div>
						</div>
						<a href="#" class="btn btn-danger block">Upgrade For Access - $197</a>
						<br/>
						<div class="panel panel-default fx-course-outline">
							<div class="panel-body">
								<h5 class="text-bold">Course Lessons</h5>
								<table class="table table-bordered fx-table-lessons">
									<thead>
										<tr>
											<th style="width: 100px;">Lessons</th>
											<th>Status</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="text-center number">1</td>
											<td>
												<a href="lesson-view.html">First Lesson Title</a>
												<div class="status pull-right">
													<i class="fa fa-check text-success"></i>
												</div>
											</td>
										</tr>
										<tr>
											<td class="text-center number">2</td>
											<td>
												<a href="lesson-view.html">Second Lesson Title</a>
												<div class="status pull-right">
													<i class="fa fa-check text-success"></i>
												</div>
											</td>
										</tr>
										<tr>
											<td class="text-center number">3</td>
											<td>
												<a href="lesson-view.html">Third Lesson Title</a>
												<div class="status pull-right">
													
												</div>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>	
		</div>
	</div>

	

<?php get_footer(); ?>
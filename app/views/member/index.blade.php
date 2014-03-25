@extends('layouts.default')

@section('content')

		<!-- Carousel
		================================================== -->
		<div id="top-user-container">

			<div class='container top-users'>

			<div class='lead'>
				<h2>Hawaii's Top 10 Klout Scores</h2>
			</div><!--/.lead-->

				<ul class='users list-inline'>
				<?php $c = 1; ?>
				@foreach ($users as $user)
					<li>
						<div class='box'>
							<div class="media">
								<a class="pull-left" href="#">
									<img class="media-object" src="holder.js/48x48" alt="...">
								</a>
							<div class="media-body">
								<h3 class="media-heading">#{{ $c }}</h3>
								<h4>{{ $user->name }}</h4>
								<div class="score">
									{{ round($user->klout_metric_score) }}
									<span class="callout"></span>
								</div><!--/.score-->
								<iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/follow_button.html?screen_name={{ $user->twitter_handle }}&dnt=false&show_count=false" style="width:300px; height:20px;"></iframe>
							</div><!--/.media-body-->
						</div><!--/.media-->
						</div><!--/.box-->
					</li>
					<?php $c++; ?>
				@endforeach
				</ul>
			</div><!--/.container-->
		</div><!-- /.carousel -->

		<!-- Wrap the rest of the page in another container to center all the content. -->

		<div class="container">

			<div class='well'> 
				<div class='row'>
					<div class='col-sm-6'>
						<div class='lead'>
						<h2>Hawaii's Popular Lists <small style='margin-left:20px;'>Updated daily.</small></h2>
						</div>
					</div><!--/.col-sm-4-->
					<div class='col-sm-6'>
						<a class='btn btn-lg btn-primary pull-right' style='margin-top:13px;'><i class='icon-pencil'> </i> Create your own list</a>
					</div><!--/.col-sm-6-->
				</div><!--/.row-->
			</div><!--/.well-->

			<div class='row top-lists'>

			@foreach ($types as $type)
				<div class='col-sm-3'>
					<ul class="list-group klout-list">
						<li class="list-group-item main">
							<h4 class="list-group-item-heading">{{ $type->title }}</h4>
							<p class="list-group-item-text">{{ $type->text }}</p>
						</li>
						
						<?php $c = 1; ?>
						@foreach ($type->users()->orderBy('klout_metric_score', 'desc')->take(3)->get() as $user)
						<li class="list-group-item">
							<div class="media">
								<a class="pull-left" href="#">
									<img class="media-object" src="holder.js/48x48" alt="...">
								</a>
								<div class="media-body">
									<h3 class="media-heading">
										<div class="score">{{ round($user->klout_metric_score) }}</div><!--/.score-->
										<span class='user-name'>{{ $user->name }}</span>
									</h3> 
									<div class='rank'>#{{ $c }}</div><!--/.div-->
									<iframe allowtransparency="true" frameborder="0" scrolling="no" src="//platform.twitter.com/widgets/follow_button.html?screen_name={{ $user->twitter_handle }}&dnt=false&show_count=false" style="width:300px; height:20px;"></iframe>
								</div><!--/.media-body-->
							</div><!--/.media-->
						</li><!--/.list-group-item-->
						<?php $c++; ?>
						@endforeach
						
						<li class="list-group-item last">
							<a class='btn btn-xlg btn-block' href="<?php echo url("type/{$type->slug}"); ?>"><i class='icon-zoom-in'> </i> View All {{ $type->title }}</a>
						</li><!--/.list-group-item-->
					</ul>
				</div><!--/.col-sm-3-->

			@endforeach

			<div class='row'>
				<div class='col-md-8'>
					<div class='page-header'>
						<h2>This Week's Movers and Shakers <small style='margin-left:20px;'>Who's Klout changed?</small></h2>
					</div><!--/.page-header-->

					<div class='row'>
						<div class='col-md-6'>
							<div class='lead'>
								Moved Up
							</div><!--/.lead-->

							<ul class="list-group stats-list">
								<li class="list-group-item">
									<div class="media">
										<a class="pull-left" href="#">
											<img class="media-object" src="holder.js/48x48" alt="...">
										</a>
										<div class="media-body">
											<h3 class="media-heading">
												<span class='user-name'>James Bob</span>

												<span class='stat text-success'>+4</span>
											</h3> 

										</div><!--/.media-body-->
									</div><!--/.media-->
								</li><!--/.list-group-item-->
							</ul>


						</div><!--/.col-md-6-->

						<div class='col-md-6'>
							<div class='lead'>
								Moved Down
							</div><!--/.lead-->
							
						</div><!--/.col-md-6-->
					</div><!--/.row-->

				</div><!--/.col-md-8-->

				<div class='col-md-4'>
					<div class='page-header'>
						<h2>Widget</h2>
					</div><!--/.page-header-->
				</div><!--/.col-md-4-->
			</div><!--/.row-->

		</div><!-- /.container -->

@stop
<?php
/**
 * The template for displaying lists of events
 *
 * Queries to do with events will default to this template if a more appropriate template cannot be found
 *
 * @package Event Organiser (plug-in)
 * @since 1.0.0
 */

global $woo_options;
get_header(); ?>

<?php woo_content_before(); ?>
<div id="content" class="col-full">
	<div id="main-sidebar-container">

		<!-- #main Starts -->
        <?php woo_main_before(); ?>
        <section id="main" class="full-width">

			<!-- Page header-->
			<header class="page-header">
			<h1 class="page-title">
				<?php
					if( eo_is_event_archive('day') )
						//Viewing date archive
						echo __('Contributor Days: ','eventorganiser').' '.eo_get_event_archive_date('jS F Y');
					elseif( eo_is_event_archive('month') )
						//Viewing month archive
						echo __('Contributor Days: ','eventorganiser').' '.eo_get_event_archive_date('F Y');
					elseif( eo_is_event_archive('year') )
						//Viewing year archive
						echo __('Contributor Days: ','eventorganiser').' '.eo_get_event_archive_date('Y');
					else
						_e('Contributor Days','eventorganiser');
				?>
				</h1>
			</header>

			<?php if ( have_posts() ) : ?>

				<?php echo do_shortcode( '[wpcd_event_filters]' ); ?>

				<!-- Navigate between pages-->
				<!-- In TwentyEleven theme this is done by twentyeleven_content_nav-->
				<?php
				global $wp_query;
				if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-above">
						<div class="nav-next events-nav-newer"><?php next_posts_link( __( 'Later events <span class="meta-nav">&rarr;</span>' , 'eventorganiser' ) ); ?></div>
						<div class="nav-previous events-nav-newer"><?php previous_posts_link( __( ' <span class="meta-nav">&larr;</span> Newer events', 'eventorganiser' ) ); ?></div>
					</nav><!-- #nav-above -->
				<?php endif; ?>

				<?php /* Start the Loop */ ?>

				<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">

					<h1 class="entry-title" style="display: inline;">
					<?php
						//If it has one, display the thumbnail
						the_post_thumbnail('thumbnail', array('style'=>'float:left;margin-right:20px;'));
					?>
					<a href="<?php the_permalink(); ?>"><?php the_title();?></a>
					</h1>

					<div class="event-entry-meta">

						<!-- Output the date of the occurrence-->
						<?php
							//Format date/time according to whether its an all day event.
							//Use microdata https://support.google.com/webmasters/bin/answer.py?hl=en&answer=176035
	 						if( eo_is_all_day() ){
								$format = 'd F Y';
								$microformat = 'Y-m-d';
							}else{
								$format = 'd F Y '.get_option('time_format');
								$microformat = 'c';
							}?>
							<time itemprop="startDate" datetime="<?php eo_the_start($microformat); ?>"><?php eo_the_start($format); ?></time>

						<!-- Display event meta list -->
						<?php echo eo_get_event_meta_list(); ?>

						<!-- Show Event text as 'the_excerpt' or 'the_content' -->
						<?php the_excerpt(); ?>

					</div><!-- .event-entry-meta -->

					<div style="clear:both;"></div>
					</header><!-- .entry-header -->

				</article><!-- #post-<?php the_ID(); ?> -->


	    		<?php endwhile; ?><!--The Loop ends-->

			<!-- Navigate between pages-->
			<?php
				if ( $wp_query->max_num_pages > 1 ) : ?>
					<nav id="nav-below">
						<div class="nav-next events-nav-newer"><?php next_posts_link( __( 'Later events <span class="meta-nav">&rarr;</span>' , 'eventorganiser' ) ); ?></div>
						<div class="nav-previous events-nav-newer"><?php previous_posts_link( __( ' <span class="meta-nav">&larr;</span> Newer events', 'eventorganiser' ) ); ?></div>
					</nav><!-- #nav-below -->
				<?php endif; ?>

			<?php else : ?>
				<!-- If there are no events -->
				<article id="post-0" class="post no-results not-found">
					<header class="entry-header">
						<h1 class="entry-title"><?php _e( 'Nothing Found.', 'eventorganiser' ); ?></h1>
					</header><!-- .entry-header -->

					<div class="entry-content">
						<p><?php _e( 'Apologies, but no results were found for the requested archive. ', 'eventorganiser' ); ?></p>
					</div><!-- .entry-content -->
				</article><!-- #post-0 -->

			<?php endif; ?>

        </section><!-- /#main -->
        <?php woo_main_after(); ?>

        <?php //get_sidebar(); ?>

	</div><!-- /#main-sidebar-container -->

	<?php //get_sidebar( 'alt' ); ?>

</div><!-- #content -->

<?php get_footer(); ?>

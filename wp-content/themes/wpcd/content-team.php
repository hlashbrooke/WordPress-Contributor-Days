<?php
/**
 * Resources Content Template
 *
 * @package WooFramework
 * @subpackage Template
 */

$title_before = '<h1 class="title entry-title">';
$title_after = '</h1>';

if ( ! is_single() ) {
	$title_before = $title_before . '<a href="' . get_permalink( get_the_ID() ) . '" rel="bookmark" title="' . the_title_attribute( array( 'echo' => 0 ) ) . '">';
	$title_after = '</a>' . $title_after;
}

$page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
	woo_post_inside_before();
?>
	<header>
		<?php

		$icon = get_post_meta( get_the_ID(), '_team_icon', true );
		if( $icon ) {
			$title_before .= '<i class="dashicons ' . esc_attr( $icon ) . '"></i>';
		}

		the_title( $title_before, $title_after );
		?>
	</header>

	<section class="entry">
	    <?php
	    if ( is_single() ) {

	    	the_excerpt();

	    	$url = get_post_meta( get_the_ID(), '_team_url', true );
	    	if( $url ) {
	    		echo '<a href="' . esc_url( $url ) . '" target="_blank" class="button">' . __( 'Find out more', 'wpcd' ) . '</a>';
	    	}

	    	$notes = get_post_meta( get_the_ID(), '_contribution_notes', true );

	    	if( $notes ) {
	    		echo '<h3>' . __( 'Contribution notes:', 'wpcd' ) . '</h3>';
	    		echo '<p>' . nl2br( $notes ) . '</p>';
	    	}

	    	$rss_feed = get_post_meta( get_the_ID(), '_team_rss', true );
	    	if( $rss_feed ) {

	    		echo '<h3>' . __( 'Team news:', 'wpcd' ) . '</h3>';
	    		wp_widget_rss_output( $rss_feed, array( 'items' => 1, 'show_summary' => 1, 'show_date' => 1 ) );
	    	}

	    	$args = array(
	    		'post_type' => 'resource',
	    		'post_status' => 'publish',
	    		'posts_per_page' => -1,
	    		'meta_query' => array(
	    			array(
	    				'key' => '_resource_team',
	    				'value' => get_the_ID(),
    				),
    			),
    		);

    		$resources = get_posts( $args );

    		if( ! empty( $resources ) ) {

    			$resource_list = '<h3>' . __( 'Linked resources:', 'wpcd' ) . '</h3>';
    			$resource_list .= '<ul class="resource-list">';

			    	foreach( $resources as $resource ) {
			    		$resource_list .= '<li>';

			    		$url = get_post_meta( $resource->ID, '_resource_url', true );

						if( $url ) {
							$resource_list .= '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $resource->post_title ) . '" target="_blank">';
						}

						$resource_list .= '<b>' . $resource->post_title . '</b>';

						if( $url ) {
							$resource_list .= '</a>';
						}

						$resource_list .= '<br/><span class="team-resource-content">'.$resource->post_content . '</span>';

						$media = get_children( array( 'post_parent' => $resource->ID, 'post_type' => 'attachment', 'posts_per_page' => -1, 'post_status' => 'any' ) );

				    	if( ! empty( $media ) ) {

				    		$media_list = '<br/><span class="team-resource-downloads"><b>' . __( 'Download file(s):', 'wpcd' ) . ' </b>';

							$c = 0;
				    		foreach( $media as $file ) {

				    			$filename = wp_get_attachment_url( $file->ID );
				    			$ext = strtoupper( pathinfo( $filename, PATHINFO_EXTENSION ) );
				    			$title = pathinfo( $filename, PATHINFO_BASENAME );

								if( $c > 0 ) {
									$media_list .= ' | ';
								}
								$media_list .= '<a href="' . esc_url( $filename ) . '" title="' . esc_attr( $title ) . '" target="_blank">' . esc_html( $ext ) . '</a>';

				    			++$c;
				    		}

				    		$media_list .= '</span>';

					    	$resource_list .= $media_list;
				    	}

						$resource_list .= '</li>';
					}

				$resource_list .= '</ul>';

				echo $resource_list;
			}

	    } else {
	    	the_excerpt();
	    }
	    ?>
	</section><!-- /.entry -->
<?php
	woo_post_inside_after();
?>
</article><!-- /.post -->
<?php
	woo_post_after();
?>
<?php

add_action( 'wp_enqueue_scripts', 'wpcd_enqueue_styles' );
function wpcd_enqueue_styles() {
    wp_enqueue_style( 'parent-css', get_template_directory_uri() . '/style.css' );
}

add_action( 'wp_print_scripts', 'wpcd_handle_scripts', 100 );
function wpcd_handle_scripts() {
	wp_enqueue_style( 'dashicons' );
}

add_action( 'woo_header_inside', 'wpcd_header_buttons', 11 );
function wpcd_header_buttons () {
	$html = '<div class="header-buttons">' ."\n";
		$html .= '<a class="button host" href="' . site_url() . '/host-a-contributor-day/">Host a Contributor Day</a>' ."\n";
		$html .= '<a class="button attend" href="' . site_url() . '/attend-a-contributor-day/">Attend a Contributor Day</a>' ."\n";
	$html .= '</div>' ."\n";

	echo $html;
}

function woo_logo () {
	$settings = woo_get_dynamic_values( array( 'logo' => '' ) );
	// Setup the tag to be used for the header area (`h1` on the front page and `span` on all others).
	$heading_tag = 'span';
	if ( is_home() || is_front_page() ) { $heading_tag = 'h1'; }

	// Get our website's name, description and URL. We use them several times below so lets get them once.
	$site_title = get_bloginfo( 'name' );
	$site_url = home_url( '/' );
	$site_description = get_bloginfo( 'description' );
?>
<div id="logo">
<?php
	// Website heading/logo and description text.
	$logo_url = $settings['logo'];
	if ( is_ssl() ) {
		$logo_url = str_replace( 'http://', 'https://', $logo_url );
	}

	echo '<div id="logo-image"><a href="' . esc_url( $site_url ) . '"><img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $site_title ) . '" /></a></div>' . "\n";

	echo '<' . $heading_tag . ' class="site-title"><a href="' . esc_url( $site_url ) . '">' . $site_title . '</a></' . $heading_tag . '>' . "\n";
	if ( $site_description ) { echo '<span class="site-description">' . $site_description . '</span>' . "\n"; }
?>
</div>
<?php
} // End woo_logo()

add_filter( 'woo_filter_post_meta', 'wpcd_filter_post_meta', 10, 1 );
function wpcd_filter_post_meta ( $post_info ) {

	if( is_post_type_archive( 'resource' ) || is_singular( 'resource' ) || is_tax( 'resource_cat' ) || is_tax( 'resource_type' ) ) {
		global $post;

		$post_info = _x( 'Added on', 'post datetime', 'wpcd' ) . ' [post_date]';

		// Get resource categories
		$cat_list = '';
		$cats = wp_get_post_terms( $post->ID, 'resource_cat' );
		foreach( $cats as $cat ) {
			if( $cat_list ) {
				$cat_list .= ', ';
			}
			$cat_list .= '<a href="' . get_term_link( $cat, 'resource_cat' ) . '" title="' . $cat->name . '">' . $cat->name . '</a>';
		}

		if( $cat_list ) {
			$post_info .= ' ' . __( 'in', 'wpcd' ) . ' ' . $cat_list;
		}

		$team_id = get_post_meta( $post->ID, '_resource_team', true );
    	if( $team_id ) {

    		$team = get_post( $team_id );

    		if( $team && ! is_wp_error( $team ) ) {
    			$icon = get_post_meta( $team->ID, '_team_icon', true );
    			$post_info .= ' ' . __( 'for', 'wpcd' ) . ' <a href="' . get_permalink( $team->ID ) . '">' . $team->post_title . '<i class="dashicons ' . esc_attr( $icon ) . '"></i></a>';
    		}
    	}
	}

	return $post_info;
}

add_filter( 'woo_post_more', 'wpcd_resources_post_more', 10, 1 );
function wpcd_resources_post_more ( $html ) {
	if( is_post_type_archive( 'resource' ) || is_tax( 'resource_cat' ) || is_tax( 'resource_type' ) ) {
		$html = '';
	}
	return $html;
}

add_filter( 'woo_archive_title', 'wpcd_archive_title', 10, 3 );
function wpcd_archive_title ( $title, $before, $after ) {
	global $wp_query;

	if( is_tax( 'resource_type' ) ) {
		$tax = $wp_query->get_queried_object();
		switch( $tax->slug ) {
			case 'organizers': $title = $before . __( 'Resources for Organizers', 'wpcd' ) . $after; break;
			case 'attendees': $title = $before . __( 'Resources for Attendees', 'wpcd' ) . $after; break;
		}
	}

	return $title;
}

add_action( 'wp_title_parts', 'wpcd_page_title', 10, 1 );
function wpcd_page_title ( $title_array ) {
	global $wp_query;

	if( is_tax( 'resource_type' ) ) {
		$tax = $wp_query->get_queried_object();
		switch( $tax->slug ) {
			case 'organizers': $title_array = array( __( 'Resources for Organizers', 'wpcd' ) ); break;
			case 'attendees': $title_array = array( __( 'Resources for Attendees', 'wpcd' ) ); break;
		}
	}

	return $title_array;
}

add_shortcode( 'wpcd_teams', 'wpcd_teams_shortcode' );
function wpcd_teams_shortcode ( $atts ) {

	$atts = shortcode_atts( array(
		'context' => 'host',
	), $atts, 'wpcd_teams' );

	$args = array(
		'post_type' => 'team',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC',
	);

	$teams = get_posts( $args );

	if( 0 == count( $teams ) ) {
		return;
	}

	$class = 'wpcd-teams';

	$html = '<div class="' . esc_attr( $class ) . '">';

		$class = 'teams-odd';
		foreach( $teams as $team ) {

			$icon = get_post_meta( $team->ID, '_team_icon', true );
			$url = get_permalink( $team->ID );

			$content = '';
			switch( $atts['context'] ) {
				case 'host': $content = nl2br( get_post_meta( $team->ID, '_contribution_notes', true ) ); break;
				case 'attend': $content = $team->post_excerpt; break;
			}
			if( ! $content ) {
				return;
			}

			$html .= '<article class="' . esc_attr( $class ) . '">';

				$icon_block = '<a class="team-list-icon" href="' . esc_url( $url ) . '" target="_blank"><i class="dashicons ' . esc_attr( $icon ) . '"></i></a>';

				$data_block = '<div class="team-list-data">';
					$data_block .= '<header><h3><a href="' . esc_url( $url ) . '" class="">' . $team->post_title . '</a></h3></header>';
					$data_block .= '<p>' . $content . '</p>';
				$data_block .= '</div>';

				if( 'teams-odd' == $class ) {
					$html .= $icon_block . $data_block;
				} else {
					$html .= $data_block . $icon_block;
				}

			$html .= '</article>';

			$html .= '<div class="fix"></div>';

			$html .= '<hr/>';

			if( 'teams-odd' == $class ) {
				$class = 'teams-even';
			} else {
				$class = 'teams-odd';
			}
		}

		$html .= '<div class="fix"></div>';

	$html .= '</div>';

	return $html;
}

add_shortcode( 'wpcd_add_resource', 'wpcd_add_resource_shortcode' );
function wpcd_add_resource_shortcode () {

	$html = '';

	if( isset( $_GET['upload'] ) && $_GET['upload']  ) {

        $upload_string = esc_attr( $_GET['upload'] );

        $msg = '';
        switch( $upload_string ) {
            case 'success':
                $status = 'tick';
                $msg = __( 'Thanks for adding a new resource!', 'wpcd' );
            break;
            case 'error':
                $status = 'alert';
                $msg = __( 'There was an error adding your resource - please try again.', 'wpcd' );
            break;
            case 'exists':
                $status = 'info';
                $msg = __( 'A resource with that name already exists.', 'wpcd' );
            break;
        }

        if( $msg ) {
            $html .= '<div class="woo-sc-box large rounded ' . esc_attr( $status ) . '">' . $msg . '</div>';
        }
    }

	$html .= '<div class="add-resource-form">';

		$html .= '<form name="add-resource-form" action="" method="post" enctype="multipart/form-data">';
			$html .= '<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />';

			$html .= '<label for="resource_name">' . __( 'Resource name:', 'wpcd' ) . '</label><input type="text" name="resource_name" id="resource_name" />';

			$html .= '<label for="resource_desc">' . __( 'Resource description:', 'wpcd' ) . '</label><textarea name="resource_desc" rows="4" id="resource_desc"></textarea>';

			$html .= '<label for="resource_url">' . __( 'Resource URL:', 'wpcd' ) . '</label><input type="url" name="resource_url" id="resource_url" />';

			$html .= '<label for="resource_file">' . __( 'Resource file:', 'wpcd' ) . '</label><input type="file" name="resource_file" id="resource_file" />';

			$html .= '<label for="resource_category">' . __( 'Resource category:', 'wpcd' ) . '</label>';
			$html .= '<select name="resource_category" id="resource_category">';

				$cats = get_terms( 'resource_cat', array( 'hide_empty' => false ) );
				foreach( $cats as $cat ) {

					// Select 'Other' by default
					$selected = '';
					if( 'Other' == $cat->name ) {
						$selected = 'selected="selected"';
					}

					$html .= '<option value="' . esc_attr( $cat->term_id ) . '" ' . $selected . '>' . esc_html( $cat->name ) . '</option>';
				}

			$html .= '</select>';

			$html .= '<label for="resource_team">' . __( 'Contribution team:', 'wpcd' ) . '</label>';
			$html .= '<select name="resource_team" id="resource_team">';

				$teams = get_posts( array( 'post_type' => 'team', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
				$html .= '<option value="0">' . __( 'No specific team', 'wpcd' ) . '</option>';
				foreach( $teams as $team ) {
					$html .= '<option value="' . esc_attr( $team->ID ) . '">' . esc_html( $team->post_title ) . '</option>';
				}

			$html .= '</select>';

			$html .= '<label for="resource_type">' . __( 'This resource is for:', 'wpcd' ) . '</label>';

			$types = get_terms( 'resource_type', array( 'hide_empty' => false ) );
			foreach( $types as $type ) {
				$html .= '<label class="resource_type_label" for="resource_type_' . esc_attr( $type->term_id ) . '" class="resource-type-label"><input type="checkbox" name="resourcetype[]" id="resource_type_' . esc_attr( $type->term_id ) . '" value="' . esc_attr( $type->term_id ) . '" checked="checked" /> ' . esc_html( $type->name ) . '</label>';
			}

			$html .= '<input type="submit" value="' . __( 'Submit', 'wpcd' ) . '" class="button" />';

		$html .= '</form>';
	$html .= '</div>';

	return $html;
}

add_action( 'init', 'wpcd_resource_submission', 11 );
function wpcd_resource_submission () {
	if( isset( $_POST['resource_name'] ) ) {

		// Get posted data
		$name = esc_html( $_POST['resource_name'] );
		$desc = esc_html( $_POST['resource_desc'] );
		$url = esc_url( $_POST['resource_url'] );

		$cat = 0;
		if( isset( $_POST['resource_category'] ) ) {
			$cat = intval( $_POST['resource_category'] );
		}

		$team = 0;
		if( isset( $_POST['resource_team'] ) ) {
			$team = intval( $_POST['resource_team'] );
		}

		$types = array();
		if( isset( $_POST['resourcetype'] ) ) {
			$types = array_map( 'intval', $_POST['resourcetype'] );
		}

		// Check for existing resource with the same name
		$existing_resource = get_page_by_title( $name, OBJECT, 'resource' );
		if( $existing_resource ) {
			$upload_string = 'exists';
		} else {

			// Set up post data
			$post_data = array(
				'post_type' => 'resource',
				'post_title' => $name,
				'post_content' => $desc,
				'post_status' => 'publish',
				'ping_status' => 'open',
				'comment_status' => 'open',
			);

			// Insert post
			$post_id = wp_insert_post( $post_data, false );

			// Add additional post info
			if( $post_id ) {

				$upload_string = 'success';

				// Set resource post meta
				update_post_meta( $post_id, '_resource_url', $url );
				update_post_meta( $post_id, '_resource_team', $team );

				// Set resource category
				if( $cat ) {
					wp_set_object_terms( $post_id, $cat, 'resource_cat' );
				}

				if( ! empty( $types ) ) {
					foreach( $types as $type ) {
						wp_set_object_terms( $post_id, $type, 'resource_type', true );
					}
				}

				// Upload file
				if( isset( $_FILES ) && ! empty( $_FILES ) ) {
					foreach( $_FILES as $file ) {
						if( is_array( $file ) ) {
							$attachment_id = wpcd_upload_user_file( $file, $post_id );
						}
					}
				}
			} else {
				$upload_string = 'error';
			}
		}

		$url = add_query_arg( array( 'upload' => $upload_string ), site_url( '/add-a-resource' ) );
		wp_redirect( $url );
		exit;
	}
}

function wpcd_upload_user_file( $file = array(), $post_id = 0 ) {

	// Only upload if associated with a post
	if( ! $post_id ) {
		return;
	}

	// Limit file size upload to 5MB
	if( 5000000 < $file['size'] ) {
		return;
	}

	require_once( ABSPATH . 'wp-admin/includes/admin.php' );

	$file_return = wp_handle_upload( $file, array( 'test_form' => false ) );

	if( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
		return false;
	} else {

		$filename = $file_return['file'];

		$attachment = array(
			'post_mime_type' => $file_return['type'],
			'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content' => '',
			'post_status' => 'inherit',
			'guid' => $file_return['url'],
		);

		$attachment_id = wp_insert_attachment( $attachment, $file_return['url'], $post_id );

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		if( 0 < intval( $attachment_id ) ) {
			return $attachment_id;
		}
	}

	return false;
}

add_shortcode( 'wpcd_event_filters', 'wpcd_event_filters_shortcode' );
function wpcd_event_filters_shortcode () {

	$html = '<div class="event-filters">';

		$html .= '<form name="event_filters" method="get" action="">';

			$cities = eo_get_venue_cities();
			$states = eo_get_venue_states();
			$countries = eo_get_venue_countries();

			if( ! empty( $cities ) ) {

				$selected = '';
				if( isset( $_GET['event_city'] ) ) {
					$selected = $_GET['event_city'];
				}

				$html .= '<select name="event_city">';
					$html .= '<option value="all">' . __( '-- All Cities --', 'wpcd' ) . '</option>';
					foreach( $cities as $city ) {
						$html .= '<option ' . selected( $selected, $city, false ) . ' value="' . esc_attr( $city ) . '">' . esc_html( $city ) . '</option>';
					}
				$html .= '</select>';
			}

			if( ! empty( $states ) ) {

				$selected = '';
				if( isset( $_GET['event_state'] ) ) {
					$selected = $_GET['event_state'];
				}

				$html .= '<select name="event_state">';
					$html .= '<option value="all">' . __( '-- All States --', 'wpcd' ) . '</option>';
					foreach( $states as $state ) {
						$html .= '<option ' . selected( $selected, $state, false ) . ' value="' . esc_attr( $state ) . '">' . esc_html( $state ) . '</option>';
					}
				$html .= '</select>';
			}

			if( ! empty( $countries ) ) {

				$selected = '';
				if( isset( $_GET['event_country'] ) ) {
					$selected = $_GET['event_country'];
				}

				$html .= '<select name="event_country">';
					$html .= '<option value="all">' . __( '-- All Countries --', 'wpcd' ) . '</option>';
					foreach( $countries as $country ) {
						$html .= '<option ' . selected( $selected, $country, false ) . ' value="' . esc_attr( $country ) . '">' . esc_html( $country ) . '</option>';
					}
				$html .= '</select>';
			}

			$html .= '<input type="submit" value="' . __( 'Go', 'wpcd' ) . '" class="button" />';

		$html .= '</form>';

	$html .= '</div>';

	return $html;
}

add_filter( 'eventorganiser_posterboard_query', 'wpcd_filter_events', 10, 1 );
function wpcd_filter_events ( $query ) {

	$args = array();
	$request_url = $_SERVER['HTTP_REFERER'];
	$query_string = parse_url( $request_url, PHP_URL_QUERY );
	parse_str( $query_string, $args );

	if( empty( $args ) ) {
		return $query;
	}

	$city = $args['event_city'];
	$state = $args['event_state'];
	$country = $args['event_country'];

	$venue_query = array();

	if( $city && 'all' != $city ) {
		$city_query = array(
			'key' => '_city',
			'value' => $city,
		);
		$venue_query[] = $city_query;
	}

	if( $state && 'all' != $state ) {
		$state_query = array(
			'key' => '_state',
			'value' => $state,
		);
		$venue_query[] = $state_query;
	}

	if( $country && 'all' != $country ) {
		$country_query = array(
			'key' => '_country',
			'value' => $country,
		);
		$venue_query[] = $country_query;
	}

	if( ! empty( $venue_query ) ) {
		$venue_query['relation'] = 'AND';
		$query['venue_query'] = $venue_query;
	}

	return $query;

}

add_filter( 'eventorganiser_event_map_tooltip', 'wpcd_event_map_tooltip', 10, 3 );
function wpcd_event_map_tooltip ( $html, $venue_id, $events ) {

	$html = '';

	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );

	$c = 0;
	foreach( $events as $event ){

		if( $c > 0 ) {
			$html .= '<hr/>';
		}

		$html .= sprintf(
			'<h4><a href="%s" title="%s">%s</a></h4>',
			get_permalink( $event->ID ),
			esc_attr( get_the_title( $event->ID ) ),
			get_the_title( $event->ID )
		);

		$format = eo_is_all_day( $event->ID ) ? $date_format : $date_format . ' ' . $time_format;
		$start_date = eo_get_the_start( $format, $event->ID, null, $event->occurrence_id );

		$html .= '<p><b>' . $start_date . '</b></p>';

		$html .= '<p>' . __( 'Venue:' , 'wpcd' ) . ' ' . eo_get_venue_name( $venue_id ) . '<br/><span class="map-venue-address">' . implode( ', ', array_filter( eo_get_venue_address( $venue_id ) ) ) . '</span></p>';

		++$c;

	}

	return $html;
}
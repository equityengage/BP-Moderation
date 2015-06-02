<?php
/**
 * Notification module for BP Moderation.
 *
 * @since 0.2.0
 */
class bpModNotification {
	/**
	 * Static initializer.
	 */
	public static function init() {
		// register BP moderation notification callback
		buddypress()->moderation->notification_callback = array( __CLASS__, 'format' );

		// mark notifications
		add_action( 'bp_actions', array( __CLASS__, 'mark' ) );

		// filter unread notifications by action
		if ( ! has_filter( 'bp_after_has_notifications_parse_args', 'bp_follow_filter_unread_notifications' ) ) {
			add_filter( 'bp_after_has_notifications_parse_args', array( __CLASS__, 'filter_unread_notifications' ) );
		}
	}

	/**
	 * Format notifications.
	 *
	 * @param string $action            The type of moderation item.
	 * @param int    $item_id           The flag ID.
	 * @param int    $secondary_item_id The content ID.
	 * @param int    $total_items       The total number of notifications to format.
	 * @param string $format            'string' to get a BuddyBar-compatible notification, 'array' otherwise.
	 * @return string $return Formatted notification.
	 */
	public static function format( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
		$array = array(
			'link' => '',
			'text' => ''
		);

		// load up DB class
		bpModLoader::load_class( 'bpModObjContent' );

		$bpMod =& bpModeration::get_istance();

		// use the notification callback set for each content type
		if ( ! empty( $bpMod->content_types[$action]->callbacks['format_notification'] ) && is_callable( $bpMod->content_types[$action]->callbacks['format_notification'] ) ) {
			$array = call_user_func( $bpMod->content_types[$action]->callbacks['format_notification'], $item_id, $secondary_item_id, $total_items );
		}

		// Fallback text
		if ( empty( $array['text'] ) ) {
			$plural_label = ! empty( $bpMod->content_types[$action]->plural_label ) ? $bpMod->content_types[$action]->plural_label : __( 'items', 'bp-moderation' );
			$plural_label = strtolower( $plural_label );

			// one item only
			if ( 1 == $total_items ) {
				$text = sprintf( __( 'One of your %s was flagged as inappropriate', 'bp-moderation' ), $plural_label );

			// more than one of the same item
			} else {
				$text = sprintf( __( '%d of your %s were flagged as inappropriate', 'bp-moderation' ), $total_items, $plural_label );
			}

			$array['text'] = $text;
		}

		// Fallback singular link
		if ( empty( $array['link'] ) ) {
			// one item only
			if ( 1 == $total_items ) {
				$cont = new bpModObjContent( $secondary_item_id );

				$array['link'] = $cont->item_url;
			}
		}

		// security
		$array['link'] = esc_url( $array['link'] );
		$array['text'] = strip_tags( $array['text'] );

		// all notifications greater than 1 get redirected to a user's notifications page
		if ( $total_items > 1 ) {
			$array['link'] = add_query_arg( 'action', sanitize_title( $action ), bp_get_notifications_permalink() );
		}

		if ( 'string' == $format ) {
			return apply_filters( "bp_moderation_new_{$action}_notification", '<a href="' . $array['link'] . '">' . $array['text'] . '</a>', $total_items, $array['link'], $array['text'], $item_id, $secondary_item_id );

		} else {
			return apply_filters( "bp_moderation_new_{$action}_return_notification", $array, $item_id, $secondary_item_id, $total_items );
		}
	}

	/**
	 * Let plugins run their mark notification routine.
	 */
	public static function mark() {
		$bpMod =& bpModeration::get_istance();

		// convert object to array
		$mark_actions = json_decode( json_encode( $bpMod->content_types ), true );

		// grab all 'mark_notification' callbacks
		$mark_actions = self::array_column_recursive( $mark_actions, 'mark_notification' );

		foreach ( $mark_actions as $action ) {
			if ( is_callable( $action ) ) {
				call_user_func( $action );
			}
		}
	}

	/**
	 * Filter notifications by component action.
	 *
	 * Only applicable in BuddyPress 2.1+.
	 *
	 * @since 1.3.0
	 *
	 * @param array $retval Current notification parameters.
	 * @return array
	 */
	public static function filter_unread_notifications( $retval ) {
		// make sure we're on a user's notification page
		if ( ! bp_is_user_notifications() ) {
			return $retval;
		}

		// make sure we're doing this for the main notifications loop
		if ( ! did_action( 'bp_before_member_body' ) ) {
			return $retval;
		}

		// filter notifications by action
		if ( ! empty( $_GET['action'] ) ) {
			$retval['component_action'] = sanitize_title( $_GET['action'] );

			// remove this filter to prevent any other notification loop getting filtered
			remove_filter( 'bp_after_has_notifications_parse_args', array( __CLASS__, 'filter_unread_notifications' ) );
		}

		return $retval;
	}

}
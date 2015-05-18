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
		$array = array();

		// load up DB class
		bpModLoader::load_class( 'bpModObjContent' );

		$bpMod =& bpModeration::get_istance();

		// use the notification callback set for each content type
		if ( is_callable( $bpMod->content_types[$action]->callbacks['format_notification'] ) ) {
			$array = call_user_func( $bpMod->content_types[$action]->callbacks['format_notification'], $action, $item_id, $secondary_item_id, $total_items );
		}

		// @todo Add a fallback notification instead of nothing
		if ( empty( $array ) ) {
			return;
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
	 * Recursive version of {@link array_column()}.
	 *
	 * Returns the values recursively from columns of the input array, identified
	 * by the $columnKey. Optionally, you may provide an $indexKey to index the
	 * values in the returned* array by the values from the $indexKey column in
	 * the input array.
	 *
	 * @see http://php.net/manual/en/function.array-column.php#116214
	 *
	 * @param array $input     A multi-dimensional array (record set) from which to pull
	 *                         a column of values.
	 * @param mixed $columnKey The column of values to return. This value may be the
	 *                         integer key of the column you wish to retrieve, or it
	 *                         may be the string key name for an associative array.
	 * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
	 *                         the returned array. This value may be the integer key
	 *                         of the column, or it may be the string key name.
	 *
	 * @return array
	 */
	public static function array_column_recursive( $input = NULL, $columnKey = NULL, $indexKey = NULL ) {

		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();
		if ( $argc < 2 ) {
			trigger_error( "array_column_recursive() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

			return NULL;
		}
		if ( ! is_array( $params[ 0 ] ) ) {
			// Because we call back to this function, check if call was made by self to
			// prevent debug/error output for recursiveness :)
			$callers = debug_backtrace();
			if ( $callers[ 1 ][ 'function' ] != 'array_column_recursive' ){
				trigger_error( 'array_column_recursive() expects parameter 1 to be array, ' . gettype( $params[ 0 ] ) . ' given', E_USER_WARNING );
			}

			return NULL;
		}
		if ( ! is_int( $params[ 1 ] )
			 && ! is_float( $params[ 1 ] )
			 && ! is_string( $params[ 1 ] )
			 && $params[ 1 ] !== NULL
			 && ! ( is_object( $params[ 1 ] ) && method_exists( $params[ 1 ], '__toString' ) )
		) {
			trigger_error( 'array_column_recursive(): The column key should be either a string or an integer', E_USER_WARNING );

			return FALSE;
		}
		if ( isset( $params[ 2 ] )
			 && ! is_int( $params[ 2 ] )
			 && ! is_float( $params[ 2 ] )
			 && ! is_string( $params[ 2 ] )
			 && ! ( is_object( $params[ 2 ] ) && method_exists( $params[ 2 ], '__toString' ) )
		) {
			trigger_error( 'array_column_recursive(): The index key should be either a string or an integer', E_USER_WARNING );

			return FALSE;
		}
		$paramsInput     = $params[ 0 ];
		$paramsColumnKey = ( $params[ 1 ] !== NULL ) ? (string) $params[ 1 ] : NULL;
		$paramsIndexKey  = NULL;
		if ( isset( $params[ 2 ] ) ) {
			if ( is_float( $params[ 2 ] ) || is_int( $params[ 2 ] ) ) {
				$paramsIndexKey = (int) $params[ 2 ];
			} else {
				$paramsIndexKey = (string) $params[ 2 ];
			}
		}
		$resultArray = array();
		foreach ( $paramsInput as $row ) {
			$key    = $value = NULL;
			$keySet = $valueSet = FALSE;
			if ( $paramsIndexKey !== NULL && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = TRUE;
				$key    = (string) $row[ $paramsIndexKey ];
			}
			if ( $paramsColumnKey === NULL ) {
				$valueSet = TRUE;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = TRUE;
				$value    = $row[ $paramsColumnKey ];
			}

			$possibleValue = self::array_column_recursive( $row, $paramsColumnKey, $paramsIndexKey );
			if ( $possibleValue ) {
				$resultArray = array_merge( $possibleValue, $resultArray );
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[ ] = $value;
				}
			}
		}

		return $resultArray;
	}
}
<?php
/**
 * Common post type functionality.
 *
 * @package   Cue
 * @copyright Copyright (c) 2016, AudioTheme, LLC
 * @license   GPL-2.0+
 * @link      https://audiotheme.com/
 * @since     2.0.0
 */

/**
 * Base post type class.
 *
 * @package Cue
 * @since   2.0.0
 */
abstract class Cue_PostType_AbstractPostType {
	/**
	 * Register the post type.
	 *
	 * @since 2.0.0
	 */
	public function register_post_type() {
		register_post_type( $this->post_type, $this->get_args() );
	}

	/**
	 * Retrieve post type registration arguments.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	abstract protected function get_args();

	/**
	 * Retrieve post updated messages.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	abstract protected function get_updated_messages( $post );

	/**
	 * Use a v4 UUID for new CPTs.
	 *
	 * @param  array $data Post data to save to the database.
	 * @return array
	 */
	public function add_uuid_to_new_posts( $data ) {
		if ( empty( $data['guid'] ) && $this->post_type === $data['post_type'] ) {
			$data['guid'] = wp_slash( sprintf( 'urn:uuid:%s', bandkit_generate_uuid_v4() ) );
		}

		return $data;
	}

	/**
	 * Filter post type update messages.
	 *
	 * @since 2.0.0
	 *
	 * @param array $messages Post type updated messages.
	 * @return array
	 */
	public function post_updated_messages( $messages ) {
		$post = get_post();

		if ( $post && $this->post_type === $post->post_type ) {
			$update_messages = $this->get_updated_messages( $post );
			$messages[ $this->post_type ] = $this->maybe_add_message_links( $update_messages, $post );
		}

		return $messages;
	}

	/**
	 * Add preview and view links to update messages for publicly queryable
	 * posts types.
	 *
	 * @since 2.0.0
	 *
	 * @param array   $messages Post updated messages.
	 * @param WP_Post $post Post object.
	 * @return array
	 */
	protected function maybe_add_message_links( $messages, $post ) {
		$post_type_object = get_post_type_object( $post->post_type );

		if ( $post_type_object->publicly_queryable ) {
			$permalink = get_permalink( $post->ID );
			$preview_permalink = add_query_arg( 'preview', 'true', $permalink );

			$preview_link = sprintf( ' <a href="%1$s" target="_blank">%2$s</a>', esc_url( $preview_permalink ), $messages['preview'] );
			$scheduled_link = sprintf( ' <a href="%1$s" target="_blank">%2$s</a>', esc_url( $permalink ), $messages['preview'] );
			$view_link = sprintf( ' <a href="%1$s">%2$s</a>', esc_url( $permalink ), $messages['view'] );

			$messages[1]  .= $view_link;
			$messages[6]  .= $view_link;
			$messages[8]  .= $preview_link;
			$messages[9]  .= $scheduled_link;
			$messages[10] .= $preview_link;
		}

		return $messages;
	}

	/**
	 * Whether a post has a draft or pending status.
	 *
	 * @since 2.0.0
	 *
	 * @param WP_Post $post Post object.
	 * @return bool
	 */
	protected function is_draft_or_pending( $post ) {
		return isset( $post->post_status ) && in_array( $post->post_status, array( 'draft', 'pending', 'auto-draft' ) );
	}
}

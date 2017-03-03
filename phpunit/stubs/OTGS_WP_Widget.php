<?php
/**
 * @author OnTheGo Systems
 */
interface OTGS_WP_Widget {
	public function get_field_name( $field_name );
	public function get_field_id( $field_name );
	public function _register();
	public function _set( $number );
	public function _get_display_callback();
	public function _get_update_callback();
	public function _get_form_callback();
	public function is_preview();
	public function display_callback( $args, $widget_args = 1 );
	public function update_callback( $deprecated = 1 );
	public function form_callback( $widget_args = 1 );
	public function _register_one( $number = - 1 );
	public function save_settings( $settings );
	public function get_settings();
}
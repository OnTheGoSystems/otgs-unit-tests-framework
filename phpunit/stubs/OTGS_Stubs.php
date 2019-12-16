<?php

/**
 * @author OnTheGo Systems
 */
class OTGS_Stubs {
	protected $test_case;

	/**
	 * OTGS_Stubs constructor.
	 *
	 * @param PHPUnit\Framework\TestCase $test_case
	 */
	public function __construct( PHPUnit\Framework\TestCase $test_case ) {
		$this->test_case = $test_case;
	}

	/**
	 * @param PHPUnit\Framework\TestCase $testCase
	 *
	 * @return WP_Widget|PHPUnit\Framework\MockObject\MockObject
	 */
	public function WP_Widget() {
		$methods = array(
			'__construct',
			'get_field_name',
			'get_field_id',
			'_register',
			'_set',
			'_get_display_callback',
			'_get_update_callback',
			'_get_form_callback',
			'is_preview',
			'display_callback',
			'update_callback',
			'form_callback',
			'_register_one',
			'save_settings',
			'get_settings',
		);

		return $this->test_case->getMockBuilder( 'OTGS_WP_Widget' )->setMockClassName( 'WP_Widget' )->setMethods( $methods )->getMock();
	}

	/**
	 * @param PHPUnit\Framework\TestCase $testCase
	 *
	 * @return WP_Theme|PHPUnit\Framework\MockObject\MockObject
	 */
	public function WP_Theme() {
		$methods = array(
			'get',
			'exists',
			'parent',
			'display',
			'get_stylesheet',
			'get_template',
			'get_stylesheet_directory',
			'get_template_directory',
			'get_stylesheet_directory_uri',
			'get_template_directory_uri',
			'get_theme_root',
			'get_theme_root_uri',
			'get_screenshot',
			'get_files',
			'get_post_templates',
			'get_page_templates',
			'load_textdomain',
			'is_allowed',
			'get_core_default_theme',
			'get_allowed',
			'get_allowed_on_network',
			'get_allowed_on_site',
			'network_enable_theme',
			'network_disable_theme',
			'sort_by_name',
		);

		return $this->test_case->getMockBuilder( 'WP_Theme' )->setMethods( $methods )->getMock();
	}

	/**
	 * @param PHPUnit\Framework\TestCase $testCase
	 *
	 * @return WP_Filesystem_Direct|PHPUnit\Framework\MockObject\MockObject
	 */
	public function WP_Filesystem_Direct() {
		$methods = array( 'exists', 'is_readable', 'get_contents_array' );

		return $this->test_case->getMockBuilder( 'WP_Filesystem_Direct' )->disableOriginalConstructor()->setMethods( $methods )->getMock();
	}

	/**
	 * @param PHPUnit\Framework\TestCase $testCase
	 *
	 * @return WP_Query|PHPUnit\Framework\MockObject\MockObject
	 */
	public function WP_Query() {
		$methods = array(
			'is_category',
			'set',
			'get',
			'is_tag',
			'is_tax',
			'get_queried_object_id',
			'is_archive',
			'is_attachment',
			'is_page',
			'is_singular',
		);

		return $this->test_case->getMockBuilder( 'WP_Query' )->disableOriginalConstructor()->setMethods( $methods )->getMock();
	}

	/**
	 * @param PHPUnit\Framework\TestCase $testCase
	 *
	 * @return wpdb|PHPUnit\Framework\MockObject\MockObject
	 */
	public function wpdb() {
		$methods = array(
			'prepare',
			'query',
			'get_results',
			'get_col',
			'get_var',
			'get_row',
			'delete',
			'update',
			'insert',
			'remove_placeholder_escape',
		);

		$wpdb = $this->test_case->getMockBuilder( 'wpdb' )->disableOriginalConstructor()->setMethods( $methods )->getMock();

		$wpdb->blogid             = 1;
		$wpdb->prefix             = 'wp_';
		$wpdb->posts              = 'posts';
		$wpdb->postmeta           = 'post_meta';
		$wpdb->comments           = 'comments';
		$wpdb->commentmeta        = 'comment_meta';
		$wpdb->terms              = 'terms';
		$wpdb->term_taxonomy      = 'term_taxonomy';
		$wpdb->term_relationships = 'term_relationships';

		return $wpdb;
	}
}

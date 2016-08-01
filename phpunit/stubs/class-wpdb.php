<?php

/**
 * @author OnTheGo Systems
 */
class wpdb {
	public $last_error = '';
	public $num_queries = 0;
	public $insert_id = 0;
	public $prefix = '';
	public $base_prefix;
	public $blogid = 0;
	public $siteid = 0;
	public $comments;
	public $commentmeta;
	public $links;
	public $options;
	public $postmeta;
	public $posts;
	public $terms;
	public $term_relationships;
	public $term_taxonomy;
	public $termmeta;
	public $usermeta;
	public $users;
	public $blogs;
	public $blog_versions;
	public $registration_log;
	public $signups;
	public $site;
	public $sitecategories;
	public $sitemeta;
	public $field_types = array();
	public $charset;
	public $collate;
	public $func_call;
	public $is_mysql = null;

	function get_results(){}
	function propare(){}
}
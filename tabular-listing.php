<?php
/*
Plugin Name: Tabular Listing
Plugin URI: http://wordpress.org/extend/plugins/tabular-listing/
Description: A plugin that help you with listing your posts in a tabular fashion as well as providing the criteria for filtering data.
Version: 120505
Author: Angela Zou
Author URI: http://blog.centilin.com
License: GPL2
*/
/*
    Copyright 2012  Angela Zou  (email : angelaz@centilin.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Add a configuration page in back-end panel
add_action('admin_menu', 'tab_list_admin_menu');
function tab_list_admin_menu() {
	add_options_page('Tabular Listing Options', 'Tabular Listing', 'manage_options', 'tabular-listing', 'tabular_listing_admin_output');
	add_action('admin_init', 'tab_list_register_plugin_settings');
}

//Add system field to store plugin settings
function tab_list_register_plugin_settings() {
	//General System Settings
	register_setting('tab_list_options', 'tab_list_enable_css');
	register_setting('tab_list_options', 'tab_list_enable_sort');
	register_setting('tab_list_options', 'tab_list_table_no', 'is_positive_int');
	
	add_settings_section('tab_list_general_options', 'General Options', 'tab_list_general_options_code', 'tab_list_general_options');
	add_settings_field('tab_list_enable_css', 'Use Plugin Styling', 'tab_list_enable_css_code', 'tab_list_general_options', 'tab_list_general_options');
	add_settings_field('tab_list_enable_sort', 'Use Table Sorting', 'tab_list_enable_sort_code', 'tab_list_general_options', 'tab_list_general_options');
	add_settings_field('tab_list_table_no', 'Number Of Tables', 'tab_list_table_no_code', 'tab_list_general_options', 'tab_list_general_options');

	//Per Table Settings
	register_setting('tab_list_options', 'table_1');
	$i = '1';
	add_settings_section('tab_list_table_1', 'Table 1', 'tab_list_table_code', 'tab_list_table_1');
	add_settings_field('tab_list_posts_filter_table_1', 'Posts Filter', 'tab_list_posts_filter_code', 'tab_list_table_1', 'tab_list_table_1', array("id"=>"1"));
	add_settings_field('tab_list_table_filter_table_1', 'Available Filters', 'tab_list_table_filter_code', 'tab_list_table_1', 'tab_list_table_1', array("id"=>"1"));	
	add_settings_field('tab_list_row_content_table_1', 'Row Content', 'tab_list_row_content_code', 'tab_list_table_1', 'tab_list_table_1', array("id"=>"1"));
}

//Validation Functions
function is_positive_int($val, $index=false) {
	$val = (!$index) ? intval($val) : intval($val[$index]);
	
	if($val < 0) return false;
	else return true;
}
function validate_table($val) {
	$pattern = '/^((post_type|post_status|meta_key|meta_value|numberposts|category|orderby|order|offset)\:[0-9a-z]+\;?)?$/i';
	if($val['posts_filter']) {
		$val['posts_filter'] = trim($val['posts_filter']);
		if(preg_match($pattern, $val['posts_filter']) == 0) return false;
		else return true;
	} else if($val['table_filter']) {
		$val['table_filter'] = trim($val['table_filter']);
		if(preg_match($pattern, $val['table_filter']) == 0) return false;
		else return true;
	} else if($val['row_content']) {
		
	}
}

//General Settings Output Functions
function tab_list_general_options_code() {
	echo '<p>' . _e("This section allow you to configure general Tabular Listing settings") . '</p>';
}
function tab_list_enable_css_code() {
	echo '<input id="tab_list_enable_css" name="tab_list_enable_css" type="checkbox" value="1" ' . checked( get_option("tab_list_enable_css"), 1, false ) . '" /><br />';
}
function tab_list_enable_sort_code() {
	echo '<input id="tab_list_enable_sort" name="tab_list_enable_sort" type="checkbox" value="1" ' . checked( get_option("tab_list_enable_sort"), 1, false ) . '" /><br />';
}
function tab_list_table_no_code() {
	echo '<input id="tab_list_table_no" name="tab_list_table_no" type="text" value="' . get_option("tab_list_table_no", "1") . '" /><br />';
}

//Per Table Settings Output Functions
function tab_list_table_code() {
	echo '<p>' . _e("This section contains configuration options for this table. Shortcode to use is [tab_list id=x]") . '</p>';
}
/*
function tab_list_table_html_code($i) {
	$table = 'table_' . $i["id"];
	switch($i["type"]) {
		case "posts_filter":
			$output = '<p>' . _e("List your post filter in the following format  criteria:condition. Criteria can be one of the following: post_type, post_status, meta_key, meta_value, numberposts, category, orderby, order, and offset. Example  post_status:published") . '</p>';
			$output .= '<input id="posts_filter" name="' . $table . '[posts_filter]" type="text" value="' . $options["posts_filter"] . '" /><br />';				break;
		case "table_filter":
			$output = '<p>' . _e("List the options you want your users to use for post filtering in the following format  criteria:condition:alias. Criteria can be one of the following: post_type, post_status, meta_key, meta_value, numberposts, category, orderby, order, and offset. Example  post_status:published:Published") . '</p>';
			$output .= '<input id="table_filter" name="' . $table . '[table_filter]" type="text" value="'. $options["table_filter"] . '" /><br />';
			break;
		case "row_content":
			$output = '<p>' . _e("Enter the content layout for each row here. Valid items include: post_name, published_date, thumb(x, y), excerpt, meta_key(name). meta_key(name) displays the meta value that of the meta key name. Use {col} and {/col} to enclose content for a column. Example {col} thumb(small){/col} {col} excerpt &lt;br /&gt; meta_key(price) {/col}") . '</p>';
			$output .= '<textarea id="row_content" name="' . $table . '[row_content]" cols=90 rows=8>' . $options["row_content"] . '</textarea><br />';
			break;
	} //End of Switch

	echo $output;
}//End of Function */

function tab_list_posts_filter_code($i) {
	$table = 'table_' . $i["id"];
	$options = get_option($table);
	$output = '<p>' . _e("List your post filter in the following format  criteria:condition. Criteria can be one of the following: post_type, post_status, meta_key, meta_value, numberposts, category, orderby, order, and offset. Example  post_status:published") . '</p>';
	$output .= '<input id="posts_filter" name="' . $table . '[posts_filter]" type="text" value="' . $options["posts_filter"] . '" /><br />';
	echo $output;
}
function tab_list_table_filter_code($i) {
	$table = 'table_' . $i["id"];
	$options = get_option($table);
	$output = '<p>' . _e("List the options you want your users to use for post filtering in the following format  criteria:condition:alias. Criteria can be one of the following: post_type, post_status, meta_key, meta_value, numberposts, category, orderby, order, and offset. Example  post_status:published:Published") . '</p>';
	$output .= '<input id="table_filter" name="' . $table . '[table_filter]" type="text" value="'. $options["table_filter"] . '" /><br />';
	echo $output;
}
function tab_list_row_content_code($i) {
	$table = 'table_' . $i["id"];
	$options = get_option($table);
	$output = '<p>' . _e("Enter the content layout for each row here. Valid items include: post_name, published_date, thumb(x, y), excerpt, meta_key(name). meta_key(name) displays the meta value that of the meta key name. Use {col} and {/col} to enclose content for a column. Example {col} thumb(small){/col} {col} excerpt &lt;br /&gt; meta_key(price) {/col}") . '</p>';
	$output .= '<textarea id="row_content" name="' . $table . '[row_content]" cols=90 rows=8>' . $options["row_content"] . '</textarea><br />';
	echo $output;
}


//Output Formatting
function tabular_listing_admin_output() {
	?>
	<div class="wrap">
		<h2>Tabular Listing General Options</h2>
		<form method="post" action="options.php">
			<?php
				settings_fields('tab_list_options');
				do_settings_sections('tab_list_general_options');
				do_settings_sections('tab_list_table_1');
			?>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>
	</div>
	<?php
}


//Enable CSS Styling
function enable_css() {
	echo '<link rel="stylesheet" id="tabular_listing" href="' . site_url("/wp-content/plugins/tabular-listing/styles.css") . '" type="text/css" media="all" />';
}
if(get_option('tab_list_enable_css') == '1') add_action('wp_head', 'enable_css');

//Enable Sort Table Script
function enable_sort() {
	echo '<script type="text/javascript" id="tabular_listing" src="' . site_url("/wp-content/plugins/tabular-listing/sorttable.js") . '"></script>';
}
if(get_option('tab_list_enable_sort') == '1') add_action('wp_head', 'enable_sort');


//Most Important Shortcode
add_shortcode('tab_list', 'tab_list');
function tab_list($atts) {
	extract( shortcode_atts( array(
		'id' => '',
	), $atts ) );
	global $post;
	$output = '';
	
	//Process the table filters first
	$options = get_option('table_' . $id);
	$table_filter = $options['table_filter'] ? $options['table_filter'] : '';
	$table_filter = str_replace(" ", "", $options['table_filter']);
	if(substr($table_filter, -1) == ';') $table_filter = substr($table_filter, 0, -1);
	$table_filter = explode(';', $table_filter);
	
	//Handling the table filters
	if($table_filter) {
		$output .= '<div id="selection-filters">' . _e('Display Only: ') . '<ul>';
		foreach($table_filter as $f) {
			$temp = explode(':', $f);
			$temp[2] = isset($temp[2]) ? $temp[2] : $temp[1];
			$output .= '<li><a href="/?' . $temp[0] . '=' . 	$temp[1] . '">' . $temp[2]  . '</a></li>';
		}
		$output .= '</ul></div>';
	}
		
	//Then process the posts filters
	$posts_filter = $options['posts_filter'] ? $options['posts_filter'] : somedefaultvalue;
	$posts_filter = str_replace(" ", "", $options['posts_filter']);
	$posts_filter = str_replace(';', '&', $posts_filter);
	$posts_filter = str_replace(':', '=', $posts_filter);
	$posts = get_posts($posts_filter);
	
	//And process the row content
	$row_content = trim($options['row_content']);
	preg_match_all('!\{col}(.*?)\{/col}!', $row_content, $content);
	
	unset($posts_filter);
	unset($table_filter);
	unset($options);
	
	//Get the posts first, then format according to row content
	if($posts) {
		$output .= '<table id="tab-list-content myTable">';
		foreach($posts as $post) {
			setup_postdata($posts);
			
			$output .= '<tr>';
			for($i = 0; $i < count($content); $i++) {
				$output .= '<td>' . return_content($content[1][$i], $post->ID, $post->post_date, $post->post_excerpt) . '</td>';
			}
			$output .= '</tr>';
		}
		$output .= '</table>';
	} else { //Bail early if there are no posts found
		$output .= '<div id="tab-list-content">' . _e('No Related Posts Found') . '</div>';
		return $output;
	}
	
	
	return $output;
}

function return_content($item, $id, $date, $excerpt) {
	var_dump($item);
	if(strpos($item, 'post_name') !== false) {
		$result = get_the_title($id);
		preg_replace('/post_name/', $result, $item);
		var_dump($item);
	}
	
	if(strpos($item, 'published_date') !== false) {
		preg_replace('/published_date/', $date, $item);
		var_dump($item);
	}
	
	if(strpos($item, 'excerpt') !== false) {
		preg_replace('/excerpt/', $excerpt, $item);
		var_dump($item);
	}
	
	if(strpos($item, 'thumb') !== false) {
		preg_replace_callback('/thumb\([^{]+\)/', function($match) use ($id) {
			$result = get_the_post_thumbnail($id, $match[0]) ? get_the_post_thumbnail($id, $match[0]) : '';
			return $result;
		}, $item);
		var_dump($item);
	}
	
	if(strpos($item, 'meta_key') !== false) {
		preg_replace_callback('/meta_key\(([^{]+)\)/', function ($match) use ($id) {
			$result = get_post_meta($id, $match[0], true) ? get_post_meta($id, $match[0], true) : '';
			return $result;
		}, $item);
		var_dump($item);
	}
	
	return $item;
}
?>
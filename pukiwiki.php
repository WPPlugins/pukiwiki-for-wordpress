<?php
/*
 Plugin Name: Pukiwiki for WordPress
 Plugin URI: http://wordpress.org/extend/plugins/pukiwiki-for-wordpress/
 Version: 0.2
 Description: Pukiwki for WordPress
 Author: makoto_kw
 Author URI: http://www.makotokw.com/
 */
/*  Copyright 2009 makoto_kw (email : makoto.kw+wordpress@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PukiWiki_for_WordPress
{
	const NAME = 'PukiWiki for WordPress';
	const VERSION = '0.2';
	
	var $agent = '';
	var $url = '';
	var $convertCount = 0; // for pukiwiki_navigateor_id
	
	function getInstance() {
		static $plugin = null;
		if (!$plugin) {
			$plugin = new PukiWiki_for_WordPress();
		}
		return $plugin;
	}
	
	function init() {
		$this->agent = self::NAME.'/'.self::VERSION;
		$this->url = get_bloginfo('url').'/wp-content/plugins/'.end(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
		add_action('wp_head', array($this,'head'));
		add_action('the_content', array($this,'the_content'), 7);
		add_filter('edit_page_form', array($this,'edit_form_advanced')); // for page
		add_filter('edit_form_advanced', array($this,'edit_form_advanced')); // for post
	}
	
	function head() {
	?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->url?>/pukiwiki.css"/>
	<?php
	}
	
	function the_content($str) {
		$replace = 'return wp_pukiwiki($matches[1]);';
		return preg_replace_callback('/\[pukiwiki\](.*?)\[\/pukiwiki\]/s',create_function('$matches',$replace),$str);
	}
	
	function edit_form_advanced() {
?>
<script type="text/javascript" src="<?php echo $this->url?>/admin.js"></script>
<?php
	}
	
	function convert($text) {
		$navigator = 'pukiwiki_content'.$this->convertCount++;
		return '<div id="'.$navigator.'" class="pukiwiki_content">'.$this->convert_html($text, $navigator).'</div>';
	}
	
	function convert_html($text, $navigator) {
		$path = $this->url.'/svc/';
		$content = http_build_query(array('content'=>$text, 'navigator' => $navigator));
		$headers = array(
			'User-Agent: '.$this->agent,
			'Content-Type: application/x-www-form-urlencoded',
			'Content-Length: '.strlen($content),
		);
		$opt = array(
			'http' => array(
				'method'    => 'POST',
				'header'    => implode("\r\n", $headers),
				'content'   => $content
			)
		);
		return @file_get_contents($path, false, stream_context_create($opt));
	}
}

add_action('init', 'pukiwiki_init');
function pukiwiki_init() {
	$p = PukiWiki_for_WordPress::getInstance();
	$p->init();
}

function wp_pukiwiki($text) {
	$p = PukiWiki_for_WordPress::getInstance();
	return $p->convert($text);
}
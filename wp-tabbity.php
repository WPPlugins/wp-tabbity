<?php
/*
Plugin Name: WP-Tabbity
Description: Creates tabbed display of content based on shortcode entered by user.
Version: 0.6
Author: Tom Belknap
Author URI: http://holisticnetworking.net
Plugin URI: http://holisticnetworking.net/plugins/2010/02/27/wp-tabbity/
*/

new WPTabbity;

class WPTabbity {
	var $tabset		= array();
	
	/*
	// getDefaults:Sets default options for new tabs
	*/
	private function getDefaults() {
		// Get our options:
		$options = get_option('wp-tabbity');
		$this->groupid		= 'aTabbityGroup';
		$this->groupcls		= isset($options['groupcls']) ? $options['groupcls'] . ' wp-tabbity-group' : 'wp-tabbity-group';
		$this->grouptitle	= isset($options['grouptitle']) ? $options['grouptitle'] : '';
		$this->title		= 'A Tabbity Tab';
		$this->class		= isset($options['class']) ? $options['class'] : 'wp-tabbity';
		$this->order		= 'default';
		$this->style		= isset($options['style']) ? $options['style'] : 'default';
		$this->css			= !empty($options['css']) ? $options['css'] : '';
	}
	
	/*
	// Horribly redundant settings functions:
	*/
	function registerSettings() {
		register_setting( 'wp-tabbity', 'wp-tabbity' );
		add_settings_section('tabbity-defaults', 'Default Settings', array(&$this, 'displayDefaultText'), 'wp-tabbity');
		add_settings_field('grouptitle_setting', 'Default Tab Group Title:', array(&$this, 'grouptitle_setting_output'), 'wp-tabbity', 'tabbity-defaults');
		add_settings_field('groupcls_setting', 'Default Tab Group Class:', array(&$this, 'groupcls_setting_output'), 'wp-tabbity', 'tabbity-defaults');
		add_settings_field('class_setting', 'Default Tab Class:', array(&$this, 'class_setting_output'), 'wp-tabbity', 'tabbity-defaults');
		add_settings_field('style_setting', 'Tab Style:', array(&$this, 'style_setting_output'), 'wp-tabbity', 'tabbity-defaults');
		add_settings_field('csss_setting', 'Custom Stylesheet:', array(&$this, 'css_setting_output'), 'wp-tabbity', 'tabbity-defaults');
	}
	function displayDefaultText() {
		echo '<p>Set the default values for WP-Tabbity tabs and tab groups.</p>';
	}
	function grouptitle_setting_output() {
		$options = get_option('wp-tabbity');
		echo "<input id='groupcls' name='wp-tabbity[grouptitle]' size='40' type='text' value='{$options['grouptitle']}' />";
	}
	function groupcls_setting_output() {
		$options = get_option('wp-tabbity');
		echo "<input id='groupcls' name='wp-tabbity[groupcls]' size='40' type='text' value='{$options['groupcls']}' />";
	}
	function class_setting_output() {
		$options = get_option('wp-tabbity');
		echo "<input id='groupcls' name='wp-tabbity[class]' size='40' type='text' value='{$options['class']}' />";
	}
	function style_setting_output() {
		$options	= get_option('wp-tabbity');
		$themes		= $this->getThemes();
		$output		= "<select name='wp-tabbity[style]'>";
		foreach($themes as $key=>$value) :
			$output	.= sprintf(
				'<option value="%s" %s>%s</option>',
				$value,
				($value == $options['style']) ? 'selected="selected"' : '',
				ucwords( preg_replace( '/[-_]+/', ' ', $value ) )
			);
		endforeach;
		$output .= "</select>";
		echo $output;
	}
	function css_setting_output() {
		$options = get_option('wp-tabbity');
		echo "<p><strong>Note:</strong> path to this CSS file is relative to your theme's directory! </p><input id='groupcss' name='wp-tabbity[css]' size='100' type='text' value='{$options['css']}' />";
	}
	
	/*
	// getThemes:	Returns a list of available theme directories.
	*/
	function getThemes() {
		$return = array();
		
		// Default list:
		$default	= dirname(__FILE__).'/styles';
		$dlist		= scandir( $default );
		foreach( $dlist as $key=>$value ) :
			if( is_dir( dirname( __FILE__ ) . '/styles/' . $value) && !in_array( $value, array( '.', '..', '.git', '.svn' ) ) ) :
				$return[] = $value;
			endif;
		endforeach;
		
		// User-added themes:
		$dir	= get_stylesheet_directory() . '/wp-tabbity';
		$list	= scandir( $dir );
		if( !empty( $list ) ) :
			foreach( $list as $key=>$value ) :
				if( is_dir( get_stylesheet_directory() . '/wp-tabbity/' . $value ) && !in_array( $value, array( '.', '..', '.git', '.svn' ) ) ) :
					$return[] = $value;
				endif;
			endforeach;
		endif;
		asort( $return );
		return $return;
	}
	
	
	
	/*
	// tab:						Does the work of parsing and creating tabs on post load.
	// @var str $atts:			The included attributes from the shortcode.
	// @var str $content:		The post content contained in the shortcode.
	// @return str $output:		A <div> created to hold the content of this tab group.
	*/
	function tab($atts, $content) {
		$defaultid		= str_split(strip_tags($content), 10);
		extract(shortcode_atts(array(
			'title'		=> trim($defaultid[0]),
			'id' 		=> sanitize_title_with_dashes(trim($defaultid[0])),
			'class'		=> $this->class,
			'order'		=> $this->order
		), $atts));
		$this->addTab($title, $id, $class, $order, $content);
		// return 'No, you are not crazy.';
	}
	
	
	/*
	// addTab:				Adds a shortcode tab to the selected group. Also adds the 
	//						tab group to the page in the correct location, if not already there.
	// @vat str $groupid:	Specifies what group the tab belongs to.
	// @var str $groupcls:	The class a group belongs to.
	// @var str $title:		The tab's title.
	// @var str $id:		The tab's id.
	// @var str $class:		The class a tab belongs to.
	// @var str $order:		The order in which the tab displays, or default.
	*/
	public function addTab($title, $id, $class, $order, $content) {
		if(isset($this->tabset['id'])) :
			$class = ($class == 'wp-tabbity') ? $class : $class.' wp-tabbity';
			// Create our html elements for this tab:
			$tab['li']			= '<li class="'.$class.'"><a href="#'.$id.'">'.$title.'</a></li>';
			$tab['content']		= '<div id="'.$id.'">'.do_shortcode(trim($content)).'</div>';
			// If we have an $order number, use that. Else, just put the tab at the end of the list:
			if($order != 'default') :
				$this->tabset['tabs'][$order]	= $tab;
			else :
				$this->tabset['tabs'][]			= $tab;
			endif;
		endif;
	}
	
	

	/*
	// group:					Output the group.
	// @var str $atts:			The included attributes for the shortcode.
	// @var str $content:		The content inside the tabbity group.
	*/
	function group($atts, $content) {
		global $wpdb;
		extract(shortcode_atts(array(
			'groupcls'		=> $this->groupcls,
			'groupid'		=> $this->groupid,
			'grouptitle'	=> $this->grouptitle,
		), $atts));
		// make sure groupcls includes wp-tabbity-group:
		if(!stristr($groupcls, 'wp-tabbity-group')) :
			$groupcls	.= ' wp-tabbity-group';
		endif;
		// Establish our tabset:
		$this->addGroup($groupid, $groupcls, $grouptitle);
		// bundle up our tabs before we add the group to the page:
		$content	= do_shortcode($content);
		// Don't attempt output if there's nothing to put out:
		if(isset($this->tabset['tabs'])) :
			$output = $this->outputGroup();
			return $output;
		endif;
	}
	
	
	/*
	// addGroup:		Defines the group in the tabset property
	*/
	private function addGroup($groupid, $groupcls, $grouptitle) {
		$this->tabset['id'] 		= $groupid;
		$this->tabset['class']		= $groupcls;
		$this->tabset['title']		= $grouptitle;
		return true;
	}
	
	
	
	/*
	// outputGroup:	Adds the group div into the page and inserts tabs
	//				from this->tabset.
	*/
	public function outputGroup() {
		// If the groupid exists, cool. If not, print nothing:
		if(isset($this->tabset['tabs'])) :
			$output		= !empty($this->tabset['title']) ? '<h2 class="wp-tabbity-title">'.$this->tabset['title'].'</h2>' : '';
			$output		.= '<div id="'.$this->tabset['id'].'" class="'.$this->tabset['class'].'">';
			// Create tab list:
			$output		.= '<ul>';
			foreach($this->tabset['tabs'] as $key=>$tab) :
				$output .= $tab['li'];
			endforeach;
			$output		.= '</ul>';
			// Create output divs:
			foreach($this->tabset['tabs'] as $key=>$tab) :
				$output .= $tab['content'];
			endforeach;
			$output		.= '</div>';
			// Zero out the Object and dump the result back into the Post:
			unset($this->tabset);
			return $output;
		endif;
	}



	/*
	// enqueue:					Enqueues the correct scripts to launch the jQuery tabs
	*/
	function enqueue() {
		// Custom CSS
		if( !empty( $this->css ) ) :
			wp_register_style( 'wpTabbity', get_stylesheet_directory_uri() . $this->css );
		// jQuery style:
		else :
			if( file_exists( plugin_dir_path( __FILE__ ) . $this->style . '/style.css' ) ) :
				wp_register_style( 'wpTabbity', plugins_url( $this->css, __FILE__ ) );
			else :
				wp_register_style( 'wpTabbity', get_stylesheet_directory_uri() . '/wp-tabbity/' . $this->style . '/jquery-ui.theme.min.css' );
			endif;
		endif;
		wp_enqueue_style( 'wpTabbity' );
		wp_enqueue_script('tabbity', plugins_url() . '/wp-tabbity/wp-tabbity-script.js', array('jquery', 'jquery-ui-core', 'jquery-ui-tabs'), '1.0');
	}

	/*
	// printStyles:	Adds the CSS styles to the page.
	*/
	function printStyles() {
		
	}



	/*
	// add_menu:		Administration panel
	*/
	function add_menu() {
		add_options_page('WP-Tabbity', 'WP-Tabbity', 'edit_posts', basename(__FILE__), array(&$this, 'control'));
		add_action('admin_init', array(&$this, 'registerSettings'));
	}

	/*
	// control
	*/
	function control() {
		$themes	= $this->getThemes();
		?>
	<div class="wrap">
		<h2>WP-Tabbity</h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'wp-tabbity' ); ?>
			<?php do_settings_sections('wp-tabbity'); ?>
			<input name="Submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
		</form>
	</div>
		<?php
	}
	
	
	function WPTabbity() {
		$this->__construct();
	}
	
	
	// Set 'em up, Joe:
	function __construct() {
		$this->getDefaults();
		// Add our methods to WordPress:
		if ( is_admin() ){ // admin actions
			add_action('admin_menu', array(&$this, 'add_menu'));
		} else {
			add_shortcode('wp-tabbity', array(&$this, 'tab'));
			add_shortcode('wp-tabbitygroup', array(&$this, 'group'));
			add_action('init', array(&$this, 'enqueue'));
			add_action('wp_print_styles', array(&$this, 'printStyles'));
		}
	}
}

?>

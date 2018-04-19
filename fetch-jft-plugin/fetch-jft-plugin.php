<?php
/*
Plugin Name: Fetch JFT
Plugin URI: https://wordpress.org/plugins/fetch-jft/
Author: Patrick J NERNA
Description: This is a plugin that fetches the Just For Today from NAWS and puts it on your site Simply add [jft] shortcode to your page. Fetch JFT Widget can be added to your sidebar or footer as well.
Version: 1.1.1
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

require_once('simple_html_dom.php');
require_once('admin/jft-dashboard.php');
add_action('admin_menu', 'jft_plugin_admin_add_page');

function jft_func( $atts ){
	/*$jft_language = get_option(jft_language);
	
	switch ($jft_language) {
		case 'english' :
			$jft_language_url = 'https://jftna.org/jft/';
			break;
		case 'spanish':
			$jft_language_url = 'https://forozonalatino.org/sxh';
			break;
		case 'french':
			$jft_language_url = 'https://jftna.org/jft/';
			break;
		default:
			$jft_language_url = 'http://jpa.narcotiquesanonymes.org/';		
	}*/

	// Get the contents of JFT
	if(get_option(jft_layout) == 'block'){
		$d = file_get_html('https://jftna.org/jft/');
		$jft_ids = array('jft-date','jft-title','jft-page','jft-quote','jft-quote-source','jft-content','jft-thought','jft-copyright');
		$i = 0;
		foreach($d->find(tr) as $element) {
			if($i != 5) {
				$formated_element = trim(strip_tags($element));
				echo '<div id="'.$jft_ids[$i].'">'.$formated_element.'</div>';
			}else{
				$break_array = preg_split('/<br[^>]*>/i', $element);
				foreach($break_array as $p) {
					if(!empty($p)){
						$content = '<p class="'.$jft_ids[$i].'">'.trim($p).'</p>';
						echo preg_replace("/<p[^>]*>([\s]|&nbsp;)*<\/p>/", '', $content); 
					}
				}
			}
			$i++; 
		}
	}else{
		$d = new DOMDocument;
		$jft = new DOMDocument;
		
		// Get the contents of JFT
		$d->loadHTML(file_get_contents('https://jftna.org/jft/'));
		// Parse and extract just the body
		$body = $d->getElementsByTagName('body')->item(0);
		foreach ($body->childNodes as $child) {
			$jft->appendChild($jft->importNode($child, true));
		}
		// export just the html of body
		echo $jft->saveHTML();
	}
	?>
	<div align="right"><a href="https://www.jftna.org/jft-subscription.htm" target="_blank">Subscribe</a></div>
	<?php	
}

// create [jft] shortcode
add_shortcode( 'jft', 'jft_func' );


/** START Fetch JFT Widget **/
// register JFT_Widget
add_action( 'widgets_init', function(){
	register_widget( 'JFT_Widget' );
});

class JFT_Widget extends WP_Widget {
	/**
	 * Sets up a new Fetch JFT widget instance.
	 *
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'JFT_widget',
			'description' => 'Displays the Just For Today',
		);
	parent::__construct( 'JFT_widget', 'Fetch JFT', $widget_ops );
	}
	
	/**
	 * Outputs the content for the current Fetch JFT widget instance.
	 *
	 *
	 * @jft_func gets and parses the jft
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Area Meetings Dropdown widget instance.
	 */
	
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		echo jft_func( $atts );
		echo $args['after_widget'];
	}
	/**
	* Outputs the settings form for the Fetch JFT widget.
	*
	*/
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Title', 'text_domain' );
		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
		<?php esc_attr_e( 'Title:', 'text_domain' ); ?>
		</label> 
	
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
			name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
			type="text" 
			value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	* Handles updating settings for the current Fetch JFT widget instance.
	*
	* @param array $new_instance New settings for this instance as input by the user via
	*                            WP_Widget::form().
	* @param array $old_instance Old settings for this instance.
	* @return array Updated settings to save.
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}
/** END Fetch JFT Widget **/

?>
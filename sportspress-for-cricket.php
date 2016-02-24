<?php
/*
 * Plugin Name: SportsPress for Cricket
 * Plugin URI: http://themeboy.com/
 * Description: A suite of cricket features for SportsPress.
 * Author: ThemeBoy
 * Author URI: http://themeboy.com/
 * Version: 0.9.3
 *
 * Text Domain: sportspress-for-cricket
 * Domain Path: /languages/
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SportsPress_Cricket' ) ) :

/**
 * Main SportsPress Cricket Class
 *
 * @class SportsPress_Cricket
 * @version	0.9.3
 */
class SportsPress_Cricket {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Define constants
		$this->define_constants();

		// Include required files
		$this->includes();

		// Output generator tag
		add_action( 'get_the_generator_html', array( $this, 'generator_tag' ), 10, 2 );
		add_action( 'get_the_generator_xhtml', array( $this, 'generator_tag' ), 10, 2 );

		// Require SportsPress core
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		// Enqueue styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );
		add_filter( 'sportspress_enqueue_styles', array( $this, 'add_styles' ) );

		// Change text to reflect cricket terminology
		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );

		// Add extras to event performance
		add_action( 'sportspress_event_performance_meta_box_table_footer', array( $this, 'meta_box_table_footer' ), 10, 8 );
		add_action( 'sportspress_event_performance_table_footer', array( $this, 'table_footer' ), 10, 4 );
		add_filter( 'sportspress_event_performance_show_footer', '__return_true' );

		// Display subs separately
		add_filter( 'sportspress_event_performance_players', array( $this, 'players' ), 10, 2 );
		add_action( 'sportspress_after_event_performance_table', array( $this, 'subs' ), 10, 4 );

		// Display formatted results
		add_filter( 'sportspress_event_logo_options', array( $this, 'event_logo_options' ) );
		add_filter( 'sportspress_event_logos_team_result', array( $this, 'format_result' ), 10, 3 );
		add_filter( 'sportspress_event_team_result_admin', array( $this, 'format_result' ), 10, 3 );
		add_filter( 'sportspress_calendar_team_result_admin', array( $this, 'format_result' ), 10, 3 );
		add_filter( 'sportspress_event_list_main_results', array( $this, 'format_results' ), 10, 2 );
		add_filter( 'sportspress_event_blocks_team_result_or_time', array( $this, 'format_results' ), 10, 2 );
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_CRICKET_VERSION' ) )
			define( 'SP_CRICKET_VERSION', '0.9.3' );

		if ( !defined( 'SP_CRICKET_URL' ) )
			define( 'SP_CRICKET_URL', plugin_dir_url( __FILE__ ) );

		if ( !defined( 'SP_CRICKET_DIR' ) )
			define( 'SP_CRICKET_DIR', plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Register/queue frontend scripts.
	 *
	 * @access public
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'sportspress-cricket', SP_CRICKET_URL .'js/sportspress-cricket.js', array( 'jquery' ), SP_CRICKET_VERSION, true );
	}

	/**
	 * Enqueue styles.
	 */
	public static function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'sp_event', 'edit-sp_event' ) ) ) {
			wp_enqueue_script( 'sportspress-cricket-admin', SP_CRICKET_URL . 'js/admin.js', array( 'jquery' ), SP_CRICKET_VERSION, true );
		}

		wp_enqueue_style( 'sportspress-cricket-admin', SP_CRICKET_URL . 'css/admin.css', array( 'sportspress-admin-menu-styles' ), '0.9' );
	}

	/**
	 * Add stylesheet.
	*/
	public static function add_styles( $styles = array() ) {
		$styles['sportspress-for-cricket'] = array(
			'src'     => str_replace( array( 'http:', 'https:' ), '', SP_CRICKET_URL ) . '/css/sportspress-for-cricket.css',
			'deps'    => '',
			'version' => SP_CRICKET_VERSION,
			'media'   => 'all'
		);
		return $styles;
	}

	/**
	 * Include required files.
	*/
	private function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';
	}

	/**
	 * Output generator tag to aid debugging.
	 */
	function generator_tag( $gen, $type ) {
		switch ( $type ) {
			case 'html':
				$gen .= "\n" . '<meta name="generator" content="SportsPress for Cricket ' . esc_attr( SP_CRICKET_VERSION ) . '">';
				break;
			case 'xhtml':
				$gen .= "\n" . '<meta name="generator" content="SportsPress for Cricket ' . esc_attr( SP_CRICKET_VERSION ) . '" />';
				break;
		}
		return $gen;
	}

	/**
	 * Require SportsPress core.
	*/
	public static function require_core() {
		$plugins = array(
			array(
				'name'        => 'SportsPress',
				'slug'        => 'sportspress',
				'required'    => true,
				'is_callable' => array( 'SportsPress', 'instance' ),
			),
		);

		$config = array(
			'default_path' => '',
			'menu'         => 'tgmpa-install-plugins',
			'has_notices'  => true,
			'dismissable'  => true,
			'is_automatic' => true,
			'message'      => '',
			'strings'      => array(
				'nag_type' => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}

	/** 
	 * Text filter.
	 */
	public function gettext( $translated_text, $untranslated_text, $domain ) {
		if ( $domain == 'sportspress' ) {
			switch ( $untranslated_text ) {
				case 'Events':
					$translated_text = __( 'Matches', 'sportspress-for-cricket' );
					break;
				case 'Event':
					$translated_text = __( 'Match', 'sportspress-for-cricket' );
					break;
				case 'Add New Event':
					$translated_text = __( 'Add New Match', 'sportspress-for-cricket' );
					break;
				case 'Edit Event':
					$translated_text = __( 'Edit Match', 'sportspress-for-cricket' );
					break;
				case 'View Event':
					$translated_text = __( 'View Match', 'sportspress-for-cricket' );
					break;
				case 'View all events':
					$translated_text = __( 'View all matches', 'sportspress-for-cricket' );
					break;
				case 'Substitute':
				case 'Substituted':
					$translated_text = __( 'Did Not Bat', 'sportspress-for-cricket' );
					break;
				case 'Offense':
					$translated_text = __( 'Batting', 'sportspress-for-cricket' );
					break;
				case 'Defense':
					$translated_text = __( 'Bowling', 'sportspress-for-cricket' );
					break;
			}
		}
		
		return $translated_text;
	}

	/**
	 * Display extras in event edit page.
	*/
	public function meta_box_table_footer( $data = array(), $labels = array(), $team_id = 0, $positions = array(), $status = true, $sortable = true, $numbers = true, $section = -1 ) {
		if ( 1 == $section ) return;
		?>
		<tr class="sp-row sp-post sp-extras">
			<?php if ( $sortable ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<?php if ( $numbers ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td><strong><?php _e( 'Extras', 'sportspress-for-cricket' ); ?></strong></td>
			<?php if ( ! empty( $positions ) ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<?php
				$colspan = 1;
				if ( is_array( $labels ) ) {
					$colspan = sizeof( $labels );
				}
			?>
			<td colspan="<?php echo $colspan; ?>"><input type="text" name="sp_players[<?php echo $team_id; ?>][0][extras]" value="<?php echo sp_array_value( sp_array_value( $data, 0, array() ), 'extras', '' ); ?>" /></td>
			<?php if ( $status ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
		</tr>
		<?php
	}

	/**
	 * Display extras in event page.
	*/
	public function table_footer( $data = array(), $labels = array(), $position = null, $performance_ids = null ) {
		$show_players = get_option( 'sportspress_event_show_players', 'yes' ) === 'yes' ? true : false;
		$show_numbers = get_option( 'sportspress_event_show_player_numbers', 'yes' ) === 'yes' ? true : false;
		$mode = get_option( 'sportspress_event_performance_mode', 'values' );

		$row = sp_array_value( $data, 0, array() );
		$row = array_filter( $row );
		$extras = sp_array_value( $row, 'extras', '' );
		$extras = trim( $extras );
		if ( ! empty( $extras ) ) {
			?>
			<tr class="sp-extras-row">
				<?php
				if ( $show_players ):
					if ( $show_numbers ) {
						echo '<td class="data-number">&nbsp;</td>';
					}
					echo '<td class="data-name">' . __( 'Extras', 'sportspress-for-cricket' ) . '</td>';
				endif;

				echo '<td class="data-extras" colspan="' . sizeof( $labels ) . '">' . $extras . '</td>';
				?>
			</tr>
			<?php
		}
	}

	/**
	 * Remove subs from main box score.
	*/
	public function players( $data = array(), $lineups = array() ) {
		return $lineups;
	}

	/**
	 * Display subs in own section.
	*/
	public function subs( $data = array(), $lineups = array(), $subs = array(), $class = '' ) {
		if ( empty( $subs ) || '0' !== substr( $class, -1 ) ) return;

		$names = array();

		foreach ( $subs as $id => $void ) {
			$name = get_the_title( $id );
			if ( $name ) {
				$link = get_post_permalink( $id );
				$names[] = '<a href="' . $link . '">' . $name . '</a>';
			}
		}
		?>
		<p class="sp-event-performance-simple-subs sp-align-left">
			<?php printf( __( 'Did not bat: %s', 'sportspress-for-cricket' ), implode( ', ', $names ) ); ?>
		</p>
		<?php
	}

	/**
	 * Add event logo options.
	*/
	public function event_logo_options( $options = array() ) {
		$options[] = array(
			'title' 	=> __( 'Delimiter', 'sportspress-for-cricket' ),
			'id' 		=> 'sportspress_event_logos_results_delimiter',
			'class' 	=> 'small-text',
			'default'	=> '/',
			'type' 		=> 'text',
		);

		$options[] = array(
			'title'     => __( 'Format', 'sportspress-for-cricket' ),
			'desc' 		=> __( 'Reverse order', 'sportspress-for-cricket' ),
			'id' 		=> 'sportspress_event_logos_reverse_results_format',
			'default'	=> 'no',
			'type' 		=> 'checkbox',
		);
		return $options;
	}

	/**
	 * Format single result.
	*/
	public function format_result( $result = '', $id = 0, $team = 0 ) {
		if ( '' === $result || ! $id || ! $team ) return $result;
		$results = sp_get_results( $id );
		$team_results = sp_array_value( $results, $team, array() );
		if ( ! is_array( $team_results ) || 0 == sizeof( $team_results ) ) return $result;
		$main = sp_get_main_result_option();
		while ( key( $team_results ) !== $main ) {
			next( $team_results );
		}
		$val = next( $team_results );
		if ( false === $val ) {
			$val = reset( $team_results );
		}
		if ( isset( $val ) && ! is_array( $val ) && '' !== $val ) {
			$delimiter = get_option( 'sportspress_event_logos_results_delimiter', '/' );
			$reverse = get_option( 'sportspress_event_logos_reverse_results_format', 'no' );
			if ( 'yes' == $reverse ) {
				$result = $val . $delimiter . $result;
			} else {
				$result .= $delimiter . $val;
			}
		}
		return $result;
	}

	/**
	 * Format results.
	*/
	public function format_results( $results = array(), $id = 0 ) {
		if ( ! is_array( $results ) || 1 >= sizeof( $results ) || ! $id ) return $results;

		$teams = get_post_meta( $id, 'sp_team' );

		foreach ( $results as $team => $result ) {
			$results[ $team ] = self::format_result( $result, $id, sp_array_value( $teams, $team, 0 ) );
		}

		return $results;
	}
}

endif;

new SportsPress_Cricket();

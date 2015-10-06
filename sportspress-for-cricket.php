<?php
/*
Plugin Name: SportsPress for Cricket
Plugin URI: http://themeboy.com/
Description: A suite of cricket features for SportsPress.
Author: ThemeBoy
Author URI: http://themeboy.com/
Version: 0.9
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'SportsPress_Cricket' ) ) :

/**
 * Main SportsPress Cricket Class
 *
 * @class SportsPress_Cricket
 * @version	0.9
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

		// Require SportsPress core
		add_action( 'tgmpa_register', array( $this, 'require_core' ) );

		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 30 );

		// Change text to reflect cricket terminology
		add_filter( 'gettext', array( $this, 'gettext' ), 20, 3 );

		// Add extras to event performance
		add_action( 'sportspress_event_performance_meta_box_table_footer', array( $this, 'meta_box_table_footer' ), 10, 7 );
		add_action( 'sportspress_event_performance_table_footer', array( $this, 'table_footer' ), 10, 4 );
		add_filter( 'sportspress_event_performance_table_total_value', array( $this, 'table_total_value' ), 10, 3 );
		add_filter( 'sportspress_event_performance_split_team_players', array( $this, 'split_team_players' ) );
		add_filter( 'sportspress_event_performance_split_team_split_position_subdata', array( $this, 'subdata' ), 10, 3 );
		add_filter( 'sportspress_event_performance_show_footer', '__return_true' );

		// Display subs separately
		add_action( 'sportspress_after_event_performance_table', array( $this, 'subs' ), 10, 4 );
		add_filter( 'sportspress_event_performance_players', array( $this, 'players' ), 10, 2 );

		// Add bowling order
		add_filter( 'sportspress_event_performance_number_label', array( $this, 'number_label' ) );
	}

	/**
	 * Define constants.
	*/
	private function define_constants() {
		if ( !defined( 'SP_CRICKET_VERSION' ) )
			define( 'SP_CRICKET_VERSION', '0.9' );

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
	 * Include required files.
	*/
	private function includes() {
		require_once dirname( __FILE__ ) . '/includes/class-tgm-plugin-activation.php';
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
					$translated_text = _x( 'Matches', 'cricket', 'sportspress' );
					break;
				case 'Event':
					$translated_text = _x( 'Match', 'cricket', 'sportspress' );
					break;
				case 'Add New Event':
					$translated_text = _x( 'Add New Match', 'cricket', 'sportspress' );
					break;
				case 'Edit Event':
					$translated_text = _x( 'Edit Match', 'cricket', 'sportspress' );
					break;
				case 'View Event':
					$translated_text = _x( 'View Match', 'cricket', 'sportspress' );
					break;
				case 'View all events':
					$translated_text = _x( 'View all matches', 'cricket', 'sportspress' );
					break;
				case 'Substitute':
				case 'Substituted':
					$translated_text = _x( 'Did Not Bat', 'cricket', 'sportspress' );
					break;
			}
		}
		
		return $translated_text;
	}

	/**
	 * Display extras in event edit page.
	*/
	public function meta_box_table_footer( $data = array(), $labels = array(), $team_id = 0, $positions = array(), $status = true, $sortable = true, $numbers = true ) {
		?>
		<tr class="sp-row sp-post sp-extras">
			<?php if ( $sortable ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<?php if ( $numbers ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<td><strong><?php _e( 'Extras', 'sportspress' ); ?></strong></td>
			<?php if ( ! empty( $positions ) ) { ?>
				<td>&nbsp;</td>
			<?php } ?>
			<?php foreach( $labels as $column => $label ):
				$player_performance = sp_array_value( $data, -1, array() );
				$value = sp_array_value( $player_performance, $column, '' );
				?>
				<td><input type="text" name="sp_players[<?php echo $team_id; ?>][-1][<?php echo $column; ?>]" value="<?php echo $value; ?>" /></td>
			<?php endforeach; ?>
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

		$row = sp_array_value( $data, -1, array() );
		$row = array_filter( $row );
		$row = array_intersect_key( $row, $labels );
		if ( ! empty( $row ) ) {
			?>
			<tr class="sp-extras-row <?php echo ( $i % 2 == 0 ? 'odd' : 'even' ); ?>">
				<?php
				if ( $show_players ):
					if ( $show_numbers ) {
						echo '<td class="data-number">&nbsp;</td>';
					}
					echo '<td class="data-name">' . __( 'Extras', 'sportspress' ) . '</td>';
				endif;

				$row = sp_array_value( $data, -1, array() );

				if ( $mode == 'icons' ) echo '<td class="sp-performance-icons">';

				foreach ( $labels as $key => $label ):
					if ( 'name' == $key )
						continue;
					if ( isset( $position ) && 'position' == $key )
						continue;
					if ( $key == 'position' ):
						$value = '&nbsp;';
					elseif ( array_key_exists( $key, $row ) && $row[ $key ] != '' ):
						$value = $row[ $key ];
					else:
						$value = '&nbsp;';
					endif;

					if ( $mode == 'values' ):
						echo '<td class="data-' . $key . '">' . $value . '</td>';
					elseif ( intval( $value ) && $mode == 'icons' ):
						$performance_id = sp_array_value( $performance_ids, $key, null );
						if ( $performance_id && has_post_thumbnail( $performance_id ) ):
							echo str_repeat( get_the_post_thumbnail( $performance_id, 'sportspress-fit-mini' ) . ' ', $value );
						endif;
					endif;
				endforeach;

				if ( $mode == 'icons' ) echo '</td>';
				?>
			</tr>
			<?php
		}
	}

	/**
	 * Add extras to performance total.
	*/
	public function table_total_value( $value = 0, $data = array(), $key = null ) {
		$value += sp_array_value( sp_array_value( $data, -1, array() ), $key, 0 );
		return $value;
	}

	/**
	 * Add extra player row to split team players.
	*/
	public function split_team_players( $players = array() ) {
		$players[] = -1;
		return $players;
	}

	/**
	 * Add extra subdata to split team split position players.
	*/
	public function subdata( $subdata = array(), $data = array(), $index = 0 ) {
		if ( 1 == $index ) {
			uasort( $subdata, array( $this, 'sort_by_number' ) );
		}
		$subdata[-1] = sp_array_value( $data, -1, array() );
		return $subdata;
	}

	/**
	 * Sort array by number subvalue.
	*/
	public function sort_by_number( $a, $b ) {
		return $a['number'] - $b['number'];
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
			<?php printf( __( 'Did not bat: %s', 'sportspress' ), implode( ', ', $names ) ); ?>
		</p>
		<?php
	}

	/**
	 * Display number as bowling order.
	*/
	public function number_label() {
		return __( 'Bowling Order', 'sportspress' );
	}
}

endif;

new SportsPress_Cricket();

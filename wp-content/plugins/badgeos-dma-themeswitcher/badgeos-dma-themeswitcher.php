<?php
/**
 * Plugin Name: DMA Theme Switcher
 * Plugin URI: http://WebDevStudios.com
 * Description: Allow admins to switch between the Kiosk site and the public-facing site.
 * Version: 1.0
 * Author: WebDevStudios
 * Author URI: http://WebDevStudios.com
 */

class DMA_Theme_Switcher {

	function __construct() {

		// Grab our available themes
        $kiosk_theme = wp_get_theme(get_option('dma_kiosk_theme'));
        $portal_theme = wp_get_theme(get_option('dma_portal_theme'));

		$this->available_themes = array(
            $kiosk_theme->stylesheet => $kiosk_theme->name,
            $portal_theme->stylesheet => $portal_theme->name,
        );

		// Setup all our hooks and filters
		add_action( 'init',       array( &$this, 'set_theme_cookie' ) );
		add_filter( 'stylesheet', array( &$this, 'get_stylesheet' ) );
		add_filter( 'template',   array( &$this, 'get_template' ) );
		add_action( 'dma_header', array( &$this, 'theme_switcher_output' ) );
	}

	/**
	 * Get the desired theme stylesheet
	 *
	 * @since  1.0
	 * @param  string $stylesheet A specific fallback stylesheet if a desired theme is not selected
	 * @return string             The stylesheet to use
	 */
	function get_stylesheet($stylesheet = '') {

		$theme = $this->get_theme();

		if (empty($theme)) {
			return $stylesheet;
		}

		return $theme['Stylesheet'];
	}

	/**
	 * Get the desired template file
	 *
	 * @since  1.0
	 * @param  string $template A specific fallback template if a desired theme is not selected
	 * @return string           The template to use
	 */
	function get_template($template) {

		$theme = $this->get_theme();

		if ( empty( $theme ) ) {
			return $template;
		}

		return $theme['Template'];
	}

	/**
	 * Helper function to get a specific theme as set by the visitor
	 *
	 * @since  1.0
	 * @return string The theme name to use if set, empty otherwise
	 */
	function get_theme() {

		// If we've selected a specific theme, return that
		if ( !empty($_COOKIE["wptheme" . COOKIEHASH] ) ) {
			return wp_get_theme( $_COOKIE["wptheme" . COOKIEHASH] );

		// Otherwise, return the designated theme for our device
		} else {

			// If we're using a DMA kiosk...
			if ( BadgeOS_Custom_Authentication::is_ip_whitelisted() )
				return wp_get_theme('dma');

			// Otherwise, we're using any other device...
			else
				return wp_get_theme('dma-portal');
		}
	}

	/**
	 * Store a user's theme selection for 8hrs
	 */
	function set_theme_cookie() {

		// Set our cookie to expire 8 hours from now
		$expire = time() + ( 1000 * 60 * 60 * 8 );

		// If we've set a specific theme via querystring...
		if ( ! empty($_GET["wptheme"] ) ) {
			// Setup our cookie...
			setcookie(
				"wptheme" . COOKIEHASH,
				stripslashes($_GET["wptheme"]),
				$expire,
				COOKIEPATH
			);
			// Redirect back to the current page (without the queryvar set)
			$redirect = remove_query_arg('wptheme');
			wp_redirect($redirect);
			exit;
		}
	}

	/**
	 * Build our theme switcher output, complete with styling
	 *
	 * @since  1.0
	 * @return void
	 */
	function theme_switcher_output() {

		// Only display the selector to logged-in admins.
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
			?>
			<style type="text/css">
				.themeswitcher {
					padding: 0;
					margin: 0;
					text-align: left;
					position: absolute;
					top: 10px;
					right: 3px;
					width: 45%;
				}
				.dma-portal .themeswitcher {
					width: 35%;
				}
				.themeswitcher select {
					position: relative;
					display: block;
					width: 66%;
					height: 80%;
					padding: 2% 2% 1%;
					font-size: 40px;
					line-height: 50px;
					background: transparent url(<?php echo get_stylesheet_directory_uri(); ?>/images/assets.svg) 370px -76px no-repeat;
					margin-bottom: 40px;
					border: 2px solid #8d7c75;
					float: right;
				}
				.dma-portal .themeswitcher select {
					width: 70%;
					font-size: 18px;
					padding: 0 2%;
					height: 48px;
					background-position: 212px -61px;
				}
				.themeswitcher .alignleft {
					padding: 6px 10px 0 0;
					line-height: 36px;
				}
				.dma-portal .themeswitcher .alignleft {
					padding-top: 0;
					margin: 10px 0 0;
				}
			</style>
			<div class="themeswitcher alignright">
				<label for="themeswitcher" class="alignleft">Switch Theme: </label>
				<?php
					// Grab our current active theme
					$current_theme = wp_get_theme()->stylesheet;

					// Build our selector output
					echo '<select name="themeswitcher" id="themeswitcher" onchange="location.href=this.options[this.selectedIndex].value;">'."\n";
					foreach ( $this->available_themes as $theme_dir => $theme_name ) {
						echo '<option value="' . esc_attr( add_query_arg( 'wptheme', $theme_dir ) ) . '"' . selected( $theme_dir, $current_theme, false ) . '>' . esc_html( $theme_name ) . '</option>';
					}
					echo "</select>\n";
				?>
			</div>
			<?php
		}
	}
}
$theme_switcher = new DMA_Theme_Switcher();

add_action('admin_menu', 'badgeos_themeswitcher_menu');

function badgeos_themeswitcher_menu() {
    add_options_page('Friends Themeswitcher', 'Friends Themeswitcher', 'manage_options', 'badgeos-custom-themeswitcher', 'badgeos_themeswitcher_options');
}

function badgeos_themeswitcher_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }   

    $dma_portal_theme = get_option('dma_portal_theme');
    $dma_kiosk_theme = get_option('dma_kiosk_theme');

    if (isset($_POST['dma_portal_theme']) && isset($_POST['dma_kiosk_theme'])) {
        $dma_portal_theme = $_POST['dma_portal_theme'];
        $dma_kiosk_theme = $_POST['dma_kiosk_theme'];
        update_option('dma_portal_theme', $dma_portal_theme);
        update_option('dma_kiosk_theme', $dma_kiosk_theme); 
?>
<div class="updated"><p><strong><?php _e('settings saved.', 'menu-test' ); ?></strong></p></div>
<?php
    }

    $themes = wp_get_themes();
    foreach($themes as $name => $theme) {
        $options[$name] = $theme->name;
    }

?>
<form name="badgeos-settings" method="POST" action="">
    <p> 
        <?php _e("Portal Theme:", 'dma-portal-theme' ); ?>  
        <select name="dma_portal_theme">
            <?php foreach($options as $key => $val) { ?>
                <option value="<?php print $key; ?>" <?php selected($dma_portal_theme, $key); ?>><?php print $val; ?></option>
            <?php } ?>
        </select>
    </p>
    <p> 
        <?php _e("Kiosk Theme:", 'dma-kiosk-theme' ); ?>  
        <select name="dma_kiosk_theme">
            <?php foreach($options as $key => $val) { ?>
                <option value="<?php print $key; ?>" <?php selected($dma_kiosk_theme, $key); ?>><?php print $val; ?></option>
            <?php } ?>
        </select>
    </p>

    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
    </p>
    
</form>
<?php
}


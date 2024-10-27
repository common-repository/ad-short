<?php
/**
* @package Ad Short
* @author Joshua Rodarte
* @version 2.0.1
*/
/*
Plugin Name: Ad Short
Plugin URI: http://how-to-program.xyz
Description: Shortcode for in-post/page adsense ads with minor support for responsive layouts.
Author: Joshua Rodarte
Version: 2.0.1
License: GPL 2
Donate URI: http://how-to-program.xyz/donate
*/

/*	Copyright 2015-2016 Joshua Rodarte

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

defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

class AdShort {
  private $version = '2.0.1';
  public $option_group = 'pluginPage';
  public $option_name = 'ad_short_settings';
  public $section_name = 'ad_short_pluginPage_section';
  private $settings = array(
    'adsense_client_id' => 'Adsense Client ID',
    'responsive_ad_slot' => 'Responsive Ad Slot ID',
    'square_ad_slot' => 'Square',
    'vertical_ad_slot' => 'Vertical Link',
    'horizonatal_ad_slot' => 'Horizontal Link',
    'banner_ad_slot' => 'Banner',
    'mobile_banner_ad_slot' => 'Mobile Banner',
    'large_mobile_banner_ad_slot' => 'Large Mobile Banner'
  );

  public function __construct(  ) {
    // Set up Settings
    $plugin = plugin_basename( __FILE__ );
    add_filter( "plugin_action_links_$plugin", array( $this, 'ad_short_add_settings_link' ) );
    add_action( 'admin_menu', array( $this, 'ad_short_add_admin_menu' ) );
    add_action( 'admin_init', array( $this, 'ad_short_settings_init' ) );

    // Meat of plugin
    add_action( 'wp_enqueue_scripts', array( $this, 'ad_short_scripts' ) );
    add_filter( 'script_loader_tag', array( $this, 'add_defer_attribute' ), 10, 2 );
    add_shortcode( 'ad', array( $this, 'ad_func' ) );

    // MCE editor plugins
    add_action( 'admin_print_footer_scripts', array( $this, 'add_quicktags' ), 100 );
    add_action( 'admin_head', array( $this, 'mce_editor_setup' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'mce_editor_css' ) );
  }


  // Meat and Potatoes
  public function ad_func( $atts ) {
    // Get saved plugin options
    $options = $this->options();
    $class = '';

    // Bring shortcode params over with defaults
    $config = shortcode_atts( array(
      'client' => $options['adsense_client_id'],
      'slot' => $options['responsive_ad_slot'], // responsive
      'type' => 'square',
    ), $atts );

    // Depending on type, assign a class and use appropriate slot id
    switch( $config['type'] ) {
      case 'vlink':
      case '3link':
        $class = 'three-links';
        $config['slot'] = $options['vertical_ad_slot'];
        break;

      case 'hlink':
        $class = 'links-horizontal';
        $config['slot'] = $options['horizonatal_ad_slot'];
        break;

      case 'mbanner':
        $class = 'mobile-banner';
        $config['slot'] = $options['mobile_banner_ad_slot'];
        break;

      case 'lmbanner':
        $class = 'large-mobile-banner';
        $config['slot'] = $options['large_mobile_banner_ad_slot'];
        break;

      case 'banner':
        $class = 'banner';
        $config['slot'] = $options['banner_ad_slot'];
        break;

      case 'square':
        $class = 'square';
        $config['slot'] = $options['square_ad_slot'];
        break;

      case 'msquare':
        $class = 'square above-fold';
        $config['slot'] = $options['square_ad_slot'];
        break;

      default:
        $class = 'square';
        $config['slot'] = $options['responsive_ad_slot'];
        break;
    }

    // Construct output html
    $ad_html =
    '<div class="ad-parent ' . $class . '">
    <ins class="adsbygoogle" data-ad-client="' . $config['client'] . '" data-ad-slot="' . $config['slot'] . '"></ins>
    </div>
    <script>
    (adsbygoogle = window.adsbygoogle || []).push({});
    </script>';

    return $ad_html;
  }

  // Load google ads js and our css
  public function ad_short_scripts(  ) {
    wp_register_script( 'adsbygoogle', '//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js', [], $this->version, false );
    wp_enqueue_script( 'adsbygoogle' );

    wp_register_style( 'ad-short', plugins_url( '/ad_short.css', __FILE__ ), [], $this->version );
    wp_enqueue_style( 'ad-short' );
  }

  // http://matthewhorne.me/add-defer-async-attributes-to-wordpress-scripts/
  // We want the google ads file loaded asyncronously.
  public function add_defer_attribute( $tag, $handle ) {
    if( 'adsbygoogle' !== $handle ) {
      return $tag;
    }

    return str_replace( ' src', ' async src', $tag );
  }

  /*** TinyMCE Additions ***/

  // Add custom css for button in mce editor
  public function mce_editor_css() {
    wp_enqueue_style( 'ad_short_mce', plugins_url( '/mce/button.css', __FILE__ ) );
  }

  // Hook into visual editor
  public function mce_editor_setup() {
    if( get_user_option( 'rich_editing' ) == 'true' ) {
      add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
      add_filter( 'mce_buttons', array( $this, 'register_tinymce_button' ) );
    }
  }

  // Add our tinymce plugin javascript
  public function add_tinymce_plugin( $plugin_array ) {
    $plugin_array['ad_short_button'] = plugins_url( '/mce/button.js', __FILE__ );
    return $plugin_array;
  }

  // Register our tinymce plugin
  public function register_tinymce_button( $buttons ) {
    array_push( $buttons, 'ad_short_button');
    return $buttons;
  }

  // Add Quicktags to tinymce editor text view
  public function add_quicktags() {
    if( wp_script_is( 'quicktags' ) ) {
      ?>
      <script>
        QTags.addButton( 'ad_short_square', 'Square', '[ad type="square"]', '' );
        QTags.addButton( 'ad_short_mobile_square', 'Above Fold Square', '[ad type="msquare"]', '' );
        QTags.addButton( 'ad_short_banner', 'Banner', '[ad type="banner"]', '' );
        QTags.addButton( 'ad_short_mbanner', 'Mobile Banner', '[ad type="mbanner"]', '' );
        QTags.addButton( 'ad_short_lmbanner', 'Large Mobile Banner', '[ad type="lmbanner"]', '' );
        QTags.addButton( 'ad_short_vlink', 'Vertical Links', '[ad type="vlink"]', '' );
        QTags.addButton( 'ad_short_hlink', 'Horizontal Links', '[ad type="hlink"]', '' );
      </script>
      <?php
    }
  }

  /*** Settings API stuff ***/

  // Add a Settings link in plugin page
  public function ad_short_add_settings_link( $links ) {
    $settings_link = '<a href="tools.php?page=ad_short">' . __( 'Settings' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
  }

  // Add Settings link in menu
  public function ad_short_add_admin_menu(  ) {
  	add_management_page( 'Ad Short', 'Ad Short', 'manage_options', 'ad_short', array( $this, 'ad_short_options_page' ) );
  }

  // Register setting group and fields
  public function ad_short_settings_init(  ) {
  	register_setting( $this->option_group, $this->option_name );

  	add_settings_section(
  		$this->section_name,
  		__( 'Ad Short Settings', 'wordpress' ),
  		array( $this, $this->section_name . '_callback' ),
  		$this->option_group
  	);

    foreach( $this->settings as $field => $description ) {
      add_settings_field(
        $field,
        __( $description, 'wordpress' ),
        array( $this, $field . "_render" ),
        $this->option_group,
        $this->section_name
      );
    }
  }

  // Render option fields
  public function adsense_client_id_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[adsense_client_id]' value='<?php echo $this->options()['adsense_client_id']; ?>'>
  	<?php
  }

  public function responsive_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[responsive_ad_slot]' value='<?php echo $this->options()['responsive_ad_slot']; ?>'>
  	<?php
  }

  public function square_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[square_ad_slot]' value='<?php echo $this->options()['square_ad_slot']; ?>'>
  	<?php
  }

  public function vertical_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[vertical_ad_slot]' value='<?php echo $this->options()['vertical_ad_slot']; ?>'>
  	<?php
  }

  public function horizonatal_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[horizonatal_ad_slot]' value='<?php echo $this->options()['horizonatal_ad_slot']; ?>'>
  	<?php
  }

  public function banner_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[banner_ad_slot]' value='<?php echo $this->options()['banner_ad_slot']; ?>'>
  	<?php
  }

  public function mobile_banner_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[mobile_banner_ad_slot]' value='<?php echo $this->options()['mobile_banner_ad_slot']; ?>'>
  	<?php
  }

  public function large_mobile_banner_ad_slot_render(  ) {
  	?>
  	<input type='text' name='ad_short_settings[large_mobile_banner_ad_slot]' value='<?php echo $this->options()['large_mobile_banner_ad_slot']; ?>'>
  	<?php
  }

  // Render setting page description text
  public function ad_short_pluginPage_section_callback(  ) {
  	echo __( 'Enter your Adsense Client ID and ad slot IDs here.', 'wordpress' );
  }

  // Include and render option page template
  public function ad_short_options_page(  ) {
    $this->template( 'options' );
  }

  /*** Misc ***/

  // Helper function for retrieving options
  private function options(  ) {
    return get_option( $this->option_name );
  }

  // Helper function for loading a php template
  private function template( $name ) {
    include plugin_dir_path( __FILE__ ) . 'template/' . $name . '.php';
  }
}

new AdShort();

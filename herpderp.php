<?php
/*
Plugin Name: Herp Derp
Plugin URI: http://www.jwz.org/herpderp/
Version: 1.1
Description: This plugin adds a checkbox to replace the text of your comments with "Herp Derp".
Author: Jamie Zawinski
Author URI: http://www.jwz.org/
*/

/* Copyright © 2012-2015 Jamie Zawinski <jwz@jwz.org>

   Permission to use, copy, modify, distribute, and sell this software and its
   documentation for any purpose is hereby granted without fee, provided that
   the above copyright notice appear in all copies and that both that
   copyright notice and this permission notice appear in supporting
   documentation.  No representations are made about the suitability of this
   software for any purpose.  It is provided "as is" without express or 
   implied warranty.
 
   Inspired by "Herp Derp Youtube Comments" by Tanner Stokes:
   http://www.tannr.com/herp-derp-youtube-comments/

   This version by jwz, created: 13-Dec-2012.
 */


$herpderp_plugin_title     = 'Herp Derp';
$herpderp_plugin_name      = 'herpderp';
$herpderp_prefs_toggle_key = 'herp';
$herpderp_prefs_toggle_id  = "$herpderp_plugin_name-$herpderp_prefs_toggle_key";

add_action ('wp_enqueue_scripts', 'herpderp_init');
function herpderp_init() {  

  // Pass the default value of the prefs checkbox down into JS.
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_key;
  $options = get_option ($herpderp_plugin_name);
  $def = $options[$herpderp_prefs_toggle_key];

  wp_register_script ('herpderp',
                      plugins_url ('herpderp.js' , __FILE__ ),
                      array(), null, true);
  wp_localize_script ('herpderp', 'Derpfault', array('herp' => $def));
  wp_enqueue_script ('herpderp');
}


// Include default styling.  Maybe this should be in the preferences page.
//
add_action ('wp_head', 'herpderp_head');
function herpderp_head() {
?>
<STYLE TYPE="text/css">
 .herpderp { float: right; text-transform: uppercase;
             font-size: 7pt; font-weight: bold; }
</STYLE>
<?
}

// Identify comment text so we can find it later.
// Different themes emit comments differently.
add_filter ('comment_text', herpderp_comment_text, 40);  // after wpautop
function herpderp_comment_text($text) {
  return "<span class='herpc'>$text</span>";
}


/*************************************************************************
 Admin pages
 *************************************************************************/

add_action('admin_menu', 'herpderp_admin_add_page');

function herpderp_admin_add_page() {
  global $herpderp_plugin_title;
  global $herpderp_plugin_name;

  add_options_page ($herpderp_plugin_title . ' Options', $herpderp_plugin_title,
                    'manage_options', $herpderp_plugin_name,
                    'herpderp_options_page');
}


/* Create our preferences page.
 */
function herpderp_options_page() {
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_key;

?>
  <style>
   #wpbody-content p { max-width: 60em; margin-right; 1em; }
  </style>
  <div>
   <h2>Herp Derp</h2>
   <i>By <a href="http://www.jwz.org/">Jamie Zawinski</a></i>

   <p> This plugin herps all the derps.

   <P> This adds a checkbox to your comments page that replaces the text of
   all of the comments with "Herp Derp".  The setting is persistent,
   via a cookie.

   <P> Inspired by
   <A HREF="http://www.tannr.com/herp-derp-youtube-comments/">"Herp Derp
   Youtube Comments"</A>.

   <p>
   <form action="options.php" method="post">
    <?php settings_fields ($herpderp_plugin_name); ?>
    <?php do_settings_sections ($herpderp_plugin_name); ?>
    <p>
    <input name="Submit" type="submit"
           value="<?php esc_attr_e('Save Changes'); ?>" />
   </form>
  </div>
<?
}

/* Add a "Settings" link on the "Plugins" page too, next to "Deactivate".
 */
add_filter ('plugin_action_links', 'herpderp_add_settings_link', 10, 2);

function herpderp_add_settings_link ($links, $file) {
   global $herpderp_plugin_name;
   if ($file == "$herpderp_plugin_name/$herpderp_plugin_name.php" &&
       function_exists ('admin_url')) {
     $link = '<a href="' .
       admin_url ("options-general.php?page=$herpderp_plugin_name") .
       '">' . __('Settings') . '</a>';
     array_unshift ($links, $link);
  }
  return $links;
}


/* Create the preferences fields and hook in to the database.
 */
add_action('admin_init', 'herpderp_admin_init');

function herpderp_admin_init() {
  global $herpderp_plugin_title;
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_id;

  register_setting ($herpderp_plugin_name, $herpderp_plugin_name);

  add_settings_section ($herpderp_plugin_name,
                        $herpderp_plugin_title . ' Settings',
                        'herpderp_section_text', $herpderp_plugin_name);
  add_settings_field ($herpderp_prefs_toggle_id,
                      'Herp Derpify comments by default?',
                      'herpderp_setting_string', $herpderp_plugin_name,
                      $herpderp_plugin_name);
}


function herpderp_section_text() {
}


/* Generates the <input> form element for our preference.
 */
function herpderp_setting_string() {
  global $herpderp_plugin_name;
  global $herpderp_prefs_toggle_key;
  global $herpderp_prefs_toggle_id;

  $options = get_option ($herpderp_plugin_name);
  $def_toggle = $options[$herpderp_prefs_toggle_key];

  echo "<input id='$herpderp_prefs_toggle_id'
             name='" . $herpderp_plugin_name . "[" .
                       $herpderp_prefs_toggle_key . "]'
             type='checkbox' value='herp' " . ($def_toggle ? ' checked' : '') .
       ' />';
}

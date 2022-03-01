<?php
use Vonage\Client;
require __DIR__ . '/vendor/autoload.php';

/*
  Plugin Name: Vonage Wordpress 2FA
  Plugin URI: http://wordpress.org/plugins/hello-wordpress/
  Description: Use Vonage's APIs for 2FA
  Author: James Seconde
  Version: 0.0.1
  Author URI: http://example.org/
*/

$basic = new Client\Credentials\Basic('232130c9', 'mOHPMgmBQBRO8xNB');
$client = new Client(new Client\Credentials\Container($basic));

function db_install()
{
    global $wpdb;
    $tableName = $wpdb->prefix . 'vonage2fa';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $tableName (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      created datetime DEFAULT '0000-00-00 00:00:00' NULL,
      user_id mediumint(9) NOT NULL,
      2fa_key varchar(6) NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
}

function db_uninstall()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'vonage2fa';

    $sql = "DROP TABLE IF EXISTS $table_name";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function vonage_2fa_setup_menu()
{
    add_menu_page(
        'Test Plugin Page',
        'Vonage 2FA',
        'manage_options',
        'vonage_2fa_plugin',
        'load_admin_settings',
        'data:image/svg+xml;base64,' . base64_encode('<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
             width="300.000000pt" height="261.000000pt" viewBox="0 0 300.000000 261.000000"
             preserveAspectRatio="xMidYMid meet">

            <g transform="translate(0.000000,261.000000) scale(0.100000,-0.100000)"
            fill="#000000" stroke="none">
            <path d="M10 2588 c375 -850 841 -1883 850 -1883 6 0 77 146 156 324 l144 324
            -282 628 -283 628 -297 1 -298 0 10 -22z"/>
            <path d="M1975 1662 c-544 -1227 -601 -1341 -747 -1497 -70 -75 -137 -120
            -216 -146 l-57 -18 326 2 325 2 76 38 c141 69 266 231 407 528 47 97 813 1814
            892 1997 l18 42 -302 0 -302 -1 -420 -947z"/>
            </g>
            </svg>
            ')
    );
}

function vonage_2fa_register_settings() {
    register_setting( 'vonage_api_settings_options', 'vonage_api_settings_options', 'vonage_api_settings_options_validate' );
    add_settings_section( 'api_credentials', 'Vonage API Credentials', 'vonage_plugin_text_helper', 'vonage_2fa_plugin' );

    add_settings_field( 'api_credentials_key', 'API Key', 'api_credentials_key', 'vonage_2fa_plugin', 'api_credentials' );
    add_settings_field( 'api_credentials_secret', 'API Secret', 'api_credentials_secret', 'vonage_2fa_plugin', 'api_credentials' );
    add_settings_field( 'api_credentials_phone_number', 'Phone Number', 'api_credentials_phone_number', 'vonage_2fa_plugin', 'api_credentials' );
}

function vonage_api_settings_options_validate()
{
    return true;
}

function vonage_plugin_text_helper() {
    echo '<p>You will need a valid API Key/Secret credentials pair from your Vonage Dashboard, and a working phone number.</p>';
}

function api_credentials_key() {
    $options = get_option( 'vonage_api_settings_options' );
    echo "<input id='api_credentials_key' name='vonage_api_settings_options[api_credentials_key]' type='text' value='" . esc_attr( $options['api_credentials_key'] ) . "' />";
}

function api_credentials_secret() {
    $options = get_option( 'vonage_api_settings_options' );
    echo "<input id='api_credentials_secret' name='vonage_api_settings_options[api_credentials_secret]' type='text' value='" . esc_attr( $options['api_credentials_secret'] ) . "' />";
}

function api_credentials_phone_number() {
    $options = get_option( 'vonage_api_settings_options' );
    echo "<input id='api_credentials_phone_number' name='vonage_api_settings_options[api_credentials_phone_number]' type='text' value='" . esc_attr( $options['api_credentials_phone_number'] ) . "' />";
}

function load_admin_settings()
{
    echo "
    <img src='" . plugin_dir_url(__FILE__) . "assets/logo-large.png' alt='Vonage logo'>
    <h1>Built in 2FA</h1>
    <p>A text is sent out with a 2FA code to the user for each Admin panel login attempt.</p>
    <div>
        <form action='options.php' method='post'>";
            settings_fields('vonage_api_settings_options');
            do_settings_sections('vonage_2fa_plugin');
            submit_button();
    echo "
        </form>
    </div>
    ";
}

add_action('admin_menu', 'vonage_2fa_setup_menu');
register_activation_hook(__FILE__, 'db_install');
register_deactivation_hook(__FILE__, 'db_uninstall');
add_action('admin_init', 'vonage_2fa_register_settings');
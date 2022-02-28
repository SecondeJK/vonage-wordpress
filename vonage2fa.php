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

add_action('admin_menu', 'test_plugin_setup_menu');
register_activation_hook(__FILE__, 'db_install');

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

function test_plugin_setup_menu()
{
    add_menu_page(
        'Test Plugin Page',
        'Vonage 2FA',
        'manage_options',
        'test-plugin',
        'load_admin',
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

function load_admin()
{
    echo "
    <img src='" . plugin_dir_url(__FILE__) . "assets/logo-large.png' alt='Vonage logo'>
    <h1>Built in 2FA</h1>
    <p>A text is sent out with a 2FA code to the user for each Admin panel login attempt.</p>
    <div>
        <h2>Vonage API Keys</h2>
        <form>
            <label for='api_key'>API Key</label>
            <input type='text' name='api_key'/>
            <label for='api_key'>API Secret</label>
            <input type='text' name='api_secret'/>
            <label for='active'>2FA Active</label>
            <input type='checkbox' name='active'/>
            <input type='submit'>
        </form>
    </div>
    ";
}
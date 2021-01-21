<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

/** Define the URL of WordPress */
define( 'WP_HOME', 'http://e-eat.cleverapps.io/' );
define( 'WP_SITEURL', 'http://e-eat.cleverapps.io/' );

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'bligszs4kmiois8ytttm' );

/** MySQL database username */
define( 'DB_USER', 'ujy2cipvmy6g5lnx' );

/** MySQL database password */
define( 'DB_PASSWORD', 't6qny6Ha6ND0kaQIQt49' );

/** MySQL hostname */
define( 'DB_HOST', 'bligszs4kmiois8ytttm-mysql.services.clever-cloud.com' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'ib*!#cmh~/]igF$vMa*1 l}XV/^@=@,-3Hu=-V|Bo9Txd<7q39U.jw$Hh_e@Y{R8' );
define( 'SECURE_AUTH_KEY',  'c|tn2.5*g7G!(3*K!yAmZ=qgU=3yHS)ia<BaBNXJ$dxqc3w~N*Kup^SVxbZ$(Hm2' );
define( 'LOGGED_IN_KEY',    'W,?xX(TNQa0L?MN>;pefuHXXA?b$mSS&d[6;k(qD3#S%*UY|sq4b5.F=]efM.G3(' );
define( 'NONCE_KEY',        '&AN_F)R6RG>c5xbRm 2CGB(2@-8 %jUQ,gDE3Lsss *t*|wC^j^|OC<]zaM6nKS8' );
define( 'AUTH_SALT',        'p.4RACj8{2/**-+Kr]eN<4m?^L?I%yo+LBmTZv/ON{St~wu6@&v;u6k+wRmeCnn(' );
define( 'SECURE_AUTH_SALT', ')1 U:-`zsI_R3TP.CSRoz}o/+FJJ4V(^Zw,BxP$^FqKxE1]=qjf9A|OgQ+=Jpd>n' );
define( 'LOGGED_IN_SALT',   '3y?H2Q!PKG<hdY]@UP;1(e;I&9k3 qPoVQD0gH%@*2~%$;)$pM`5a}5kq;UgXy@r' );
define( 'NONCE_SALT',       'I.[@.dZ}%E r@W W-t#/HG3F3PwN+{`Ycn)0k|tcXas}Ty7+V>jg^h;<;$&V1$.g' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );
ini_set('display_errors','Off');
ini_set('error_reporting', E_ALL );
define('WP_DEBUG_DISPLAY', false);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

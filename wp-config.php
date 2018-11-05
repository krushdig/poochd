<?php

define('FS_METHOD', 'direct');

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
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'krushdig_wp6' );

/** MySQL database username */
define( 'DB_USER', 'krushdig_wp6' );

/** MySQL database password */
define( 'DB_PASSWORD', 'S7*u72pwLB=x' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'od_MpBDMuU&&Z>u{4!I+256c-gnfaZc_QobOQ[->0@+F<<|iE<UR).UcxP.9A$)|');
define('SECURE_AUTH_KEY',  'CQ-7s4ok8tDuT5Ivc!I?k~B3HYAJ_x|Z]gqS~!+^Lf}NK%w@;N+x,Vq|Vfn+/Y$o');
define('LOGGED_IN_KEY',    '=a7VvgWx+Iyq EX:sm~Lt=<V}{u1&*+sy.wLYsmYkw||87U2T{AJ[b(/K 7*3!+P');
define('NONCE_KEY',        'f03Y-r{4Wd,GFu.82Yi^S%%DLkK?=GAr.bt-l?hxuoR9daA]3%9T]#`sH0sHE[: ');
define('AUTH_SALT',        '4KhwGJ}?{:fJ)&(t5n6r%=j_9X>BCf6da ApRI}(zN7hoo-9LvFwl(+`6YN^0zK[');
define('SECURE_AUTH_SALT', '#T[ |Y-!2Lp3!gd}o|y_(.%d&)a+bL$;}%8Q%8<&IMW=^i7/5m$|d9hV4ozg@+dz');
define('LOGGED_IN_SALT',   'A-r|rS2#iNYrI(5/{AcU0S%)VVHY^O^/U$p5J8I9-&oW-(WH}xJn*==y6[+~W~-;');
define('NONCE_SALT',       'w&6M:z.Q*8CEL<Re_n/.|}B9DVus%jXC2b+_G,.)Gn=5et-S;rg&tfPpQ(Z0EmGc');


/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'BisdsHzT';




/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

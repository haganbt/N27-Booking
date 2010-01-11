<?
  // Define Script Setup

  // Define our webserver variables
  // FS = Filesystem (physical)
  // WS = Webserver (virtual)

  // Name of this site
  define('SITE_NAME', 'My Booking Website') ;

  // Current Script Name
  define('SCRIPT_NAME',substr($_SERVER['PHP_SELF'],strrpos($_SERVER['PHP_SELF'],"/")+1));
  // Current Script WS Path
  define('SCRIPT_PATH', $_SERVER['PHP_SELF']);

  // You can setup testing server variables for a testing machine, such as Win32.
  if (preg_match("/^[CDEFGHIJKL]{1}:/i", $_SERVER['DOCUMENT_ROOT'])) {
	define('TESTING_SERVER', 1); // Set to '1' to use settings for testing server
	define('HTTP_SERVER', 'http://127.0.0.1');  // testing server
	define('DIR_FS_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '');
  } else {
	define('TESTING_SERVER', 0); // Web Server
	define('HTTP_SERVER', '');
	define('DIR_FS_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . '/test/');
  }
  define('DOMAIN_NAME', 'http://www.yourdomain.com/');
  define('HTTPS', false);

  // WS = Webserver (virtual)
  define('DIR_WS_SCRIPTS', '/test/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_WIDGETS', DIR_WS_INCLUDES . 'widgets/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_DATA', DIR_WS_INCLUDES . 'data/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');

  //the dir to upload the file to on the server
  //this dir must be writiable by php (chmod 757 ought to do it)
  define('DIR_WS_ATTACHMENTS', 'attachments/' );
  
  // FS = Filesystem (physical)
  define('DIR_FS_INCLUDES', DIR_FS_DOCUMENT_ROOT . 'includes/');
  define('DIR_FS_LOGS', DIR_FS_INCLUDES . 'logs/');
  define('DIR_FS_DATA', DIR_FS_INCLUDES . 'data/');
  define('DIR_FS_SESSIONS', DIR_FS_INCLUDES . 'sessions/');
  define('DIR_FS_ATTACHMENTS', DIR_FS_DOCUMENT_ROOT . 'attachments/' );

  // Filenames for the Project
  define('FILENAME_LOGIN', 'user_login.php');
  define('FILENAME_LOGOUT', 'user_logout.php');
  define('FILENAME_REGISTER', 'user_register.php');
  define('FILENAME_UPDATE', 'user_update.php');
  define('FILENAME_FORGOT_USERNAME', 'user_forgot_username.php');
  define('FILENAME_CHANGE_PASSWD', 'user_change_passwd.php');
  define('FILENAME_FORGOT_PASSWD', 'user_forgot_passwd.php');
  define('FILENAME_HELP', 'user_help.php');
  define('FILENAME_MONTH_VIEW', 'month_view.php');
  define('FILENAME_WEEK_VIEW', 'week_view.php');
  define('FILENAME_DAY_VIEW', 'day_view.php');
  define('FILENAME_DETAILS_VIEW', 'details_view.php');
  define('FILENAME_ADD_EVENT', 'add_event.php');
  define('FILENAME_DEFAULT_VIEW', 'month_view.php');
  define('FILENAME_ADMIN_REGISTER', 'admin_user_register.php');
  define('FILENAME_ADMIN_PRIVILEGES', 'admin_user_privileges.php');
  define('FILENAME_ADMIN_UPDATE', 'admin_user_update.php');
  define('FILENAME_ADMIN_MAX_BOOKINGS', 'admin_limit_user_bookings.php');
  define('FILENAME_ADMIN_BLOCK_BOOKING', 'admin_block_booking.php');
  define('FILENAME_SITE_ADMIN', 'site_admin.php');
  define('FILENAME_ADMIN_BOOKING_CREDITS', 'admin_set_booking_credits.php');
  define('FILENAME_ADMIN_BOOKING_OPTIONS', 'admin_set_booking_options.php');
  define('FILENAME_ADMIN_EMAIL_OPTIONS', 'admin_set_email_options.php');
  define('FILENAME_MY_BOOKWAKE_VIEW', 'my_bookings.php');
  define('FILENAME_ADMIN_MODIFY_GROUPS', 'admin_modify_groups.php');
  define('FILENAME_ADMIN_MODIFY_USER_GROUPS', 'admin_modify_user_groups.php');
  define('FILENAME_ADMIN_EMAIL_MAILSHOT', 'admin_email_mailshot.php');
  define('FILENAME_ADMIN_MAILSHOT_TYPE', 'admin_mailshot_type.php');
  define('FILENAME_BUDDY_LIST', 'user_buddylist.php');
  define('FILENAME_ADMIN_BUDDY_OPTIONS', 'admin_set_buddy_options.php');
  define('FILENAME_ADMIN_PAYMENT_GATEWAY', 'admin_payment_gateway.php');
  define('FILENAME_ADMIN_PAYPAL_TRANSACTIONS', 'admin_paypal_transactions.php');  
  define('FILENAME_BUY_CREDITS', 'user_buy_credits.php');
  define('FILENAME_ADMIN_MODIFY_PRODUCTS', 'admin_modify_products.php');
  define('FILENAME_ADMIN_MODIFY_GROUP_PRODUCTS', 'admin_modify_group_products.php');
  
  
  if (SCRIPT_NAME != FILENAME_MONTH_VIEW && SCRIPT_NAME != FILENAME_WEEK_VIEW &&
	SCRIPT_NAME != FILENAME_DAY_VIEW && SCRIPT_NAME != FILENAME_ADD_EVENT) {
		define('NAV_SCRIPT_NAME', FILENAME_DEFAULT_VIEW."");
  } else {
		define('NAV_SCRIPT_NAME', SCRIPT_NAME."");
  }

// INCLUDE PATH CHANGE
// Extend the php INCLUDE_PATH
// Use ";" for Windows, and ":" for Unix!
  if (TESTING_SERVER) { $del = ';'; } else { $del = ':'; }
  ini_set("include_path", "" .
	DIR_FS_DOCUMENT_ROOT . $del .
	DIR_FS_DOCUMENT_ROOT . DIR_WS_INCLUDES . $del .
	DIR_FS_DOCUMENT_ROOT . DIR_WS_FUNCTIONS . $del .
	DIR_FS_DOCUMENT_ROOT . DIR_WS_WIDGETS . $del .
	ini_get("include_path")
	);

// INI Register Globals, Security Issue
  ini_set('register_globals', "0");
// Use SuperGlobals instead!


  // If enabled, the parse time will not store its time after the
  // header(location) redirect. Used with the wrap_exit() function.
  define('EXIT_AFTER_REDIRECT', 1);
  define('STORE_PAGE_PARSE_TIME', 0); // store the time it takes to parse a page
  define('STORE_PAGE_PARSE_TIME_LOG', DIR_FS_LOGS . 'parse_time_log.txt');
  define('STORE_PARSE_DATE_TIME_FORMAT', '%d/%m/%Y %H:%M:%S');
  if (STORE_PAGE_PARSE_TIME == '1') { $parse_start_time = microtime(); }
  define('STORE_DB_TRANSACTIONS', 0);

// MAIL SETTINGS
// Rewrite Mail Settings for Windows
// Note, the mail settings have to be specific for the server!
  if (TESTING_SERVER) {
		ini_set('SMTP', 'smtp.yourdomain.com');
		ini_set('sendmail_from', 'username@yourdomain.com');
        define ('MAIL_MYNAME', 'Booking Calendar');
        define ('MAIL_MYEMAIL', 'username@yourdomain.com');
  } else { // Web Server
        define ('MAIL_MYNAME', 'Booking Calendar');
        define ('MAIL_MYEMAIL', 'noreply@yourdomain.com');
  }
// set to "1" if extended email check function should be used
// If you're testing locally and your webserver has no possibility to query
// a dns server you should set this to "0"!
  define('ENTRY_EMAIL_ADDRESS_CHECK', 0);

// define our database connection
  if (TESTING_SERVER) {
	define('DB_SERVER', 'localhost');
	define('DB_SERVER_USERNAME', 'username');
	define('DB_SERVER_PASSWORD', 'password');
	define('DB_DATABASE', '');
	define('USE_PCONNECT', 0);
  } else { // Web Server
	define('DB_SERVER', 'localhost');
	define('DB_SERVER_USERNAME', 'user');
	define('DB_SERVER_PASSWORD', 'password');
	define('DB_DATABASE', 'database');
	define('USE_PCONNECT', 0);
  }
// define the database tables
  define('DATE_TIME_SCHEDULE_TABLE', 'booking_schedule');
  define('BOOKING_EVENT_TABLE', 'booking_event');
  define('BOOKING_USER_TABLE', 'booking_user');
  define('SETTINGS_TABLE', 'booking_admin_functions');
  define('BOOKING_OPTIONS_TABLE', 'booking_options');
  define('BOOKING_EVENT_OPTIONS_TABLE', 'booking_event_options');
  define('BOOKING_USER_OPTIONS_TABLE', 'booking_user_options');
  define('BOOKING_USER_GROUPS_TABLE', 'booking_user_groups');
  define('BOOKING_GROUPS_TABLE', 'booking_groups');
  define('EMAILSHOT_SENT_TO_GROUPS', 'booking_contact_sent_email_groups');
  define('EMAILSHOT_SENT_TO_USERS', 'booking_contact_sent_email_users');
  define('EMAILSHOT_SENT_EMAILS', 'booking_contact_sent_emails');
  define('EMAILSHOT_ATTACHMENTS', 'booking_attachments');
  define('EMAILSHOT_ATTACHMENTS_TEMP', 'booking_attachments_temp');
  define('BOOKING_BUDDIES', 'booking_user_buddies');
  define('BOOKING_BUDDIES_PENDING', 'booking_user_buddies_pending');
  define('BOOKING_CREDIT_TYPES', 'booking_credit_types');
  define('BOOKING_PRODUCT_ITEM', 'booking_product_item');
  define('PAYPAL_TRANSACTIONS', 'booking_paypal_transactions');  
  define('BOOKING_PRODUCT_GROUPS', 'booking_product_groups');  
  
// include the database functions
  include_once(DIR_WS_FUNCTIONS . 'database.php');
// make a connection to the database... now
  wrap_db_connect() or die('Unable to connect to database server!');

// some code to solve compatibility issues (magic quotes)
  include_once(DIR_WS_FUNCTIONS . 'compatibility.php');

// SESSION INI CHANGES

// Change the session name for security purposes.
  ini_set("session.name", "SID");
// Do not pass the session id thru the URL, use cookies only.
  ini_set("session.use_trans_sid", 0);
// Change the session save path because we could be on a shared server.
  ini_set("session.save_path", DIR_FS_SESSIONS);
// Use only cookies for session management.
  ini_set("session.use_only_cookies", 1);
// define how the session functions will be used
  include_once(DIR_WS_FUNCTIONS . 'sessions.php');

// lets start our session
  if (@!SID && $_REQUEST[wrap_session_name()]) {
    wrap_session_id( $_REQUEST[wrap_session_name()] );
  }
  wrap_session_start();
  if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, DIR_WS_SCRIPTS);
  }

// define our general functions used application-wide
  include_once(DIR_WS_FUNCTIONS . 'booking_db_fns.php');
  include_once(DIR_WS_FUNCTIONS . 'calendar_fns.php');
  include_once(DIR_WS_FUNCTIONS . 'common_fns.php');
  include_once(DIR_WS_FUNCTIONS . 'general.php');
  include_once(DIR_WS_FUNCTIONS . 'validations.php');

// Include the user and password crypto functions
  include_once(DIR_WS_FUNCTIONS . 'user_auth_fns.php');
  include_once(DIR_WS_FUNCTIONS . 'password_funcs.php');

// Set the Error Reporting to show critical errors and warnings.
  error_reporting(E_ERROR | E_WARNING);



// BOOKING CALENDAR SETTINGS:

  // The Time Interval for the Booking Calendar must stay constant after the
  // calendar is in use, so choose wisely! It is also recommended not to change
  // the max and min hour after the script is in use!
  //
   // These must remain constant after the Booking Calendar is in use!
  define('BOOKING_TIME_INTERVAL', '15'); // 15, 30 or 60 minutes (Recommended: 30 or 60 mins)
  define('MIN_BOOKING_HOUR', '09');   // 00-24 hours  (including this hour)
  define('MAX_BOOKING_HOUR', '22');  // 20 = 8 PM    (NOT including this hour)
  // Adjustable Settings
  define('WEEK_START', '1'); // Weekday Start ('0' for Sunday or '1' for Monday)
  define('DEFINE_AM_PM', true); // Set to 'true' for AM PM display.
  define('DISPLAY_TIME_INTERVAL', '15'); // 15, 30 or 60 minutes (Recommended: 30 or 60 mins)
  define('PAGE_REFRESH', '180');  // 30->up, number of seconds between page refreshes (Day/Week/Month Views)
  // Location (Conference Rooms) Variables
  // Associative DB Field Naming Array (key => db_field)
  $location_db_name = array (
			'loc1' => 'event_id_location_1',
			'loc2' => 'event_id_location_2',
			);
  // Associative Display Array (key => display_text)
  $location_display = array (
			'loc1' => 'Location 1',
			'loc2' => 'Location 2',
			);
  // Default Location Name Index
  define('DEFAULT_LOCATION_NAME', 'loc1');
  if (@$_REQUEST['loc'] == '') { $_REQUEST['loc'] = DEFAULT_LOCATION_NAME; }
  // Default View
  define('DEFAULT_VIEW', 'month');
  if (@$_REQUEST['view'] == '') { $_REQUEST['view'] = DEFAULT_VIEW; }
  
 	// Number of days of schedule data to keep from now
  	define('PURGE_TABLE_SCHEDULE_DAYS', '365');

  // Provide override to flags so that admin users can do things normal users can't.
  $flagDefault = true ;
  if (wrap_session_is_registered("admin_user")) {
    $flagDefault = false ;
  }
  // Require Username/Password Authentication Settings
  define('REQUIRE_AUTH_FOR_ADDING_FLAG', $flagDefault);
  define('REQUIRE_AUTH_FOR_MODIFYING_FLAG', $flagDefault);
  define('REQUIRE_AUTH_FOR_DELETING_FLAG', $flagDefault);
  define('REQUIRE_AUTH_FOR_VIEWING_DETAILS_FLAG', false);
  // Required User Settings
  define('REQUIRE_MATCHING_USERNAME_FOR_MODIFICATIONS_FLAG', $flagDefault);
  define('REQUIRE_MATCHING_USERNAME_FOR_DELETIONS_FLAG', $flagDefault);

  // Create the needed date constants for the current & selected dates.
  include_once('define_time_constants.php');

  // Load in the session variables
  include_once('session_variables.php');
?>
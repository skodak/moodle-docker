<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

ini_set('error_log', '/var/www/php_error.log');

$CFG->site_is_public = false;

$CFG->dbtype    = getenv('MOODLE_DOCKER_DBTYPE');
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbname    = getenv('MOODLE_DOCKER_DBNAME');
$CFG->dbuser    = getenv('MOODLE_DOCKER_DBUSER');
$CFG->dbpass    = getenv('MOODLE_DOCKER_DBPASS');
$CFG->prefix    = 'm_';
$CFG->dboptions = ['dbcollation' => getenv('MOODLE_DOCKER_DBCOLLATION')];

if (getenv('MOODLE_DOCKER_DBTYPE') === 'sqlsrv') {
    $CFG->dboptions['extrainfo'] = [
        // Disable Encryption for now on sqlsrv.
        // It is on by default from msodbcsql18.
        'Encrypt' => false,
    ];
}

$CFG->wwwroot   = 'https://webserver.' . getenv('COMPOSE_PROJECT_NAME') . '.orb.local';
$CFG->sslproxy  = true;
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';
$CFG->directorypermissions = 0777;
$CFG->smtphosts = 'mailpit:1025';
$CFG->noreplyaddress = 'noreply@example.com';

if (getenv('COMPOSE_PROJECT_NAME')) {
    // Allow coexisting of sites on different ports.
    $CFG->sessioncookie = getenv('COMPOSE_PROJECT_NAME');
}

// Debug options - possible to be controlled by flag in future..
$CFG->debug = (E_ALL | E_STRICT); // DEBUG_DEVELOPER
$CFG->debugdisplay = 1;
//$CFG->debugstringids = 1; // Add strings=1 to url to get string ids.
//$CFG->perfdebug = 15;
//$CFG->debugpageinfo = 1;
$CFG->allowthemechangeonurl = 1;
$CFG->passwordpolicy = 0;
$CFG->cronclionly = 0;
$CFG->pathtophp = '/usr/local/bin/php';

$CFG->phpunit_dataroot  = '/var/www/phpunitdata';
$CFG->phpunit_prefix = 't_';
define('TEST_EXTERNAL_FILES_HTTP_URL', 'http://exttests:9000');
define('TEST_EXTERNAL_FILES_HTTPS_URL', 'http://exttests:9000');

$CFG->behat_wwwroot   = 'http://webserver';
$CFG->behat_dataroot  = '/var/www/behatdata';
$CFG->behat_prefix = 'b_';
if (getenv('MOODLE_DOCKER_BROWSER') === 'chromium' || getenv('MOODLE_DOCKER_BROWSER') === 'chrome') {
    $CFG->behat_profiles = array(
        'default' => array(
            'browser' => 'chrome',
            'wd_host' => 'http://selenium:4444/wd/hub',
            'capabilities' => [
                'extra_capabilities' => [
                    'chromeOptions' => [
                        'args' => [
                            'no-sandbox',
                            //'headless=new',
                            //'no-gpu',
                            'disable-dev-shm-usage',
                            'remote-debugging-port=9222', // Do not change, this is redirected to 9229 to allow non-localhost access.
                        ],
                    ],
                ],
            ],
        ),
    );
} else {
    $CFG->behat_profiles = array(
        'default' => array(
            'browser' => getenv('MOODLE_DOCKER_BROWSER'),
            'wd_host' => 'http://selenium:4444/wd/hub',
        ),
    );
}
$CFG->behat_faildump_path = '/var/www/behatfaildumps';

define('PHPUNIT_LONGTEST', true);

if (getenv('MOODLE_DOCKER_PHPUNIT_EXTRAS')) {
    define('TEST_SEARCH_SOLR_HOSTNAME', 'solr');
    define('TEST_SEARCH_SOLR_INDEXNAME', 'test');
    define('TEST_SEARCH_SOLR_PORT', 8983);

    define('TEST_SESSION_REDIS_HOST', 'redis');
    define('TEST_CACHESTORE_REDIS_TESTSERVERS', 'redis');

    define('TEST_CACHESTORE_MONGODB_TESTSERVER', 'mongodb://mongo:27017');

    define('TEST_CACHESTORE_MEMCACHED_TESTSERVERS', "memcached0:11211\nmemcached1:11211");
    define('TEST_CACHESTORE_MEMCACHE_TESTSERVERS', "memcached0:11211\nmemcached1:11211");

    define('TEST_LDAPLIB_HOST_URL', 'ldap://ldap');
    define('TEST_LDAPLIB_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_LDAPLIB_BIND_PW', 'password');
    define('TEST_LDAPLIB_DOMAIN', 'ou=Users,dc=openstack,dc=org');

    define('TEST_AUTH_LDAP_HOST_URL', 'ldap://ldap');
    define('TEST_AUTH_LDAP_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_AUTH_LDAP_BIND_PW', 'password');
    define('TEST_AUTH_LDAP_DOMAIN', 'ou=Users,dc=openstack,dc=org');

    define('TEST_ENROL_LDAP_HOST_URL', 'ldap://ldap');
    define('TEST_ENROL_LDAP_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_ENROL_LDAP_BIND_PW', 'password');
    define('TEST_ENROL_LDAP_DOMAIN', 'ou=Users,dc=openstack,dc=org');
}

if (getenv('MOODLE_DOCKER_BBB_MOCK')) {
    if (property_exists($CFG, 'behat_wwwroot')) {
        $mockhash = sha1($CFG->behat_wwwroot);
    } else {
        $mockhash = sha1($CFG->wwwroot);
    }
    define('TEST_MOD_BIGBLUEBUTTONBN_MOCK_SERVER', "http://bbbmock/{$mockhash}");
}

if (__FILE__ === '/var/www/config-docker.php') {
    require_once(__DIR__ . '/html/lib/setup.php');
} else {
    require_once(__DIR__ . '/lib/setup.php');
}

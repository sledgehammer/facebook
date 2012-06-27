<?php
/**
 * Facebook
 */
namespace Sledgehammer;
/**
 * Helper for using the Facebook Open Graph API or executing FQL
 * @link https://developers.facebook.com/docs/reference/api/
 * @link https://developers.facebook.com/docs/reference/fql/
 */
class Facebook extends \BaseFacebook {

	/**
	 * Total number of API requests
	 * @var int
	 */
	public $requestCount = 0;

	/**
	 * Total time it took to execute all API calls (in seconds)
	 * @var float
	 */
	public $executionTime = 0;

	/**
	 * (Re)connect when the access token
	 * @var bool
	 */
	public $autoConnect = true;

	/**
	 * Scope with persimssion the app requires.
	 * @var array|string
	 */
	public $autoConnectPermissions = array();

	/**
	 * All logged facebook requests.
	 * @var array
	 */
	public $log = array();

	/**
	 * Maximum number of request that will be logged.
	 * @var int
	 */
	public $logLimit = 50;

	/**
	 * The default limit for paged results retrieved via Facebook->all().
	 * 10 seems low, but with 1 a 2 sec per api call, it already takes 20+ sec.
	 * @var int
	 */
	public $defaultPagerLimit = 10;

	/**
	 * Facebook singleton
	 * @var Facebook
	 */
	private static $instance;

	/**
	 * Current user (singleton)
	 * @var FacebookUser
	 */
	private static $me;

	/**
	 * Facebook application (singleton)
	 * @var GraphObject
	 */
	private static $application;

	/**
	 * Use Facebook::configure() to initialize the facebook instance.
	 *
	 * @param string $appId
	 * @param string $appSecret
	 * @param array $options
	 */
	function __construct($appId, $appSecret, $options = array()) {
		// Session autostart.
		if (session_id() == false) {
			session_start();
		}
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$state = $this->getPersistentData('state');
		if (!empty($state)) {
			$this->state = $state;
		}
		foreach ($options as $property => $value) {
			$this->$property = $value;
		}
	}

	/**
	 * Set up a Facebook connection.
	 * Might cause a redirect which ends the current script.
	 *
	 * @param array $parameters  List with optional parameters
	 *   'display' => 'popup'
	 *   'scope' => array('email','read_stream', etc) @link https://developers.facebook.com/docs/authentication/permissions/
	 *   'redirect_url'> callback url
	 * @return true
	 */
	function connect($permissions = array(), $parameters = array()) {
		$this->autoConnect = false;
		if (isset($_GET['error']) || isset($_GET['error_reason'])) {
			throw new \Exception($_GET['error_description']);
		}
		if (is_string($permissions)) {
			$permissions = explode(',', $permissions);
		}
		$accessToken = false;
		if (isset($_GET['code'])) {
			$accessToken = $this->getAccessToken(); // Retrieves accesstoken and calls setPersistentData()
		} elseif (isset($_POST['signed_request'])) {
			if ($this->getUser() != 0) {
				$accessToken = $this->getAccessToken(); // Retrieves accesstoken and calls setPersistentData()
			}
		}
		if (count($permissions) > 0 && $accessToken) {
			// Validate permissions
			$acceptedPermissions = $this->getPermissions();
			foreach ($permissions as $permission) {
				if (in_array($permission, $acceptedPermissions) === false) {
					$this->clearAllPersistentData();
					throw new \Exception('Permission to "'.$permission.'" was denied');
				}
			}
			return true;
		}
		$parameters['scope'] = implode(',', $permissions);
		$this->clearAllPersistentData();
		redirect($this->getLoginUrl($parameters));
	}

	/**
	 * Check if the accessToken is active/known.
	 * @return bool
	 */
	function isConnected() {
		// @todo Real status check/request?
		// @todo expire ts controleren?
		if ($this->getPersistentData('access_token')) {
			if ($this->getUser() == 0) {
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Make an API call.
	 *
	 * @throws Exceptions on failure
	 * @return mixed response
	 */
	function api(/* polymorphic */) {
		$arguments = func_get_args();
		// Normalize fields parameter.
		if (isset($arguments[2]['fields']) && is_array($arguments[2]['fields'])) {
			$arguments[2]['fields'] = implode(',', $arguments[2]['fields']);
		}
		// Check cache
		if (isset($arguments[2]['local_cache']) && $arguments[2]['local_cache']) { // Enable caching for the request?
			$cache = sha1(json_encode($arguments));
			if (isset($_SESSION['__Facebook__']['cache'][$cache])) {
				return $_SESSION['__Facebook__']['cache'][$cache]; // Cache hit
			}
			unset($arguments[2]['local_cache']);
		}
		// Execute the Facebook API call.
		$start = microtime(true);
		$this->requestCount++;
		try {
			$response = call_user_func_array('parent::api', $arguments);
			$executionTime = (microtime(true) - $start);
		} catch (\FacebookApiException $e) {
			// Detect if the error was caused by an invalid accessToken, and (re)connect
			if ($this->autoConnect == false || $_SERVER['REQUEST_METHOD'] != 'GET') {
				throw $e;
			}
			$messages = array(
				'An active access token must be used to query information about the current user\.', // Not logged in.
				'Error validating access token: User [0-9]+ has not authorized application [0-9]+\.' // Was logged in, but user uninstalled the application.
				// @todo timeout
			);
			$invalidAccessToken = false;
			$errorMessage = $e->getMessage();
			foreach ($messages as $message) {
				if (preg_match('/^'.$message.'$/', $errorMessage)) {
					$invalidAccessToken = true;
					break;
				}
			}
			if ($invalidAccessToken === false) { // Not a connection error?
				throw $e;
			}
			if ($this->connect($this->autoConnectPermissions)) {
				// Automatic (re)connect was successful, retry api call.
				$start = microtime(true);
				$response = call_user_func_array('parent::api', $arguments);
			} else {
				throw $e;
			}
		}
		$this->executionTime += $executionTime;
		// Log request
		if ($this->requestCount < $this->logLimit) {
			$this->log[] = array(
				'request' => $arguments,
				'exectutionTime' => $executionTime,
			);
		}
		if (isset($cache)) {
			$_SESSION['__Facebook__']['cache'][$cache] = $response;
		}
		return $response;
	}

	/**
	 * Get the permissions/scope of the connection.
	 * @return array
	 */
	function getPermissions() {
		$response = $this->api('me/permissions', 'GET', array('local_cache' => true));
		$permissions = array();
		foreach ($response['data'][0] as $permission => $enabled) {
			if ($enabled) {
				$permissions[] = $permission;
			}
		}
		return $permissions;
	}

	/**
	 * Set the Application ID.
	 *
	 * @param string $appId The Application ID
	 * @return BaseFacebook
	 */
	function setAppId($appId) {
		if ($this->appId !== $appId) {
			$this->clearCache();
		}
		$this->appId = $appId;
		return $this;
	}

	/**
	 * Sets the access token for api calls.  Use this if you get
	 * your access token by other means and just want the SDK
	 * to use it.
	 *
	 * @param string $accessToken an access token.
	 * @return BaseFacebook
	 */
	function setAccessToken($accessToken) {
		if ($accessToken !== $this->getPersistentData('access_token')) {
			$this->clearCache();
		}
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * Configure the global Facebook instance.
	 *
	 * @param string $appId
	 * @param string $appSecret
	 * @param string|array $permissions Scope of the application.
	 * @param array $options optional settings: array(
	 *  'fileUploadSupport' => bool,
	 *  'autoConnect' => bool,
	 *  'logLimit' => int,
	 *  'defaultPagerLimit' => int
	 *  'signedRequest' => string
	 *  'accessToken' => string,
	 * )
	 * @return void
	 */
	static function configure($appId, $appSecret, $permissions = array(), $options = array()) {
		$options['autoConnectPermissions'] = $permissions;
		self::$instance = new Facebook($appId, $appSecret, $options);
	}

	/**
	 * Returns the Facebook instance.
	 *
	 * @return Facebook
	 */
	static function getInstance() {
		if (self::$instance === null) {
			throw new InfoException('Facebook AppID was not configured', 'Use Facebook::configure($appId, $appSecret); to configure your AppID.');
		}
		return self::$instance;
	}

	/**
	 * Current user (singleton).
	 *
	 * @return FacebookUser
	 */
	static function me() {
		if (self::$me === null) {
			self::$me = new FacebookUser(self::getInstance()->getPersistentData('user_id', 'me'), null, true);
		}
		return self::$me;
	}

	/**
	 * Current application (singleton)
	 *
	 * @return FacebookUser
	 */
	static function application() {
		if (self::$application === null) {
			self::$application = new GraphObject(self::getInstance()->getAppId(), array('local_cache' => true), true);
		}
		return self::$application;
	}

	/**
	 * Short notation for the api GET requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	static function get($path, $parameters = array()) {
		return self::getInstance()->api($path, 'GET', $parameters);
	}

	/**
	 * Retrieve data via an FQL (Facebook Query Language) query
	 * @link https://developers.facebook.com/docs/reference/fql/
	 *
	 * @param string $fql FQL Query
	 * @param array $options  array('local_cache' => bool)
	 * @return array
	 */
	static function query($fql, $options = array()) {
		$request = array(
			'method' => 'fql.query',
			'query' => $fql,
			'callback' => ''
		);
		if (array_value($options, 'local_cache')) {
			if (empty($_SESSION['__Facebook__']['cache'][$fql])) { // // Cache miss?
				$_SESSION['__Facebook__']['cache'][$fql] = self::getInstance()->api($request);
			}
			return $_SESSION['__Facebook__']['cache'][$fql];
		}
		return self::getInstance()->api($request);
	}

	/**
	 * Fetch all pages in a paginated result.
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return array
	 */
	static function all($path, $parameters = array(), $pagerLimit = null) {
		$facebook = self::getInstance();
		if (isset($parameters['limit']) || isset($parameters['offset'])) { // The request is for a specific page
			return $facebook->api($path, 'GET', $parameters);
		}
		if ($pagerLimit === null) {
			$pagerLimit = $facebook->defaultPagerLimit = 10;
		}
		$page = 0;
		$pages = array();
		$url = new URL($path);
		$url->query = $parameters;
		while (true) {
			if ($page > $pagerLimit) {
				notice('Maximum pager limit ('.$pagerLimit.') was reached');
				break;
			}
			$response = $facebook->api($url->path, 'GET', $url->query); // fetch page
			if ($page === 1 && $pages[0] === $response['data']) { // Does page 2 have identical results as page 1?
				// Bug/loop detected in facebook's pagin.
				// Example loop: /$friend_id/mutualfriends/$me_id
				return $response['data']; // return a single.
			}
			$pages[$page] = $response['data'];
			if (empty($response['paging']['next']) == false) {
				$url = new URL($response['paging']['next']);
				if (isset($url->query['limit']) && ((count($response['data']) / $url->query['limit']) < 0.10)) { // This page has less than 10% results of the limit?
					// 90+% is filtered out or there is an error/loop in facebooks paging
					// Example empty 2nd page: /me/friends
					// Example loop: /$friend_id/mutualfriends/$me_id
					break; // Assumme facebook loop/empty second page.
				}
			} else {
				// no more pages
				break;
			}
			$page++;
		}
		$data = array();
		foreach ($pages as $page) {
			$data = array_merge($data, $page);
		}
		return $data;
	}

	/**
	 * Short notation for the api POST requests
	 *
	 * @param string $path
	 * @param array|GraphObject $data
	 * @return mixed
	 */
	static function post($path, $data = array()) {
		if (is_object($data)) {
			$data = get_public_vars($data);
			foreach ($data as $field => $value) {
				if ($value instanceof Collection) {
					unset($data[$field]);
				}
			}
		}
		return self::getInstance()->api($path, 'POST', $data);
	}

	/**
	 * Short notation for the api DELETE requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	static function delete($path, $parameters = array()) {
		return self::getInstance()->api($path, 'DELETE', $parameters);
	}

	protected function clearCache() {
		self::$me = null;
		self::$application = null;
		$this->user = null;
		$this->clearPersistentData('cache');
	}

	protected function clearAllPersistentData() {
		unset($_SESSION['__Facebook__']);
		self::$me = null;
		self::$application = null;
	}

	protected function clearPersistentData($key) {
		unset($_SESSION['__Facebook__'][$key]);
	}

	protected function getPersistentData($key, $default = false) {
		if (isset($_SESSION['__Facebook__'][$key])) {
			return $_SESSION['__Facebook__'][$key];
		}
		return $default;
	}

	protected function setPersistentData($key, $value) {
		$_SESSION['__Facebook__'][$key] = $value;
		return $this;
	}

}

?>

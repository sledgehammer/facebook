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
	 * @var int
	 */
	private $defaultPageLimit = 10;

	/**
	 * Global facebook instance
	 * @var Facebook
	 */
	private static $instance;

	/**
	 * Returns the Facebook instance.
	 * Remember to set the AppId.
	 *
	 * @return Facebook
	 */
	static function getInstance() {
		if (Facebook::$instance === null) {
			// Session autostart.
			if (session_id() == false) {
				session_start();
			}
			// Create a Facebook instance using the "PHP SDK Unit Tests" AppId.
			// The AppId should be overwriten with your own setAppId()
			Facebook::$instance = new Facebook(array(
				'appId' => '117743971608120',
				'secret' => '943716006e74d9b9283d4d5d8ab93204',
			));
		}
		return Facebook::$instance;
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
		if ($accessToken) {
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
	 * Check if the accessToken is active/known
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
	 * Short notation for the api GET requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	function get($path, $parameters = array()) {
		return $this->api($path, 'GET', $parameters);
	}

	/**
	 * Retrieve data via an FQL (Facebook Query Language) query
	 * @link https://developers.facebook.com/docs/reference/fql/
	 *
	 * @param type $fql
	 * @return type
	 */
	function query($fql) {
		return $this->api(array(
					'method' => 'fql.query',
					'query' => $fql,
					'callback' => ''
				));
	}

	/**
	 * Fetch all pages in a paginated result.
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return array
	 */
	function all($path, $parameters = array(), $pageLimit = null) {
		if (isset($parameters['limit']) || isset($parameters['offset'])) { // The request is for a specific page
			return $this->api($path, 'GET', $parameters);
		}
		if ($pageLimit === null) {
			$pageLimit = $this->defaultPageLimit = 10;
		}
		$page = 0;
		$data = array();
		$url = new URL($path);
		$url->query = $parameters;
		while (true) {
			if ($page > $pageLimit) {
				notice('Max pages was reached');
				break;
			}
			$response = $this->api($url->path, 'GET', $url->query); // fetch page
			$data = array_merge($data, $response['data']);
			if (empty($response['paging']['next']) == false) {
				$url = new URL($response['paging']['next']);
			} else {
				// no more pages
				break;
			}
			$page++;
		}
		return $data;
	}

	/**
	 * Short notation for the api POST requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	function post($path, $parameters = array()) {
		return $this->api($path, 'POST', $parameters);
	}

	/**
	 * Short notation for the api DELETE requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	function delete($path, $parameters = array()) {
		return $this->api($path, 'DELETE', $parameters);
	}
	/**
	 * Make an API call.
	 *
	 * @throws Exceptions on failure
	 * @return mixed response
	 */
	function api(/* polymorphic */) {
		$start = microtime(true);
		$arguments = func_get_args();
		if (isset($arguments[2]['fields']) && is_array($arguments[2]['fields'])) {
			$arguments[2]['fields'] = implode(',', $arguments[2]['fields']);
		}
		$response = call_user_func_array('parent::api', $arguments);
		// Log resquest
		$this->executionTime += (microtime(true) - $start);
		$this->requestCount++;
		if ($this->requestCount < $this->logLimit) {
			$this->log[] = array(
				'request' => $arguments,
				'exectutionTime' => (microtime(true) - $start)
			);
		}
		return $response;
	}

	/**
	 * Get the permissions/scope of the connection.
	 * @return array
	 */
	function getPermissions() {
		$permissions = $this->getPersistentData('permissions', false);
		if ($permissions === false) {
			$response = $this->get('me/permissions');
			$permissions = array();
			foreach ($response['data'][0] as $permission => $enabled) {
				if ($enabled) {
					$permissions[] = $permission;
				}
			}
			$this->setPersistentData('permissions', $permissions);
		}
		return $permissions;

	}


	protected function clearAllPersistentData() {
		unset($_SESSION['__Facebook__']);
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

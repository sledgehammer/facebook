<?php
/**
 * FacebookClient
 */
namespace Sledgehammer;
/**
 * Wrapper for easy access to the the Facebook API calls
 * @package Facebook
 */
class FacebookClient extends Object {

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
	 * Maximum number of request that will be logged.
	 * @var int
	 */
	public $logLimit = 0;

	/**
	 * Automatic paging is limited to X pages.
	 * @var int
	 */
	public $automaticPagerLimit = 10;


	/**
	 * The Facebook class from the official PHP SDK
	 * @link https://developers.facebook.com/docs/reference/php/
	 * @var \Facebook
	 */
	private $connection;

	public $log = array();

	/**
	 * Constructor
	 * @param string $appId
	 * @param string $appSecret
	 */
	function __construct($appId, $appSecret) {
		$this->connection = new \Facebook(array(
			'appId' => $appId,
			'secret' => $appSecret,
			'fileUpload' => true,
			'cookie' => true
		));
	}

	/**
	 * Check if the accessToken is active/known
	 * @return bool
	 */
	function isConnected() {
		// @todo Real status check/request?
		// @todo expire ts controleren?
		if (isset($_SESSION[$this->getSessionVar()])) {
			if ($this->getUserId() == 0) {
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Set up a Facebook connection.
	 * Might cause a redirect which ends the current script.
	 *
	 *
	 * @param array $parameters  List with optional parameters
	 *   'display' => 'popup'
	 *   'scope' => array('email','read_stream', etc) @link https://developers.facebook.com/docs/authentication/permissions/
	 *   'redirect_url'> callback url
	 * @return true|string loginurl
	 */
	function connect($parameters = array()) {
		if (isset($parameters['scope']) && is_array($parameters['scope'])) {
			$parameters['scope'] = implode(',', $parameters['scope']);
		}
		if (isset($_GET['error']) || isset($_GET['error_reason'])) {
			throw new \Exception($_GET['error_description']);
		}
		if (isset($_GET['code'])) {
			$accessToken = $this->connection->getAccessToken();
			$_SESSION[$this->getSessionVar()] = $accessToken;
			return true;
		}
		if (isset($_POST['signed_request'])) {
			if ($this->connection->getUser() != 0) {
				$accessToken = $this->connection->getAccessToken();
				$_SESSION[$this->getSessionVar()] = $accessToken;
				return true;
			}
			return $this->connection->getLoginUrl($parameters);
		}
		// Redirect
		$url = $this->connection->getLoginUrl($parameters);
		header("Location: ".$url);
		exit();
	}

	/**
	 * Returns the Access Token.
	 *
	 * @return string
	 */
	function getAccessToken() {
		return $this->connection->getAccessToken();
	}

	/**
	 * Sets the Access Token.
	 *
	 * @param string $accessToken
	 */
	function setAccessToken($accessToken) {
		return $this->connection->setAccessToken($accessToken);
	}

	/**
	 * Close the Facebook connection
	 * @todo Really close the connection
	 */
	function close() {
		unset($_SESSION[$this->getSessionVar()]); // Causes isConnected() to return false
	}

	/**
	 * Returns the userId of the logged in user.
	 * (could generate a API call)
	 * @return int
	 */
	function getUserId() {
		return $this->connection->getUser();
	}

	/**
	 * Get profile information.
	 *
	 * @param string $userId  Use "me" for the loggedin user
	 * @param array $fields
	 * @return array
	 */
	function getUser($userId = 'me', $fields = null) {
		$parameters = $this->addFieldsToParameters($fields);
		return $this->get('/'.$userId, $parameters);
	}

	/**
	 * Build the user profile image url.
	 *
	 * @param string $userId
	 * @param string $size Image sizes: "square" (50x50) [default], "small" (50x?), "normal" (100x?) or "large" (200x?)
	 * @return string URL
	 */
	function getAvatarUrl($userId = 'me', $size = null) {
		$url = 'https://graph.facebook.com/'.$userId.'/picture';
		if ($size !== null) {
			$url .= '?type='.$size;
		}
		return $url;
	}

	/**
	 * Get a wall post
	 *
	 * @param string $postId
	 * @param array $fields
	 * @return array
	 */
	function getPost($postId, $fields = null) {
		$parameters = $this->addFieldsToParameters($fields);
		return $this->get('/'.$postId, $parameters);
	}



	/**
	 * Retrieve all the friends of the loggedin user.
	 * @link http://developers.facebook.com/docs/reference/api/user/
	 *
	 * @param array $fields optional
	 * @return array
	 */
	function getFriends($fields = null) {
		$parameters = $this->addFieldsToParameters($fields);
		return $this->all('/me/friends', $parameters);
	}

	/**
	 * Search over all public objects in the social graph.
	 * @link https://developers.facebook.com/docs/reference/api/#searching
	 *
	 * @param string $keyword
	 * @param array $parameters array(
	 *   'type' => (optional) post|user|page|group|checkin
	 *   'fields' => (optional)
	 *   'center' => (optional) geocoordinates. example: "37.76,-122.427"
	 *   'distance' => (optional)
	 *   'since' => (optional) yesterday
	 *   'until' => (optional) yesterday
	 *   'limit' => (optional)
	 * )
	 */
	function search($keyword, $parameters = array()) {
		if (isset($parameters['fields'])) {
			$parameters = $this->addFieldsToParameters($parameters['fields'], $parameters);
		}
		$parameters['q'] = $keyword;
		return $this->get('search', $parameters);
	}

	/**
	 * Redirect to a send message dialog.
	 *
	 * @param string $to  A user ID or username. Once the dialog comes up, the user can specify additional users.
	 * @param string $link  The URL
	 * @param array $parameters optional parameters
	 *   'display': iframe|popup
	 *   'name': By default a title will be taken from the link specified
	 *   'picture': By default a picture will be taken from the link specified
	 *   'description': By default a description will be taken from the link specified
	 */
	function sendMessageDialog($to, $link, $parameters = array()) {
		$parameters = array_merge(array(
			'app_id' => $this->connection->getAppId(),
			'to' => $to,
			'link' => $link,
				), $parameters);
		$hash = md5(serialize($parameters));
		if (isset($_GET['fb_sendMessageDialog']) && $_GET['fb_sendMessageDialog'] == $hash) {
			return;
		}
		$parameters['redirect_uri'] = Router::url(null, true).'?fb_sendMessageDialog='.$hash;
		$url = $this->getDialogUrl('send', $parameters);
		header('Location: '.$url);
		exit;
	}

	function getDialogUrl($dialog, $parameters = array()) {
		return 'https://www.facebook.com/dialog/'.$dialog.'?'.http_build_query($parameters);
	}

	/**
	 * Send an email to the recipients.
	 *
	 * requires "email" scope
	 *
	 * @param array $recipients  UserIds
	 * @param string $subject
	 * @param string $text
	 * @param string $fbml
	 */
	function sendEmail($recipients, $subject, $text, $fbml = null) {
		if ($fbml === null) {
			$fbml = $text;
		}
		if (is_array($recipients)) {
			$recipients = implode(',', $recipients);
		}
		return $this->api(array(
					'method' => 'notifications.sendEmail',
					'recipients' => $recipients,
					'subject' => $subject,
					'text' => $text,
					'fbml' => $fbml,
				));
	}

	/**
	 * Post a link on the feed/wall of the the loggedin user.
	 * @link http://developers.facebook.com/docs/reference/api/user/#posts
	 *
	 * @param string $url  The link
	 * @param array $parameters  message, picture, name, caption, description
	 */
	function postLink($url, $parameters = array()) {
		$parameters['link'] = $url;
		return $this->post('/me/feed', $parameters);
	}

	/**
	 * Place a message on the wall of a friend.
	 *
	 * @link http://developers.facebook.com/docs/reference/api/user/#posts
	 *
	 * @param string $userId
	 * @param string $message
	 * @param array $parameters optional parameters
	 * @return string id
	 */
	function sendMessage($userId, $message, $parameters = array()) {
		$parameters = array_merge(array(
			'message' => $message,
		), $parameters);
		$response = $this->post('/'.$userId.'/feed', $parameters);
		return $response['id'];
	}

	/**
	 * Place a link on the wall of a friend.
	 *
	 * @link http://developers.facebook.com/docs/reference/api/user/#posts
	 *
	 * @param string $userId
	 * @param string $link URL
	 * @param array $parameters optional parameters
	 * @return string id
	 */
	function sendLink($userId, $link, $parameters = array()) {
		$parameters = array_merge(array(
			'link' => $link,
		), $parameters);
		$response = $this->post('/'.$userId.'/feed', $parameters);
		return $response['id'];
	}

	/**
	 * Retrieve the AppRequest
	 *
	 * @param string $requestId
	 */
	function getAppRequest($requestId) {
		// @todo  Valideren of het id een app request is?
		return $this->get('/'.$requestId);
	}

	/**
	 * Delete the AppRequest/Invite
	 * @param string $requestId
	 */
	function deleteAppRequest($requestId) {
		// @todo  Valideren of het id een app request is?
		$this->delete('/'.$requestId);
	}

	/**
	 *
	 * @param string $userId
	 */
	function getAppRequestsFor($userId) {
		return $this->all('/'.$userId.'/apprequests');
	}

	/**
	 * Short notation for the api GET requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	protected function get($path, $parameters = array()) {
		return $this->api($path, 'GET', $parameters);
	}

	/**
	 * Retrieve data via an FQL (Facebook Query Language) query
	 * @link https://developers.facebook.com/docs/reference/fql/
	 *
	 * @param type $fql
	 * @return type
	 */
	protected function query($fql) {
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
	protected function all($path, $parameters = array()) {
		if (isset($parameters['limit']) || isset($parameters['offset'])) { // The request is for a specific page
			return $this->api($path, 'GET', $parameters);
		}
		$page = 0;
		$data = array();
		$url = new URL($path);
		$url->query = $parameters;
		while (true) {
			if ($page > $this->automaticPagerLimit) {
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
	protected function post($path, $parameters = array()) {
		return $this->api($path, 'POST', $parameters);
	}

	/**
	 * Make an API call.
	 *
	 * @throws Exceptions on failure
	 * @return mixed response
	 */
	protected function api(/* polymorphic */) {
		$start = microtime(true);
		$arguments = func_get_args();
		$response = call_user_func_array(array($this->connection ,'api'), $arguments);
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
	 * Short notation for the api DELETE requests
	 *
	 * @param string $path
	 * @param array $parameters
	 * @return mixed
	 */
	protected function delete($path, $parameters = array()) {
		return $this->api($path, 'DELETE', $parameters);
	}

	/**
	 * Return $parameters with the 'fields'
	 *
	 * @param mixed $fields
	 *   null = Don't add fields
	 *   string = Add the fields as-is (unless the string is empty)
	 *   array = Add the array values as fields
	 * @param type $parameters
	 * @return array
	 */
	protected function addFieldsToParameters($fields, $parameters = array()) {
		if ($fields === null) {
			// no op
		} elseif (is_array($fields) && count($fields) != 0) {
			$parameters['fields'] = implode(',', $fields);
		} elseif (is_string($fields) && $fields != '') {
			$parameters['fields'] = $fields;
		}
		return $parameters;
	}

	/**
	 * The key for the $_SESSION variable that contains the accessToken
	 *
	 * @return string
	 */
	protected function getSessionVar() {
		return 'facebook_client_'.$this->connection->getAppId();
	}

}

?>
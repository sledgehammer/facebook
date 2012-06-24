<?php
/**
 * FacebookRepositoryBackend
 */
namespace Sledgehammer;
/**
 * A user profile as represented in the Graph API.
 * @link https://developers.facebook.com/docs/reference/api/user/
 * @package Facebook
 */
class FacebookUser extends GraphObject {

	/**
	 * The user's Facebook ID
	 * @var string
	 */
	public $id;

	/**
	 * The user's full name
	 * @var string
	 */
	public $name;

	/**
	 * The user's first name
	 * @var string
	 */
	public $first_name;

	/**
	 * The user's middle name
	 * @var string
	 */
	public $middle_name;

	/**
	 * The user's last name
	 * @var string
	 */
	public $last_name;

	/**
	 * The user's gender: female or male
	 * @var string
	 */
	public $gender;

	/**
	 * The user's locale
	 * @var string
	 */
	public $locale;

	/**
	 * The user's languages.
	 * @permission user_likes;
	 * Contains the ISO Language Code and ISO Country Code
	 * array of objects containing language id and name
	 * @var array
	 */
	public $languages;

	/**
	 * The URL of the profile for the user on Facebook
	 * @var string
	 */
	public $link;

	/**
	 * The user's Facebook username
	 * @var string
	 */
	public $username;

	/**
	 * An anonymous, but unique identifier for the user; only returned if specifically requested via the fields URL parameter
	 * Requires access_token
	 * @var string
	 */
	public $third_party_id;

	/**
	 * Specifies whether the user has installed the application associated with the app access token that is used to make the request; only returned if specifically requested via the fields URL parameter
	 * Requires app access_token
	 * @var object containing type (this is always "user"), id (the ID of the user), and optional installed field (always true if returned); The installed field is only returned if the user has installed the application, otherwise it is not part of the returned object
	 */
	public $installed;

	/**
	 * The user's timezone offset from UTC
	 * Available only for the current user
	 * @var number
	 */
	public $timezone;

	/**
	 * The last time the user's profile was updated; changes to the languages, link, timezone, verified, interested_in, favorite_athletes, favorite_teams, and video_upload_limits are not not reflected in this value
	 * Requires access_token
	 * containing an ISO-8601 datetime
	 * @var string
	 */
	public $updated_time;

	/**
	 * The user's account verification status, either true or false (see below)
	 * Requires access_token
	 * @var bool
	 */
	public $verified;

	/**
	 * The user's biography
	 * @permissions user_about_me or friends_about_me
	 */
	public $bio;

	/**
	 * The user's birthday
	 * @permissions user_birthday or friends_birthday
	 * Date string in MM/DD/YYYY format
	 * @var string
	 */
	public $birthday;

	/**
	 * The user's cover photo (must be explicitly requested using fields=cover parameter)
	 * array of fields id, source, and offset_y
	 * @var array
	 */
	public $cover;

	/**
	 * A list of the user's devices beyond desktop
	 * @permissions User access_token required; only available for friends of the current user
	 * array of objects containing os which may be a value of 'iOS' or 'Android', along with an additional field hardware which may be a value of 'iPad' or 'iPhone' if present, however may not be returned if we are unable to determine the hardware model - Note: this is a non-default field and must be explicitly specified as shown below
	 * @var array
	 */
	public $devices;

	/**
	 * A list of the user's education history
	 * @permission user_education_history or friends_education_history
	 * array of objects containing year and type fields, and school object (name, id, type, and optional year, degree, concentration array, classes array, and with array )
	 * @var array
	 */
	public $education;

	/**
	 * email
	 * The proxied or contact email address granted by the user
	 * contains a valid RFC822 email address
	 * @var string
	 */
	public $email;

	/**
	 * The user's hometown
	 * @permission user_hometown or friends_hometown
	 * object containing name and id
	 * @var array
	 */
	public $hometown;

	/**
	 * The genders the user is interested in
	 * @permission user_relationship_details or friends_relationship_details
	 * @var array
	 */
	public $interested_in;

	/**
	 * The user's current city
	 * @permission user_location or friends_location
	 * object containing name and id
	 * @var array
	 */
	public $location;

	/**
	 * The user's political view
	 * @permission user_religion_politics or friends_religion_politics
	 */
	public $political;

	/**
	 * The URL of the user's profile pic
	 * @var string
	 */
	public $picture;

	/**
	 * The user's favorite quotes
	 * @permission user_about_me or friends_about_me
	 * @var string
	 */
	public $quotes;

	/**
	 * The user's relationship status:
	 * @permission user_relationships or friends_relationships
	 * @var string
	 */
	public $relationship_status;

	/**
	 * The user's religion
	 * @permission user_religion_politics or friends_religion_politics
	 * @var string
	 */
	public $religion;

	/**
	 * The user's significant other
	 * @permission user_relationships or friends_relationships
	 * object containing name and id
	 * @var array
	 */
	public $significant_other;

	/**
	 * The size of the video file and the length of the video that a user can upload.
	 * @permission Requires access_token
	 * object containing length and size of video
	 * @var array
	 */
	public $video_upload_limits;

	/**
	 * The URL of the user's personal website
	 * @permission user_website or friends_website
	 * containing a valid URL
	 * @var string
	 */
	public $website;

	/**
	 * A list of the user's work history
	 * @permission user_work_history or friends_work_history
	 * array of objects containing employer, location, position, start_date and end_date fields
	 * @var string
	 */
	public $work;

	//################################
	//    Relations/connections
	//################################

	/**
	 * The user's friends.
	 * @permission Only for the current user.
	 * @var Collection
	 */
	public $friends;

	/**
	 * All the pages this user has liked.
	 * @permission user_likes or friends_likes
	 * @var Collection|FacebookPage
	 */
	public $likes;

	/**
	 * Constructor
	 * @param mixed $id
	 * @param array $parameters
	 */
	function __construct($id, $parameters = null) {
		if ($id === null || is_array($id)) {
			parent::__construct($id, $parameters);
			return;
		}
		if ($parameters !== null) {
			parent::__construct($id, $parameters);
			return;
		}
		if (isset($_SESSION['__Facebook__']['cache'][$id])) {
			parent::__construct($_SESSION['__Facebook__']['cache'][$id], $parameters);
			return;
		}
		parent::__construct($id, array('fields' => $this->getAllowedFields(array('id' => $id))));
		// Cache userdata
		$_SESSION['__Facebook__']['cache'][$id] = get_public_vars($this);
	}

	function __get($property) {
		if ($property === 'friends') {
			$path = $this->id.'/friends';
			if (isset($_SESSION['__Facebook__']['cache'][$path])) {
				$response = $_SESSION['__Facebook__']['cache'][$path];
			} else {
				$friends = array();
				$fb = Facebook::getInstance();
				$response = $fb->all($path, array('fields' => $this->getAllowedFields()));
				$_SESSION['__Facebook__']['cache'][$path] = $response;
			}
			foreach ($response as $friend) {
				$friends[] = new FacebookUser($friend);
			}
			$this->_state = 'construct';
			$this->$property = new Collection($friends);
			$this->_state = 'ready';
			return $this->$property;
		}
		return parent::__get($property);
	}

	protected static function getFieldPermissions($options = array()) {
		$permissions = array(
			'bio' => 'about_me',
			'birthday' => 'birthday',
			'education' => 'education_history',
			'hometown' => 'hometown',
			'interested_in' => 'relationship_details',
			'location' => 'location',
			'political' => 'religion_politics',
			'quotes' => 'about_me',
			'relationship_status' => 'relationships',
			'religion' => 'religion_politics',
			'significant_other' => 'relationships',
			'website' => 'website',
			'work' => 'work_history',
		);
		$fb = Facebook::getInstance();
		if (isset($options['id']) && ($options['id'] === 'me' || $options['id'] === $fb->getUser())) { // Current user?
			foreach ($permissions as $property => $permission) {
				$permissions[$property] = 'user_'.$permission;
			}
			$permissions['languages'] = 'user_likes';
			$permissions['email'] = 'email';
		} else {
			// @todo detect if it's a friend
			foreach ($permissions as $property => $permission) {
				$permissions[$property] = 'friends_'.$permission;
			}
			$permissions['email'] = 'denied';
			$permissions['languages'] = 'denied';
		}
		return $permissions;
	}

	protected static function getKnownConnections($options = array()) {
		return array(
			'friends' => '\Sledgehammer\FacebookUser',
			'likes' => '\Sledgehammer\FacebookPage',
		);
	}

}

?>

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
	 * The Facebook apps and pages owned by the current user.
	 * @permission manage_pages yields access_tokens that can be used to query the Graph API on behalf of the app/page
	 * array of objects containing account name, access_token, category, id
	 * @var Collection|GraphObject
	 */
	public $accounts;

	/**
	 * The achievements for the user.
	 * @permission user_games_activity or friends_games_activity.
	 * array of achievement(instance) objects
	 * @var Collection|GraphObject
	 */
	public $achievements;

	/**
	 * The activities listed on the user's profile.
	 * @permission user_activities or friends_activities.
	 * array of objects containing activity id, name, category and create_time fields.
	 * @var Collection|GraphObject
	 */
	public $activities;

	/**
	 * The photo albums this user has created.
	 * @permission user_photos or friends_photos.
	 * array of Album objects.
	 * @var Collection|GraphObject
	 */
	public $albums;

	/**
	 * The user's outstanding requests from an app.
	 * array of app requests for the user within that app.
	 * @var Collection|GraphObject
	 */
	public $apprequests;

	/**
	 * The books listed on the user's profile.
	 * user_likes or friends_likes.
	 * array of objects containing book id, name, category and create_time fields.
	 * @var Collection|GraphObject
	 */
	public $books;

	/**
	 * The places that the user has checked-into.
	 * @permission user_checkins or friends_checkins.
	 * @var Collection|GraphObject
	 */
	public $checkins;

	/**
	 * The events this user is attending.
	 * @permission user_events or friends_events.
	 * array of objects containing event id, name, start_time, end_time, location and rsvp_status defaulting to the past two weeks.
	 * @var Collection|GraphObject
	 */
	public $events;

	/**
	 * The user's family relationships
	 * @permission user_relationships.
	 * array of objects containing id, name, and relationship fields.
	 * @var Collection|GraphObject
	 */
	public $family;

	/**
	 * The user's wall.
	 * @permission read_stream
	 * array of Post objects containing (up to) the last 25 posts.
	 * @var Collection|GraphObject
	 */
	public $feed;

	/**
	 * The user's friend lists.
	 * @permission read_friendlists.
	 * array of objects containing id and name fields of the friendlist.
	 * @var Collection|GraphObject
	 */
	public $friendlists;

	/**
	 * The user's incoming friend requests.
	 * @permission user_requests.
	 * array of objects containing to, from, message, created_time and unread fields of the friend request
	 * @var Collection|GraphObject
	 */
	public $friendrequests;

	/**
	 * The user's friends.
	 * @var Collection|FacebookUser
	 */
	public $friends;

	/**
	 * Games the user has added to the Arts and Entertainment section of their profile.
	 * @permission user_likes
	 * array of objects containing id, name, category, and created_time
	 * @var Collection|GraphObject
	 */
	public $games;

	/**
	 * The Groups that the user belongs to.
	 * @permission user_groups or friends_groups.
	 * An array of objects containing the version(old-0 or new Group-1), name, id, administrator (if user is the administrator of the Group) and bookmark_order(at what place in the list of group bookmarks on the homepage, the group shows up for the user).
	 * @var Collection|GraphObject
	 */
	public $groups;

	/**
	 * The user's news feed.
	 * @permission read_stream.
	 * array of Post objects containing (up to) the last 25 posts.
	 * @var Collection|GraphObject
	 */
	public $home;

	/**
	 * The Threads in this user's inbox.
	 * @permission read_mailbox
	 * @var Collection|GraphObject
	 */
	public $inbox;

	/**
	 * The interests listed on the user's profile.
	 * @permission user_interests or friends_interests.
	 * array of objects containing interest id, name, category and create_time fields.
	 */
	public $interests;

	/**
	 * All the pages this user has liked.
	 * @permission user_likes or friends_likes.
	 * array of objects containing like id, name, category and create_time fields.
	 * @var Collection|FacebookPage
	 */
	public $likes;

	/**
	 * The user's posted links.
	 * @permission read_stream
	 * @var Collection|GraphObject
	 */
	public $links;

	/**
	 * Posts, statuses, and photos in which the user has been tagged at a location, or where the user has authored content (i.e. this excludes objects with no location information, and objects in which the user is not tagged). See documentation of the location_post table for more detailed information on permissions.
	 * @permission user_photos, friend_photos, user_status, friends_status, user_checkins, or friends_checkins.
	 * array of objects containing the id, type, place, created_time, and optional application and tags fields.
	 * @var Collection|GraphObject
	 */
	public $locations;

	/**
	 * The movies listed on the user's profile.
	 * @permission user_likes or friends_likes
	 * array of objects containing movie id, name, category and create_time fields.
	 * @var Collection|GraphObject
	 */
	public $movies;

	/**
	 * The music listed on the user's profile.
	 * @permission user_likes or friends_likes
	 * array of objects containing music id, name, category and create_time fields.
	 * @var Collection|GraphObject
	 */
	public $music;

	/**
	 * The mutual friends between two users.
	 * array of objects containing friend id and name fields.
	 * @var Collection|GraphObject
	 */
	public $mutualfriends;

	/**
	 * The user's notes.
	 * @permission read_stream.
	 * @var Collection|GraphObject
	 */
	public $notes;

	/**
	 * The notifications for the user.
	 * @permission manage_notifications
	 * array of objects containing id, from, to, created_time,updated_time, title, link, application, unread.
	 * @var Collection|GraphObject
	 */
	public $notifications;

	/**
	 * The messages in this user's outbox.
	 * @permission read_mailbox
	 * array of messages
	 * @var Collection|GraphObject
	 */
	public $outbox;

	/**
	 * The Facebook Credits orders the user placed with an application. See the Credits API for more information.
	 * @var Collection|GraphObject
	 */
	public $payments;

	/**
	 * The permissions that user has granted the application.
	 * @var Collection|GraphObject
	 * array containing a single object which has the keys as the permission names and the values as the permission values (1/0)
	 */
	public $permissions;

	/**
	 * Photos the user (or friend) is tagged in.
	 * @permission user_photo_video_tags or friends_photo_video_tags.
	 * array of Photo objects.
	 * @var Collection|GraphObject
	 */
	public $photos;

	/**
	 * Connection omitted, is also defined as field.
	 * The user's profile picture.
	 * @var Collection|GraphObject
	 * HTTP 302 redirect to URL of the user's profile picture (use ?type=square | small | normal | large to request a different photo).
	 */
//public $picture

	/**
	 * The user's pokes.
	 * @permission read_mailbox
	 * an array of objects containing to, from, created_time and type fields.
	 * @var Collection|GraphObject
	 */
	public $pokes;

	/**
	 * The user's own posts.
	 * @permission read_stream to see non-public posts.
	 * @var Collection|FacebookPost
	 */
	public $posts;

	/**
	 * The user's questions.
	 * @permission user_questions
	 * @var Collection|GraphObject
	 */
	public $questions;

	/**
	 * The current scores for the user in games.
	 * @permission user_games_activity or friends_games_activity
	 * array of objects containing user, application, score and type.
	 * @var Collection|GraphObject
	 */
	public $scores;

	/**
	 * The user's status updates.
	 * @permission read_stream
	 * @var Collection|GraphObject
	 */
	public $statuses;

	/**
	 * People you're subscribed to.
	 * array of objects containing user id and name fields.
	 * @var Collection|GraphObject
	 */
	public $subscribedto;

	/**
	 * The user's subscribers.
	 * @var Collection|GraphObject
	 * array of objects containing user id and name fields.
	 */
	public $subscribers;

	/**
	 * Posts the user is tagged in.
	 * @permission read_stream
	 * array of objects containing id, from, to, picture, link, name, caption, description, properties, icon, actions, type, application, created_time, and updated_time
	 * @var Collection|GraphObject
	 */
	public $tagged;

	/**
	 * The television listed on the user's profile.
	 * @permission user_likes or friends_likes.
	 * array of objects containing television id, name, category and create_time fields.
	 * @var Collection|GraphObject
	 */
	public $television;

	/**
	 * The updates in this user's inbox.
	 * @permission read_mailbox
	 * @var Collection|GraphObject
	 */
	public $updates;

	/**
	 * The videos this user has been tagged in.
	 * @permission user_videos or friends_videos.
	 * @var Collection|GraphObject
	 */
	public $videos;

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

	/**
	 * Post a link or status-message to the users feed.
	 * @permission publish_stream
	 *
	 * @param FacebookPost|GraphObject|array $post
	 * @return FacebookPost
	 */
	function postToFeed($post) {
		if (is_array($post)) {
			$post = new FacebookPost($post);
		}
		$fb = Facebook::getInstance();
		if (in_array('publish_stream', $fb->getPermissions()) === false) {
			notice('Posting to the user\'s feed requires the "publish_stream" permission', 'Current permissions: '.quoted_human_implode(' and ', $fb->getPermissions()));
		}
		$response = $fb->post($this->id.'/feed', $post);
		$class = get_class($post);
		return new $class($response['id']);
	}

	/**
	 * List of two Users mutual friends.
	 * @param number $userId
	 * @return \Sledgehammer\Collection
	 */
	function getMutualfriendWith($userId) {
		$fb = Facebook::getInstance();
		$friends = $fb->all($this->id.'/mutualfriends/'.$userId); //, array('fields' => $this->getAllowedFields()));
		return new Collection($friends);
	}

	function __get($property) {
		$fb = Facebook::getInstance();
		if ($property === 'friends') {
			$path = $this->id.'/friends';
			if (isset($_SESSION['__Facebook__']['cache'][$path])) {
				$response = $_SESSION['__Facebook__']['cache'][$path];
			} else {
				$friends = array();

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
		if ($property === 'mutualfriends') {
			$this->_state = 'construct';
			$this->$property = $this->getMutualfriendWith($fb->getUser());
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
			'accounts' => null,
			'achievements' => null,
			'activities' => null,
			'albums' => null,
			'apprequests' => null,
			'books' => null,
			'checkins' => null,
			'events' => null,
			'family' => null,
			'feed' => null,
			'friendlists' => null,
			'friendrequests' => null,
			'friends' => '\Sledgehammer\FacebookUser',
			'games' => null,
			'groups' => null,
			'home' => null,
			'inbox' => null,
			'interests' => null,
			'likes' => '\Sledgehammer\FacebookPage',
			'links' => null,
			'locations' => null,
			'movies' => null,
			'music' => null,
			'mutualfriends' => null,
			'notes' => null,
			'notifications' => null,
			'outbox' => null,
			'payments' => null,
			'permissions' => null,
			'photos' => null,
			'pokes' => null,
			'posts' => '\Sledgehammer\FacebookPost',
			'questions' => null,
			'scores' => null,
			'statuses' => null,
			'subscribedto' => null,
			'subscribers' => null,
			'tagged' => null,
			'television' => null,
			'updates' => null,
			'videos' => null,
		);
	}

}

?>

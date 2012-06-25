<?php
/**
 * FacebookPage
 */
namespace Sledgehammer;
/**
 * A Page in the Graph API.
 * @link https://developers.facebook.com/docs/reference/api/page/
 */
class FacebookPage extends GraphObject {

	/**
	 * The Page's ID
	 * @var string
	 */
	public $id;

	/**
	 * The Page's name
	 * @var string
	 */
	public $name;

	/**
	 * Link to the page on Facebook
	 * string containing a valid URL
	 * @var
	 */
	public $link;

	/**
	 * The Page's category
	 * @var string
	 */
	public $category;

	/**
	 * Indicates whether the page is published and visible to non-admins
	 *
	 * @var bool
	 */
	public $is_published;

	/**
	 * Indicates whether the current session user can post on this Page
	 * @var bool
	 */
	public $can_post;

	/**
	 * The number of users who like the Page
	 * @var number
	 */
	public $likes;

	/**
	 * The Page's street address, latitude, and longitude (when available)
	 * @var array
	 */
	public $location;

	/**
	 * The phone number (not always normalized for country code) for the Page
	 * @var string
	 */
	public $phone;

	/**
	 * The total number of users who have checked in to the Page
	 * @var number
	 */
	public $checkins;

	/**
	 * Link to the Page's profile picture
	 * @var string
	 */
	public $picture;

	/**
	 * An object containing the cover_id, source, and offset_y.
	 * @var array
	 */
	public $cover;

	/**
	 * Link to the external website for the page
	 * @var string
	 */
	public $website;

	/**
	 * The number of people that are talking about this page (last seven days)
	 * @var number
	 */
	public $talking_about_count;

	/**
	 * A Page admin access_token for this page;
	 * @permission The current user must be an administrator of this page
	 */
//	public $access_token;

	/**
	 * Constructor
	 * @param mixed $id
	 * @param array $parameters
	 */
	function __construct($id, $parameters = null, $preload = false) {
		if ($parameters === null) {
			$parameters = array('fields' => self::getAllowedFields());
		}
		parent::__construct($id, $parameters, $preload);
	}

}

?>

<?php
/**
 * GraphObject
 */
namespace Sledgehammer;
/**
 * An object oriented interface to the Facebook Graph API.
 * https://developers.facebook.com/docs/reference/api/
 *
 * @package Facebook
 */
class GraphObject extends Object {

	protected $_state = 'invalid';

	/**
	 * Constructor
	 * @param string $id
	 * @param array $parameters
	 */
	function __construct($id, $parameters = array()) {
		if ($id === null) {
			// Unset all properties
			$properties = array_keys(get_public_vars($this));
			foreach ($properties as $property) {
				unset($this->$property);
			}
			return;
		}
		$this->_state = 'construct';
		if (is_array($id)) {
			$data = $id;
		} else {
			$data = Facebook::getInstance()->get($id, $parameters);
		}
		set_object_vars($this, $data);
		// Remove properties not included in the data.
		$properties = array_keys(get_public_vars($this));
		foreach ($properties as $property) {
			if (isset($data[$property]) === false) {
				unset($this->$property);
			}
		}
		$this->_state = 'ready';
	}

	/**
	 * Fetch connected grapobjects and store in the property.
	 *
	 * @param string $property
	 * @return mixed
	 */
	function __get($property) {
		// Retrieve a connection
		try {
			$connections = $this->getKnownConnections();
			if (isset($connections[$property])) {
				$class = $connections[$property];
				$parameters = array('fields' => call_user_func(array($class, 'getAllowedFields')));
			} else {
				$class = GraphObject;
				$parameters = array();
			}
			$response = Facebook::getInstance()->all($this->id.'/'.$property, $parameters);
			$objects = array();
			foreach ($response as $data) {
				$objects[] = new $class($data);
			}
			$this->_state = 'construct';
			$this->$property = new Collection($objects);
			$this->_state = 'ready';
			return $this->$property;
		} catch (\Exception $e) {
			report_exception($e);
			$this->_state = 'ready';
			return parent::__get($property);
		}
	}

	/**
	 * Allow adding properties in the "construct" fase.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	function __set($property, $value) {
		if ($this->_state === 'construct') {
			$this->$property = $value;
		} else {
			parent::__set($property, $value);
		}
	}

	/**
	 * Generate fieldlist based on propeties in the currect class.
	 *
	 * @param array $options Options that will be forwarded to the getFieldPermissions() and getKnownConnections() functions.
	 * @return array
	 */
	protected static function getAllowedFields($options = array()) {
		$permissions = static::getFieldPermissions($options);
		$relations = static::getKnownConnections($options);
		$fields = array();
		$fb = Facebook::getInstance();
		$availablePermissions = $fb->getPermissions();
		$properties = array_keys(get_public_vars(get_called_class()));
		foreach ($properties as $property) {
			if (isset($permissions[$property])) { // Does this property require a permission?
				if (in_array($permissions[$property], $availablePermissions)) { // permission granted?
					$fields[] = $property;
				}
			} elseif (array_key_exists($property, $relations) === false) { // Not a relation?
				$fields[] = $property;
			}
		}
		return $fields;
	}

	/**
	 * Fields/properties theat depend on permissions. array( field => permission)
	 * @return array
	 */
	protected static function getFieldPermissions() {
		return array();
	}

	/**
	 * Known related objects.
	 * @return array
	 */
	protected static function getKnownConnections() {
		return array();

	}
}

?>

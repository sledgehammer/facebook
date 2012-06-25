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

	/**
	 * The ID of the graph object.
	 * @var number
	 */
	public $id;

	/**
	 * new: GraphObject created with id: null
	 * constuct: Allow new properties to be added.
	 * id_only: id and parameters are but no api call to fetch fields is made.
	 * ready: fields are retrieved cia the facebook api.
	 *
	 * @var string
	 */
	protected $_state = 'new';
	/**
	 * Facebook API call parameters, only available in "id_only" state.
	 * @var array
	 */
	private $_apiParameters;

	/**
	 * Constructor
	 * @param string $id
	 * @param array $parameters Facebook API call parameters (fields, etc)
	 * @param bool $preload  true: Fetch fields from facebook now. false: Fetch fields from facebook on access.
	 */
	function __construct($id, $parameters = array(), $preload = false) {
		// Unset all properties
		$properties = array_keys(get_public_vars($this));
		unset($properties[array_search('id', $properties)]); // keep the id property
		foreach ($properties as $property) {
			unset($this->$property);
		}
		if ($id === null) {
			return;
		}
		if (is_array($id)) {
			$this->_state = 'construct';
			set_object_vars($this, $id);
			$this->_state = 'ready';
			unset($this->_apiParameters);
		} elseif ($preload) {
			$this->_state = 'construct';
			set_object_vars($this, Facebook::get($id, $parameters));
			$this->_state = 'ready';
			unset($this->_apiParameters);
		} else {
			$this->id = $id;
			$this->_apiParameters = $parameters;
			$this->_state = 'id_only';
		}
	}

	/**
	 * Delete the object from facebook.
	 *
	 * @return bool
	 */
	function delete() {
		if (empty($this->id)) {
			throw new \Exception('Can\'t delete an object without an id');
		}
		return Facebook::delete($this->id);
	}

	/**
	 * Fetch connected grapobjects and store in the property.
	 *
	 * @param string $property
	 * @return mixed
	 */
	function __get($property) {
		if (empty($this->id)) {
			return parent::__get($property);
		}
		$connections = $this->getKnownConnections();
		if (array_key_exists($property, $connections) === false) { // not a (known) connection?
			if ($this->_state === 'id_only') {
				$fields = Facebook::get($this->id, $this->_apiParameters);

				$this->_state = 'construct';
				set_object_vars($this, $fields);
				$this->_state = 'ready';
				unset($this->_apiParameters);
				if (array_key_exists($property, $fields)) {
					return $fields[$property];
				}
			}
			$fields = get_public_vars(get_class($this));
			if (array_key_exists($property, $fields)) { // is the field defined in the class?
				$permissions = static::getFieldPermissions(array('id' => $this->id));
				if (isset($permissions[$property]) && $permissions[$property] !== 'denied' && in_array($permissions[$property], Facebook::getInstance()->getPermissions()) === false) {
					notice('Field "'.$property.'" requires the "'.$permissions[$property].'" permission', 'Current permissions: '.quoted_human_implode(' and ', Facebook::getInstance()->getPermissions()));
				}
				return parent::__get($property);
			}
		}
		try {
			$state = $this->_state;
			// Retrieve a connection
			if (isset($connections[$property]) && $connections[$property] !== '\Sledgehammer\GraphObject') {
				$class = $connections[$property];
				$parameters = array('fields' => call_user_func(array($class, 'getAllowedFields')));
			} else {
				$class = '\Sledgehammer\GraphObject';
				$parameters = array();
			}
			$response = Facebook::all($this->id.'/'.$property, $parameters);
			$objects = array();
			foreach ($response as $data) {
				$objects[] = new $class($data);
			}

			$this->_state = 'construct';
			$this->$property = new Collection($objects);
			$this->_state = $state;
			return $this->$property;
		} catch (\Exception $e) {
			report_exception($e);
			$this->_state = $state;
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
	 * Handle postTo* methods.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	function __call($method, $arguments) {
		if (text($method)->startsWith('postTo')) { // a postTo*($data) method?
			if (empty($this->id)) {
				throw new \Exception('Can\'t post to a connection without an id');
			}
			$path = $this->id.'/'.lcfirst(substr($method, 6));
			if (count($arguments) > 0) {
				$parameters = $arguments[0];
			} else {
				notice('Missing argument 1 for '.$method.'()');
				$parameters = array();
			}
			$response = Facebook::post($path, $parameters);
			return new GraphObject($response['id']);
		} else {
			return parent::__call($method, $arguments);
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
		$availablePermissions = Facebook::getInstance()->getPermissions();
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

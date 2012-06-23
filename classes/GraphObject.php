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

	public $id;
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

	function __get($property) {
		// Retrieve a connection
		try {
			$response = Facebook::getInstance()->all($this->id.'/'.$property);;
			$objects = array();
			foreach ($response as $data) {
				$objects[] = new GraphObject($data);
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
	 * @param array $permissions fields and relations depending on permissions.
	 * @param array $relations
	 */
	protected function generateParameters($permissions, $relations) {
		$fields = array();
		$fb = Facebook::getInstance();
		$availablePermissions = $fb->getPermissions();
		$properties = array_keys(get_public_vars($this));
		foreach ($properties as $property) {
			if (isset($permissions[$property])) { // Does this property require a permission?
				if (in_array($permissions[$property], $availablePermissions)) { // permission granted?
					$fields[] = $property;
				}
			} elseif (in_array($property, $relations) === false) { // Not a relation?
				$fields[] = $property;
			}
		}
		return array(
			'fields' => implode(',', $fields)
		);
	}
}

?>

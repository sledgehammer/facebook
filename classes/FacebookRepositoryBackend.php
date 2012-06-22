<?php
/**
 * FacebookRepositoryBackend
 */
namespace Sledgehammer;
/**
 * @package Facebook
 */
class FacebookRepositoryBackend extends RepositoryBackend {

	public $identifier = 'facebook';

	/**
	 * @var FacebookClient
	 */
	private $client;

	/**
	 *
	 * @param FacebookClient $client
	 */
	function __construct($client) {
		$this->client = $client;
		$this->configs = array(
			'FacebookUser' => new ModelConfig('FacebookUser', array(
				'properties' => array(
					'id' => 'id',
					'name' => 'name',
					'first_name' => 'firstname',
					'last_name' => 'lastname',
					'link' => 'profileUrl',
					'username' => 'username',
					'updated_time' => 'updatedAt',
					'birthday?' => 'birthday?', // issn't alway in the array
					'gender?' => 'gender',
					'locale' => 'locale',
					'installed?' => 'installed',
					'cover?.source' => 'cover',
					'bio?' => 'bio',
					'devices?' => 'devices',
//					'education?' => 'education',
					'education?[*].school.name' => 'schools',
				),
				'backendConfig' => array(
					'get' => 'getUser',
					'all' => 'getFriends',
					'fields' => 'name,first_name,last_name,link,username,birthday,gender,locale,updated_time,installed,cover,bio,devices,education'
				)
			)),
		);
	}

	function all($config) {
		$method = $config['all'];
		return $this->client->getFriends($config['fields']);
	}

	function get($id, $config) {
		$method = $config['get'];
		return $this->client->getUser($id, $config['fields']);
	}

}

?>

<?php
/**
 * FacebookRepositoryBackend
 */
namespace Sledgehammer;
/**
 *
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
//					'birthday' => 'birthday', // issn't alway in the array
//					'gender' => 'gender',
					'locale' => 'locale',
					'updated_time' => 'updatedAt',
				),
				'backendConfig' => array(
					'type' => 'User'
				))
			)
		);
	}

	function all($config) {
		return $this->client->getFriends('name,first_name,last_name,link,username,birthday,gender,locale,updated_time');
	}

	function get($id, $config) {
		return $this->client->getUser($id);
	}
}

?>

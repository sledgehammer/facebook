<?php
/**
 * BuildFacebookClassFromDocumentation
 */
namespace Sledgehammer;
/**
 * Generate a Facebook class from the facebook api documentation html.
 */
class BuildFacebookClassFromDocumentation extends Util {

	public function generateContent() {
		// Can't download the documectation directly (required facebook login)
		$form = new Form(array(
			'class' => 'form-horizontal',
			'legend' => 'Paste HTML from https://developers.facebook.com/docs/reference/api/$model/',
			'fields' => array(
				'HTML' => new Input(array('type' => 'textarea', 'name' => 'source')),
				new Input(array('type' => 'submit', 'value' => 'Generate class', 'class' => 'btn btn-primary')),
			)
		));
		$data = $form->import($error);
		if ($data) {
			$dom = new \DOMDocument();
			@$dom->loadHTML($data['source']);
			$xml = simplexml_import_dom($dom);
			$link = $xml->xpath('//div[@class="breadcrumbs"]/a[last()]');
			$path = explode('/', trim($link[0]['href'], '/'));
			$info = array(
				'class' => 'Facebook'.ucfirst(end($path)),
				'link' => 'https://developers.facebook.com/'.implode('/', $path).'/'
			);

			$elements = $xml->xpath('//div[@id="bodyText"]');
			$info['fields'] = $this->extractFields($elements[0]->table[0]->tr);
			$info['connections'] = $this->extractFields($elements[0]->table[1]->tr);

			return new Dump($this->generatePhp($info));
		} else {
			return $form;
		}
	}

	/**
	 * Extract info from the rows in a "Fields" or  "Connections" table.
	 * @param \SimpleXMLElement $rows
	 * @return array
	 */
	function extractFields(\SimpleXMLElement $rows) {
		$fields = array();
		foreach ($rows as $row) {
			if ($row->td[0]->b == 'Name') {
				continue; // Skip table header
			}
			$field = array(
				'name' => (string) $row->td[0]->code,
				'description' => strip_tags($row->td[1]->children()->asXML()),
				'permissions' => array(),
				'returns' => strip_tags($row->td[3]->children()->asXML()),
			);
			if ($field['name'] === '') {
				$field['name'] = (string) $row->td[0]->a;
			}
			foreach ($row->td[2]->p->children()->code as $permission) {
				if (in_array((string) $permission, array('access_token'))) {
					continue; // skip "access_token"
				}
				$field['permissions'][] = (string) $permission;
			}
			$fields[] = $field;
		}
		return $fields;
	}

	/**
	 *
	 * @param array $info
	 */
	function generatePhp($info) {
		$php = "<?php\n";
		$php .= "/**\n";
		$php .= " * ".$info['class']."\n";
		$php .= " */\n";
		$php .= "namespace Sledgehammer;\n";
		$php = "/**\n";
		$php .= " * \n"; // todo general description
		$php .= " */\n";
		$php .= "class ".$info['class']." extends GraphObject {\n";
		foreach ($info['fields'] as $field) {
			$php .= "\t\n";
			$php .= "\t/**\n";
			$php .= "\t * ".$field['description'].".\n";
			if (in_array($field['returns'], array('number', 'string'))) {
				$php .= "\t * @var ".$field['returns']."\n";
			} else {
				$php .= "\t *\n\t * ".$field['returns']."\n";
			}
			$php .= "\t */\n";
			$php .= "\t public $".$field['name'].";\n";
		}
		$knownConnections = '';
		foreach ($info['connections'] as $connection) {
			$php .= "\t\n";
			$php .= "\t/**\n";
			$php .= "\t * ".$connection['description'].".\n";
			$php .= "\t *\n\t * ".$connection['returns']."\n";
			// @todo add permissions documentation
			$php .= "\t * @var Collection|GraphObject\n";
			$php .= "\t */\n";
			$php .= "\t public $".$connection['name'].";\n";
			$knownConnections .= "\t\t\t'".$connection['name']."' => array(),\n";
		}
		$php .= "\t\n";
		// @todo generate getFieldPermissions()
		$php .= "\tprotected static function getKnownConnections(\$options = array()) {\n";
		$php .= "\t\t\$connections = array(\n";
		$php .= $knownConnections;
		$php .= "\t\t);\n";
		// @todo Add permissions based on 'user/friend' option.
		$php .= "\t\treturn \$connections;\n";
		$php .= "\t}\n\n";
		$php .= "}\n\n";
		$php .= "?>";
		return $php;
	}

}

?>
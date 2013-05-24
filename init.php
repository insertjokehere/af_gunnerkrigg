<?php
class Af_Gunnerkrigg extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Display Gunnerkrigg Court comics inside feed",
			"Will Hughes");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "gunnerkrigg.com") !== FALSE) {
			if (strpos($article["plugin_data"], "gunnerkrigg,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@class="comic_image"])');

					$matches = array();

					foreach ($entries as $entry) {

						if (preg_match("/(.*\/comics\/.*)/i", $entry->getAttribute("src"), $matches)) {
							$entry->setAttribute("src","http://www.gunnerkrigg.com".$entry->getAttribute("src")); //The image URL on the site does not contain a domain component, only a path
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode) . "<br />" . $article["content"];
						$article["plugin_data"] = "gunnerkrigg,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}

	function api_version() {
		return 2;
	}
}
?>
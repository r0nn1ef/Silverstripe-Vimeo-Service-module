<?php
class VimeoSiteConfig extends DataExtension {
	static $db = array(
		'VimeoAPIKey' => 'Varchar(128)',
		'VimeoSecretKey' => 'Varchar(56)'
	);

	public function updateCMSFields(FieldList $fields) {
		// $fields->findOrMakeTab("Root.Vimeo", "Vimeo Settings", "Access");
		$fields->addFieldToTab("Root.Main", new HeaderField(null, "Vimeo Settings"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoAPIKey", "API Key"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoSecretKey", "Secret Key"));
	}
}

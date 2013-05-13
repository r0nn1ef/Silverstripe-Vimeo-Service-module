<?php
class VimeoSiteConfig extends DataExtension {
	static $db = array(
		'VimeoAPIKey' => 'Varchar(128)',
		'VimeoSecretKey' => 'Varchar(56)',
		'VimeoDefaultWidth' => 'Int',
		'VimeoDefaultHeight' => 'Int',
		'VimeoPlayerBase' => 'Varchar(128)'
	);

	static $defaults = array(
		'VimeoDefaultWidth' => 640,
		'VimeoDefaultHeight' => 360,
		'VideoPlayerBase' => 'http://player.vimeo.com/video/'
	);

	public function updateCMSFields(FieldList $fields) {
		$fields->addFieldToTab("Root.Main", new HeaderField(null, "Vimeo Settings"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoAPIKey", "API Key"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoSecretKey", "Secret Key"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoDefaultWidth", "Default Video Width"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoDefaultHeight", "Default Video Height"));
		$fields->addFieldToTab("Root.Main", new TextField("VimeoPlayerBase", "Base URL for video player."));
	}

	public function onBeforeWrite() {
		$this->owner->VimeoDefaultWidth = (int)$this->owner->VimeoDefaultWidth == 0 ? 640 : $this->owner->VimeoDefaultWidth;
		$this->owner->VimeoDefaultHeight = (int)$this->owner->VimeoDefaultHeight == 0 ? 360 : $this->owner->VimeoDefaultHeight;
		$this->owner->VimeoPlayerBase .= (substr($this->owner->VimeoPlayerBase, strlen($this->owner->VimeoPlayerBase), 1) != '/' ? '/' : '');
	}
}

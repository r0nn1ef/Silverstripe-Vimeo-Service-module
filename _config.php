<?php
	// TODO: Move all possible values to _config/settings.yml

	// Decorate SiteConfig to hold Vimeo API keys.
	Object::add_extension('SiteConfig', 'VimeoSiteConfig');
	// Decorate StringField so text-based data types have the method URLEncode
	// for use in templates.
	Object::add_extension('StringField', 'StringFieldDOD');

	// adds a simple shortcode parser to include a specific vimeo video in text.
	ShortcodeParser::get('default')->register('vimeo', array('VimeoGalleryPage', 'VimeoShortcodeHandler'));
?>
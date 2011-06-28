<?php
	// Decorate StringField so text-based data types have the method URLEncode
	// for use in templates.
	Object::add_extension('StringField', 'StringFieldDOD');
	
	// adds a simple shortcode parser to include a specific vimeo video in text.
	ShortcodeParser::get('default')->register('vimeo', array('VimeoGalleryPage', 'VimeoShortcodeHandler'));
?>
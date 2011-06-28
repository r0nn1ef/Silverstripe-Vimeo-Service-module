<?php
	class StringFieldDOD extends DataObjectDecorator {
		/**
		 * Returns the value of the field URL encoded.
		 * @return string;
		 */
		function URLEncode() {
			return $this->owner->getValue() ? urlencode($this->owner->getValue()) : '';
		}
	}

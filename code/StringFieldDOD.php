<?php
    class StringFieldDOD extends Extension
    {
        /**
         * Returns the value of the field URL encoded.
         * @return string;
         */
        public function URLEncode()
        {
            return $this->owner->getValue() ? urlencode($this->owner->getValue()) : '';
        }
    }

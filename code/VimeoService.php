<?php
/**
 * Service class to interface SilverStripe with the Vimeo video sharing service.
 *
 * @author Ronald Ferguson
 * @version 1.0
 * @package vimeoservice
 */
class VimeoService Extends RestfulService {

	/**
	 * Default player width
	 * @access protected
	 * @static
	 */
	protected static $player_width = 400;

	/**
	 * Default player height
	 * @access protected
	 * @static
	 */
	protected static $player_height = 225;

	/**
	 * API key supplied by Vimeo to use the advanced api
	 * @access protected
	 */
	protected $api_key;

	/**
	 * API key secret supplied by Vimeo for use with the API key
	 * @access protected

	 */
	protected $api_secret_key;

	protected static $token;

	protected static $token_secret;

	/**
	 * Base URL for the Vimeo advanced api
	 * @static
	 */
	public static $api_base_url = 'http://vimeo.com/api/rest/v2';

	/**
	 * @var integer
	 * @access protected
	 */
	protected $video_count;

	/**
	 * @var integer
	 * @access protected
	 */
	protected $page_count;

	/**
	 * Constructor
	 * @param Set the cache expiry interva. Defaults to 1 hour (3600 seconds)
	 * @see RestfulService
	 * @return void
	 */
	function __construct($apiKey, $apiSecretKey, $expiry=NULL) {
		parent::__construct(self::$api_base_url, $expiry);
		$this->api_key = $apiKey;
		$this->api_secret_key = $apiSecretKey;
		$this->checkErrors = true;
	}

	function errorCatch($response){
		$err_msg = $response;
		if(strpos($err_msg, '<') === false) user_error("VimeoService Error: $err_msg", E_USER_ERROR);

		return $response;
	}

	/**
	 * Set the API key supplied by Vimeo
	 * @param string
	 */
	public function setAPIKey($value) {
		$this->api_key = trim($value);
	}

	/**
	 * Set the API key secret provided by Vimeo.
	 * @param string
	 */
	public function setSecretKey($value) {
		$this->api_secret_key = trim($value);
	}

	public static function setDefaultWidth($value) {
		$value = intval($value);
		if($value == 0) {
			$value = 400;
		}
		self::$player_width = $value;
	}

	public static function setDefaultHeight($value) {
		$value = intval($value);
		if($value == 0) {
			$value = 225;
		}
		self::$player_height = $value;
	}

	public static function getDefaultHeight() {
		return self::$player_height;
	}

	public static function getDefaultWidth() {
		return self::$player_width;
	}

	/**
	 * Returns a collection of Vimeo videos for a given method.
	 * @param string
	 * @param array
	 * @param integer
	 * @return DataObjectSet or boolean false
	 */
	function getVideoFeed($method=NULL, $call_params=array(), $page_limit=10) {
		if(! $this->api_key || ! $this->api_secret_key) {
			user_error('Fatal error: Invalid API keyand/or secret specified!');
			return false;
		}

		// Prepare oauth arguments
        $oauth_params = array(
            'oauth_consumer_key' => $this->api_key,
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->_generateNonce()
        );

        $page = $call_params['start'] == 0 ? 1 : (($call_params['start']) / $page_limit + 1);

        if(self::$token) {
        	$oauth_params['oauth_token'] = self::$token;
        }

		$api_params = array(
			'format' => 'php',
			'full_response' => 1,
			'per_page' => $page_limit,
			'page' => $page
		);

        if (!empty($method)) {
            $api_params['method'] = $method;
        }

		// Merge args
        foreach ($call_params as $k => $v) {
            if (strpos($k, 'oauth_') === 0) {
                $oauth_params[$k] = $v;
            }
            else {
                $api_params[$k] = $v;
            }
        }

        // Generate the signature
        $oauth_params['oauth_signature'] = $this->_generateSignature(array_merge($oauth_params, $api_params), self::$api_base_url);

        // Merge all args
        $params = array_merge($oauth_params, $api_params);

		$this->setQueryString($params);

		$response = $this->request();

		$videos = unserialize($response->getBody());

		if(isset($videos->videos)) {
			$results = new ArrayList();
			foreach($videos->videos->video as $video) {
				/*
				 * @todo Add sorting
				 */
				$results->push(new ArrayData($this->_extractVideoInfo($video)));
			}

			// since we manually created the dataobjectset, we need to set the pager info manually, too.
			// $results->setPageLimits($call_params['start'], $page_limit, intval($videos->videos->total));
		} else {
			$results = false;
		}

		return $results;
	}

	/**
	 * Returns a Vimeo video object
	 * @param array
	 * @return ArrayData or boolean false
	 */
	function getSingleVideo($call_params=array()) {
		if(!$this->api_key || ! $this->api_secret_key) {
			user_error('Fatal error: Invalid API keyand/or secret specified!');
			return false;
		}

		// Prepare oauth arguments
        $oauth_params = array(
            'oauth_consumer_key' => $this->api_key,
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_nonce' => $this->_generateNonce()
        );

        if(self::$token) {
        	$oauth_params['oauth_token'] = self::$token;
        }

		$api_params = array(
			'format' => 'php',
			'method' => 'vimeo.videos.getInfo'
		);

		// Merge args
        foreach ($call_params as $k => $v) {
            if (strpos($k, 'oauth_') === 0) {
                $oauth_params[$k] = $v;
            }
            else {
                $api_params[$k] = $v;
            }
        }

        // Generate the signature
        $oauth_params['oauth_signature'] = $this->_generateSignature(array_merge($oauth_params, $api_params), self::$api_base_url);

        // Merge all args
        $params = array_merge($oauth_params, $api_params);

		$this->setQueryString($params);

		$response = $this->request();

		$video = unserialize($response->getBody());

		if(isset($video->video)) {

			foreach($video->video as $video) {
				$data = $this->_extractVideoInfo($video);
				$results =new ArrayData($data);
			}
		} else {
			$results = false;
		}

		return $results;
	}

	/**
	 * Extracts and populates an array structure with the video information returned from Vimeo for a single video object.
	 * @param Object
	 * @return array
	 */
	private function _extractVideoInfo($data) {
		$video = array();
		$video['ID'] = intval($data->id);
		$video['Title'] = new Text(null);
		$video['Title']->setValue(trim($data->title));
		/*
		 * need to clean up the text returned from Vimeo and paragraph format it so we can use
		 * all the nifty SilverStripe text functionality. :)
		 */
		$desc = nl2br(trim($data->description));
		$desc = str_replace("<br />\n<br />", '<br />', $desc);
		$desc = '<p>' . str_replace('<br />', '</p><p>', $desc) . '</p>';
		$video['Description'] = new HTMLText();
		$video['Description']->setValue($desc);

		// video url info
		foreach($data->urls->url as $url) {
			switch($url->type) {
				case 'video':
					$video['Url'] = new Text();
					$video['Url']->setValue(trim($url->_content));
					break;
				case 'mobile':
					$video['MobileUrl'] = new Text();
					$video['MobileUrl']->setValue(trim($url->_content));
					break;
				default:
					// do nothing.
					break;
			}
		}

		// video date info
		$upload_date = trim($data->upload_date);
		// create a SS_Datetime object so we can use familiar functionality.
		$ud = new SS_Datetime();
		$ud->setValue($upload_date);
		$video['UploadDate'] = $ud;
		$modified_date = trim($data->modified_date);
		// create a SS_Datetime object so we can use familiar functionality.
		$md = new SS_Datetime();
		$md->setValue($modified_date);
		$video['ModifiedDate'] = $md;

		// video thumbnail info
		foreach($data->thumbnails->thumbnail as $tn) {
			if(strpos($tn->_content, '_100.jpg') !== false) {
				$video['ThumbSmall'] = new Text();
				$video['ThumbSmall']->setValue(trim($tn->_content));
			} elseif (strpos($tn->_content, '_200.jpg') !== false) {
				$video['ThumbMedium'] = new Text();
				$video['ThumbMedium']->setValue(trim($tn->_content));
			} elseif (strpos($tn->_content, '_640.jpg') !== false) {
				$video['ThumbLarge'] = new Text();
				$video['ThumbLarge']->setValue(trim($tn->_content));
			}
		}

		// User information
		$video['UserName'] = new Varchar(null, 255);
		$video['UserName']->setValue(trim($data->owner->username));
		$video['UserDisplayName'] = new Varchar(null, 255);
		$video['UserDisplayName']->setValue(trim($data->owner->display_name));
		$video['UserRealName'] = new Varchar(null, 255);
		$video['UserRealName']->setValue(trim($data->owner->realname));
		$video['UserID'] = (int)trim($data->owner->id);
		$video['UserUrl'] = new Text();
		$video['UserUrl']->setValue(trim($data->owner->profileurl));
		// User photos
		for($i=0;$i<count($data->owner->portraits->portrait);$i++) {
			$tn = $data->owner->portraits->portrait[$i];
			switch($i) {
				case 0:
					$video['UserPortraitSmall'] = new Text();
					$video['UserPortraitSmall']->setValue(trim($tn->_content));
					break;
				case 1:
					$video['UserPortraitMedium'] = new Text();
					$video['UserPortraitMedium']->setValue(trim($tn->_content));
					break;
				case 2:
					$video['UserPortraitLarge'] = new Text();
					$video['UserPortraitLarge']->setValue(trim($tn->_content));
					break;
				case 3:
					$video['UserPortraitXLarge'] = new Text();
					$video['UserPortraitXLarge']->setValue(trim($tn->_content));
					break;
				default:
					// do nothing.
					break;
			}
		}

		$video['NumberLikes'] = intval(trim($data->number_of_likes));
		$video['NumberPlays'] = intval(trim($data->number_of_plays));
		$video['NumberComments'] = intval(trim($data->number_of_comments));
		$video['Duration'] = intval(trim($data->duration));
		$video['Width'] = intval(trim($data->width));
		$video['Height'] = intval(trim($data->height));
		$video['IsHD'] = new Boolean(null);
		$video['IsHD']->setValue(intval($data->is_hd));
		$video['EmbedPrivacy'] = new Text();
		$video['EmbedPrivacy']->setValue(trim($data->embed_privacy));

		return $video;
	}

	/**
	 * Generates unique identifier used in OAuth signature.
	 * @access protected
	 * @return string
	 */
	protected function _generateNonce() {
		return md5(uniqid(microtime()));
	}

	/**
	 * Generates OAuth signature required to call Vimeo advanced API functions.
	 * @param array
	 * @param string
	 * @param string
	 * @access protected
	 * @return string
	 * @see https://github.com/vimeo/vimeo-php-lib
	 */
	protected function _generateSignature($params, $url, $request_method = 'GET') {
        uksort($params, 'strcmp');
        $params = self::url_encode_rfc3986($params);

        // Make the base string
        $base_parts = array(
            strtoupper($request_method),
            $url,
            urldecode(http_build_query($params, '', '&'))
        );
        $base_parts = self::url_encode_rfc3986($base_parts);
        $base_string = implode('&', $base_parts);

        // Make the key
        $key_parts = array(
            $this->api_secret_key,
            (self::$token_secret) ? self::$token_secret : ''
        );
        $key_parts = self::url_encode_rfc3986($key_parts);
        $key = implode('&', $key_parts);

        // Generate signature
        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }

	/**
	 * Gets paged video list for videos of a specific user.
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @return DataObjectSet or boolean false
	 * @see getVideoFeed()
	 */
	public function getVideosByUser($userid, $start=0, $per_page=10, $sort=null) {
		$params = array('user_id' => $userid, 'format' => 'php', 'start' => intval($start));
		if($sort) $params['sort'] = $sort;
		$method = 'vimeo.videos.getAll';
		return $this->getVideoFeed($method, $params, $per_page);
	}

	/**
	 * Gets paged video list for videos of a specific Vimeo group.
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @return DataObjectSet or boolean false
	 * @see getVideoFeed()
	 */
	public function getVideosByGroup($groupid, $start=0, $per_page=10, $sort=null) {
		$params = array('group_id' => $groupid, 'format' => 'php', 'start' => intval($start));
		if($sort) $params['sort'] = $sort;
		$method = 'vimeo.groups.getVideos';
		return $this->getVideoFeed($method, $params, $per_page);
	}

	/**
	 * Gets paged video list for videos of a specific Vimeo album.
	 * @param integer
	 * @param integer
	 * @param integer
	 * @param string
	 * @return DataObjectSet or boolean false
	 * @see getVideoFeed()
	 */
	public function getVideosByAlbum($albumid, $start=0, $per_page=10, $sort=null) {
		// vimeo.groups.getVideos
		$params = array('album_id' => $albumid, 'format' => 'php', 'start' => intval($start));
		if($sort) $params['sort'] = $sort;
		$method = 'vimeo.albums.getVideos';
		return $this->getVideoFeed($method, $params, $per_page);
	}

	/**
	 * Gets specific Vimeo video.
	 * @param integer
	 * @return ArrayData or boolean false
	 * @see getSingleVideo()
	 */
	public function getVideoById($videoid) {
		$params = array('video_id' => $videoid);
		return $this->getSingleVideo($params);
	}

	/**
	 * URL encode a parameter or array of parameters.
	 * @param mixed
	 * @return mixed
	 */
	public static function url_encode_rfc3986($input) {
        if (is_array($input)) {
            return array_map(array('VimeoService', 'url_encode_rfc3986'), $input);
        }
        else if (is_scalar($input)) {
            return str_replace(array('+', '%7E'), array(' ', '~'), rawurlencode($input));
        }
        else {
            return '';
        }
    }
}

?>
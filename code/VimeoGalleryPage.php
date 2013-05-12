<?php


class VimeoGalleryPage extends Page {

	public static $singular_name = 'Vimeo Gallery Page';

	public static $plural_name = 'Vimeo Gallery Pages';

	public static $db = array(
		'Method' => 'Int',
		'User' => 'Varchar(100)',
		'VideosPerPage' => 'Int',
		'SortField' => 'Varchar(100)'
	);

	public static $defaults = array(
		'Method' => 1,
		'ShowVideoInPopup' => true,
		'VideosPerPage' => 10,
		'SortField' => 'UploadDate'
	);

	public static $allowed_children = array();

	public static $icon = 'vimeoservice/images/vimeo-file.gif';

	protected $_cachedVideos = null;

	function getCMSFields() {
		$fields = parent::getCMSFields();

		// $fields->findOrMakeTab("Root.Vimeo", _t('VimeoGalleryPage.SETTINGS', "Vimeo"));
		// var_dump($tab);exit;
		$fields->addFieldToTab('Root.Vimeo', new DropdownField("Method", _t('VimeoGalleryPage.SELECT', "Select"), array(
			'1' => _t('VimeoGalleryPage.USER', 'User'),
			'2' => _t('VimeoGalleryPage.GROUP', 'Group'),
			'3' => _t('VimeoGalleryPage.ALBUM', 'Album')
		)));

		$fields->addFieldToTab("Root.Vimeo", new TextField("User", _t('VimeoGalleryPage.USER_ID_LABEL', "Vimeo Username/Vimeo Group Name/Vimeo Album ID")));

		$fields->addFieldsToTab("Root.Vimeo", new DropdownField("VideosPerPage", _t('VimeoGalleryPage.VIDEOS_PER_PAGE', "Number of videos per page"), array(
			'10' => '10',
			'20' => '20',
			'30' => '30',
			'40' => '40',
			'50' => '50'
		)));

		$fields->addFieldToTab("Root.Vimeo", new DropdownField("SortField", _t('VimeoGalleryPage.SORT_BY', "Sort by"), array(
			'newest' => _t('VimeoGalleryPage.NEWEST', 'Newest'),
			'oldest' => _t('VimeoGalleryPage.OLDEST', 'Oldest'),
			'most_played' => _t('VimeoGalleryPage.MOST_PLAYED', 'Most played'),
			'most_commented' => _t('VimeoGalleryPage.MOST_COMMENTED', 'Most commented'),
			'most_liked' => _t('VimeoGalleryPage.MOST_LIKED', 'Most liked')
		)));

		return $fields;
	}

	function VimeoVideos() {

		if($this->_cachedVideos) return $this->_cachedVideos;

		$config = SiteConfig::current_site_config();

		$vimeo = new VimeoService($config->VimeoAPIKey, $config->VimeoSecretKey);
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		$per_page = intval($this->VideosPerPage) < 10 ? 10 : intval($this->VideosPerPage);
		switch($this->Method) {
			case 1:
				$videos = $vimeo->getVideosByUser($this->User, $start, $per_page, $this->SortField);
				break;
			case 2:
				$videos = $vimeo->getVideosByGroup($this->User, $start, $per_page, $this->SortField);
				break;
			case 3:
				$videos = $vimeo->getVideosByAlbum($this->User, $start, $per_page, $this->SortField);
				break;
			default:
				return false;
		}

		$this->_cachedVideos = $videos;

		return $videos;
	}

	function flushCache() {
		parent::flushCache();
		unset($this->_cachedVideos);
	}

	public static function VimeoShortcodeHandler($attributes, $content=null, $parser=null) {
		if(!isset($attributes['id'])) return '';
		$width = isset($attributes['width']) ? intval($attributes['width']) : VimeoService::getDefaultWidth();
		$height = isset($attributes['height']) ? intval($attributes['height']) : VimeoService::getDefaultHeight();
		if($width == 0) $width = VimeoService::getDefaultWidth();
		if($height == 0) $width = VimeoService::getDefaultHeight();

		return "<iframe src='http://player.vimeo.com/video/{$attributes['id']}' width='{$width}' height='{$height}' frameborder='0'></iframe>";
	}

}

class VimeoGalleryPage_Controller extends Page_Controller {

	static $allowed_actions = array(
				'index',
				'view'
			);

	// Since we are pulling the video data from a remote service, we'll want to store the video title
	// for use in the breadcrumbs later.
	protected $_videoTitle;

	function init() {
		if(Director::fileExists(project() . "/css/VimeoGallery.css")) {
			Requirements::css(project() . "/css/VimeoGallery.css");
		} elseif(Director::fileExists('themes/' . project() . "/css/VimeoGallery.css")) {
			Requirements::css('themes/' . project() . "/css/VimeoGallery.css");
		} else {
			Requirements::css("vimeoservice/css/VimeoGallery.css");
		}

		parent::init();
	}

	function isUserRequest() {
		return $this->Method == 1 ? true : false;
	}

	function isGroupRequest() {
		return $this->Method == 2 ? true : false;
	}

	function getVideo() {
		$params = $this->getURLParams();
		if(is_numeric($params['ID'])) {
			$config = SiteConfig::current_site_config();
			$vimeo = new VimeoService($config->VimeoAPIKey, $config->VimeoSecretKey);
			$video = $vimeo->getVideoById($params['ID']);
			return $video;
		} else {
			return false;
		}
	}

	function view() {

		if($video = $this->getVideo()) {
			$this->_videoTitle = $video->Title->getValue();
			$data = array('Video' => $video);
			return $this->Customise($data);
		} else {
			return $this->httpError(404, _t('VimeoGalleryPage.VIDEO_NOT_FOUND', 'Sorry that video could not be found.'));
		}
	}

	///////////////////////// Page control functions ////////////////////////////


	function Breadcrumbs() {

		//Get the default breadcrumbs
        $Breadcrumbs = parent::Breadcrumbs();

		// If we are viewing a single video, add link back to the index action of this controller
		// and add the video title to the breadcrumbs.
		$params = $this->getURLParams();
		if($params['Action'] == 'view') {
			// $lastIdx = count($Parts)-1;
			// $Parts[$lastIdx] = '<a href="' . $this->Link() . '">' . $Parts[$lastIdx] . '</a>';
			// $Parts[] = !$this->_videoTitle ? _t('VimeoGalleryPage.UNTITLED_VIDEO', 'Untitled Video') : $this->_videoTitle;
		}

		return $Breadcrumbs;
	}
}
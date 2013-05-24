<?php

class VimeoGalleryPage extends Page {

	public static $singular_name = 'Vimeo Gallery Page';

	public static $plural_name = 'Vimeo Gallery Pages';

	public static $db = array(
		'Method' => 'Int',
		'User' => 'Varchar(100)',
		'VideosPerPage' => 'Int',
		'SortField' => 'Varchar(100)',
		'VideoWidth' => 'Int',
		'VideoHeight' => 'Int',
		'VideoTitle' => 'Int',
		'VideoByLine' => 'Int',
		'VideoPortrait' => 'Int',
		'VideoColor' => 'Varchar(36)',
		'VideoAutoPlay' => 'Int',
		'VideoLoop' => 'Int',
		'VideoAPI' => 'Int',
		'VideoPlayerID' => 'Varchar(128)'
	);

	public static $defaults = array(
		'Method' => 1,
		'ShowVideoInPopup' => TRUE,
		'VideosPerPage' => 10,
		'SortField' => 'UploadDate',
		'VideoWidth' => 640,
		'VideoHeight' => 360,
		'VideoTitle' => 1,
		'VideoByLine' => 1,
		'VideoPortrait' => 1,
		'VideoColor' => '',
		'VideoAutoPlay' => 0,
		'VideoLoop' => 0,
		'VideoAPI' => 1,
		'VideoPlayerID' => ''
	);

	public static $allowed_children = array();

	public static $icon = 'vimeoservice/images/vimeo-file.gif';

	protected $_videos = NULL;

	protected $_current_page = NULL;

	protected $_total_videos = NULL;

	protected $_cachedVideos = NULL;

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$vimeoToggle = ToggleCompositeField::create('Vimeo', "Video Settings",
						array(
							new DropdownField("Method", _t('VimeoGalleryPage.SELECT', "Select"), array(
								'1' => _t('VimeoGalleryPage.USER', 'User'),
								'2' => _t('VimeoGalleryPage.GROUP', 'Group'),
								'3' => _t('VimeoGalleryPage.ALBUM', 'Album')
							)),
							new TextField("User", _t('VimeoGalleryPage.USER_ID_LABEL', "Vimeo Username/Vimeo Group Name/Vimeo Album ID")),
							new DropdownField("VideosPerPage", _t('VimeoGalleryPage.VIDEOS_PER_PAGE', "Number of videos per page"), array(
								'10' => '10',
								'20' => '20',
								'30' => '30',
								'40' => '40',
								'50' => '50'
							)),
							new DropdownField("SortField", _t('VimeoGalleryPage.SORT_BY', "Sort by"), array(
								'newest' => _t('VimeoGalleryPage.NEWEST', 'Newest'),
								'oldest' => _t('VimeoGalleryPage.OLDEST', 'Oldest'),
								'most_played' => _t('VimeoGalleryPage.MOST_PLAYED', 'Most played'),
								'most_commented' => _t('VimeoGalleryPage.MOST_COMMENTED', 'Most commented'),
								'most_liked' => _t('VimeoGalleryPage.MOST_LIKED', 'Most liked')
							)),
							new TextField("VideoWidth", "Video Width"),
							new TextField("VideoHeight", "Video Height"),
							new CheckboxField("VideoTitle", "Display video title on the videos."),
							new CheckboxField("VideoByLine", "Show the user's byline on the videos."),
							new CheckboxField("VideoPortrait", "Show the user's portrait on the videos."),
							new CheckboxField("VideoAutoPlay", "Auto play videos when viewed."),
							new TextField("VideoColor", "Specify the color of the video controls. Make sure that you don't include the #."),
							new CheckboxField("VideoAPI", "Enable Javascript API."),
							new TextField("VideoPlayerID", "Video player ID (required when using the Javascript API.)")
						)
					)->setHeadingLevel(4);

		$fields->insertBefore($vimeoToggle, "Metadata");

		return $fields;
	}

	function VimeoVideos($start = 0) {
		// TODO: Add TRUE SS cache for Vimeo API calls
		// if($this->_cachedVideos) return $this->_cachedVideos;

		$config = SiteConfig::current_site_config();

		$vimeo = new VimeoService();
		$per_page = intval($this->VideosPerPage) < 10 ? 10 : intval($this->VideosPerPage);
		// Used in the pager
		$this->_current_page = $start == 0 ? 1 : (floor($start / $per_page) + 1);

		switch($this->Method) {
			case 1:
				$result = $vimeo->getVideosByUser($this->User, $start, $per_page, $this->SortField);
				break;
			case 2:
				$result = $vimeo->getVideosByGroup($this->User, $start, $per_page, $this->SortField);
				break;
			case 3:
				$result = $vimeo->getVideosByAlbum($this->User, $start, $per_page, $this->SortField);
				break;
			default:
				$result = FALSE;
		}

		if($result) {
			$this->_videos = $result['videos'];
			$this->_total_videos = $result['total'];
		}

		return $this->_videos;
	}

	public function getVideos() {
		return (!$this->_videos ? FALSE : $this->_videos);
	}

	public function getTotalVideos() {
		return (!$this->_total_videos ? 0 : $this->_total_videos);
	}

	public function getCurrentPage() {
		return (!$this->_current_page ? 1 : $this->_current_page);
	}

	function flushCache() {
		parent::flushCache();
		unset($this->_cachedVideos);
	}

	public static function VimeoShortcodeHandler($attributes, $content=NULL, $parser=NULL) {
		if(!isset($attributes['id'])) return '';
		$width = isset($attributes['width']) ? intval($attributes['width']) : SiteConfig::current_site_config()->VimeoDefaultWidth;
		$height = isset($attributes['height']) ? intval($attributes['height']) : SiteConfig::current_site_config()->VimeoDefaultHeight;
		if($width == 0) $width = VimeoService::getDefaultWidth();
		if($height == 0) $width = VimeoService::getDefaultHeight();

		$playerURL = SiteConfig::current_site_config()->VimeoPlayerBase . $attributes['id'];

		$params = array();

		if(isset($attributes['autoplay']) && strtolower($attributes['autoplay']) === 'yes') {
			$params[] = 'autoplay=1';
		}
		if(isset($attributes['color'])) {
			$params[] = 'color=' . str_replace('#', '', trim($attributes['color']));
		}
		if(isset($attributes['title']) && strtolower($attributes['title']) === 'no') {
			$params[] = 'title=0';
		}
		if(isset($attributes['portrait']) && strtolower($attributes['portrait']) === 'no') {
			$params[] = 'portrait=0';
		}
		if(isset($attributes['byline']) && strtolower($attributes['byline']) === 'no') {
			$params[] = 'byline=0';
		}
		if(isset($attributes['loop']) && strtolower($attributes['loop']) === 'yes') {
			$params[] = 'loop=1';
		}
		if(isset($attributes['api']) && strtolower($attributes['api']) == 'yes') {
			$params[] = 'api=1';
			if(isset($attributes['player_id'])) {
				$params[] = 'player_id=' . trim($attributes['player_id']);
			}
		}

		$playerURL .= (!$params ? '' : '?' . implode('&', $params));

		return "<iframe src='{$playerURL}' width='{$width}' height='{$height}' frameborder='0' id='vimeo-video-{$attributes['id']}'></iframe>";
	}

	public function onBeforeWrite() {
		$this->VideoColor = str_replace('#', '', $this->VideoColor);
		parent::onBeforeWrite();
	}

}

class VimeoGalleryPage_Controller extends Page_Controller {

	static $allowed_actions = array(
				'index',
				'view'
			);

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
		return $this->Method == 1 ? TRUE : FALSE;
	}

	function isGroupRequest() {
		return $this->Method == 2 ? TRUE : FALSE;
	}

	function getVideo($video_id) {

		if(is_numeric($video_id)) {
			$config = SiteConfig::current_site_config();
			$vimeo = new VimeoService();
			$video = $vimeo->getVideoById($video_id);
			return $video;
		} else {
			return FALSE;
		}
	}

	protected function buildVideoURL() {
		if(!$this->_video) return FALSE;

		$url = 'http://player.vimeo.com/video/' . $this->_video->ID;
		$params = array();

		if($this->VideoAutoPlay) {
			$params[] = 'autoplay=1';
		}
		if($this->VideoColor) {
			$params[] = 'color=' . trim($this->VideoColor);
		}
		if(!$this->VideoTitle) {
			$params[] = 'title=0';
		}
		if(!$this->VideoPortrait) {
			$params[] = 'portrait=0';
		}
		if(!$this->VideoByLine) {
			$params[] = 'byline=0';
		}
		if($this->VideoLoop) {
			$params[] = 'loop=1';
		}
		if($this->VideoAPI) {
			$params[] = 'api=1';
		}
		if($this->VideoPlayerID) {
			$params[] = 'player_id=' . $this->VideoPlayerID;
		}

		return $url . (!$params ? '' : '?' . implode('&', $params));
	}

	public function index() {
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		return $this->Customise(array('Videos' => $this->VimeoVideos($start)));
	}

	function view() {
		$params = $this->getURLParams();
		$video_id = !$params['ID'] ? '' : $params['ID'];

		if($this->_video = $this->getVideo($video_id)) {
			$data = array('Video' => $this->_video, 'VideoURL' => $this->buildVideoURL());
			return $this->Customise($data);
		} else {
			return $this->httpError(404, _t('VimeoGalleryPage.VIDEO_NOT_FOUND', 'Sorry that video could not be found.'));
		}

	}

	///////////////////////// Page control functions ////////////////////////////

	function Title() {
		$title = $this->Title;

		if($this->_video) {
			$title = $this->_video->Title->getValue() . ' » ' . $title;
		}
		return $title;
	}

	function Breadcrumbs($separator = ' &raquo; ') {

		//Get the default breadcrumbs
        $Breadcrumbs = parent::getBreadcrumbs($separator);
		$Parts = explode($separator, $Breadcrumbs);
		// If we are viewing a single video, add link back to the index action of this controller
		// and add the video title to the breadcrumbs.
		$params = $this->getURLParams();
		if($params['Action'] == 'view') {
			$lastIdx = count($Parts)-1;
			$Parts[$lastIdx] = '<a href="' . $this->Link() . '">' . $Parts[$lastIdx] . '</a>';
			$Parts[] = !$this->_video->Title ? _t('VimeoGalleryPage.UNTITLED_VIDEO', 'Untitled Video') : $this->_video->Title->getValue();
		}

		$Breadcrumbs = implode($separator, $Parts);

		return $Breadcrumbs;
	}

	function PaginatedPages() {
		$pager = new PaginatedList($this->Videos, $this->request);
		$pager->setPageLength($this->VideosPerPage);
		$pager->setCurrentPage($this->CurrentPage);
		$pager->setTotalItems($this->TotalVideos);
		$pager->setLimitItems(TRUE);
		return $pager;
	}
}
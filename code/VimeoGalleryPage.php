<?php


class VimeoGalleryPage extends Page
{
    
    public static $singular_name = 'Vimeo Gallery Page';
    
    public static $plural_name = 'Vimeo Gallery Pages';
    
    public static $db = array(
        'Method' => 'Int',
        'User' => 'Varchar(100)',
        'VideosPerPage' => 'Int',
        'ShowVideoInPopup' => 'Boolean',
        'PopupTheme' => 'Varchar(20)',
        'PopupWidth' => 'Int',
        'PopupHeight' => 'Int',
        'SortField' => 'Varchar(100)'
    );
    
    public static $defaults = array(
        'Method' => 1,
        'ShowVideoInPopup' => true,
        'VideosPerPage' => 10,
        'PopupTheme' => 'default',
        'PopupWidth' => 400,
        'PopupHeight' => 225,
        'SortField' => 'UploadDate'
    );
    
    public static $allowed_children = array();
    
    public static $icon = 'vimeoservice/images/vimeo';
    
    protected $_cachedVideos = null;
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        
        $fields->addFieldToTab('Root.Videos', new DropdownField("Method", _t('VimeoGalleryPage.SELECT', "Select"), array(
            '1' => _t('VimeoGalleryPage.USER', 'User'),
            '2' => _t('VimeoGalleryPage.GROUP', 'Group'),
            '3' => _t('VimeoGalleryPage.ALBUM', 'Album')
        )));
        
        $fields->addFieldToTab("Root.Videos", new TextField("User", _t('VimeoGalleryPage.USER_ID_LABEL', "Vimeo Username/Vimeo Group Name/Vimeo Album ID")));
        
        $fields->addFieldsToTab("Root.Videos", new DropdownField("VideosPerPage", _t('VimeoGalleryPage.VIDEOS_PER_PAGE', "Number of videos per page"), array(
            '10' => '10',
            '20' => '20',
            '30' => '30',
            '40' => '40',
            '50' => '50'
        )));
        
        $fields->addFieldToTab("Root.Videos", new DropdownField("SortField", _t('VimeoGalleryPage.SORT_BY', "Sort by"), array(
            'newest' => _t('VimeoGalleryPage.NEWEST', 'Newest'),
            'oldest' => _t('VimeoGalleryPage.OLDEST', 'Oldest'),
            'most_played' => _t('VimeoGalleryPage.MOST_PLAYED', 'Most played'),
            'most_commented' => _t('VimeoGalleryPage.MOST_COMMENTED', 'Most commented'),
            'most_liked' => _t('VimeoGalleryPage.MOST_LIKED', 'Most liked')
        )));
        
        $fields->addFieldToTab("Root.Videos", new CheckboxField("ShowVideoInPopup", _t('VimeoGalleryPage.VIDEO_IN_POPUP', "Show video in popup?")));
        
        $fields->addFieldToTab("Root.Videos", new DropdownField("PopupTheme", _t('VimeoGalleryPage.POPUP_THEME', "Popup theme"), array(
            'default' => _t('VimeoGalleryPage.DEFAULT', 'Default'),
            'dark_square' => _t('VimeoGalleryPage.DARK_SQUARE', 'Dark Square'),
            'dark_rounded' => _t('VimeoGalleryPage.DARK_ROUNDED', 'Dark Rounded'),
            'light_square' => _t('VimeoGalleryPage.LIGHT_SQUARE', 'Light Square'),
            'light_rounded' => _t('VimeoGalleryPage.LIGHT_ROUNDED', 'Light Rounded'),
            'facebook' => _t('VimeoGalleryPage.FACEBOOK', 'Facebook')
        )));
        
        $fields->addFieldToTab("Root.Videos", new Textfield("PopupWidth", _t('VimeoGalleryPage.POPUP_WIDTH', "Popup width")));
        
        $fields->addFieldToTab("Root.Videos", new Textfield("PopupHeight", _t('VimeoGalleryPage.POPUP_HEIGHT', "Popup height")));
        
        return $fields;
    }
    
    public function VimeoVideos()
    {
        if ($this->_cachedVideos) {
            return $this->_cachedVideos;
        }
        
        $vimeo = new VimeoService();
        $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
        $per_page = intval($this->VideosPerPage) < 10 ? 10 : intval($this->VideosPerPage);
        switch ($this->Method) {
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
        
        //$sort_direction = ($this->SortField == 'Title' ? 'ASC' : 'DESC');
        //$videos->sort($this->SortField, $sort_direction);

        $this->_cachedVideos = $videos;
        
        return $videos;
    }
    
    public function flushCache($persistent = true)
    {
        parent::flushCache();
        unset($this->_cachedVideos);
    }
    
    public static function VimeoShortcodeHandler($attributes, $content=null, $parser=null)
    {
        if (!isset($attributes['id'])) {
            return '';
        }
        $width = isset($attributes['width']) ? intval($attributes['width']) : VimeoService::getDefaultWidth();
        $height = isset($attributes['height']) ? intval($attributes['height']) : VimeoService::getDefaultHeight();
        if ($width == 0) {
            $width = VimeoService::getDefaultWidth();
        }
        if ($height == 0) {
            $width = VimeoService::getDefaultHeight();
        }
        
        return "<iframe src='http://player.vimeo.com/video/{$attributes['id']}' width='{$width}' height='{$height}' frameborder='0'></iframe>";
    }
}

class VimeoGalleryPage_Controller extends Page_Controller
{
    
    public static $allowed_actions = array(
                'index',
                'view'
            );
    
    // Since we are pulling the video data from a remote service, we'll want to store the video title
    // for use in the breadcrumbs later.		
    protected $_videoTitle;
    
    public function init()
    {
        if (Director::fileExists(project() . "/css/VimeoGallery.css")) {
            Requirements::css(project() . "/css/VimeoGallery.css");
        } elseif (Director::fileExists('themes/' . project() . "/css/VimeoGallery.css")) {
            Requirements::css('themes/' . project() . "/css/VimeoGallery.css");
        } else {
            Requirements::css("vimeoservice/css/VimeoGallery.css");
        }

        // only include if necessary

        if ($this->ShowVideoInPopup) {
            Requirements::javascript("vimeoservice/javascript/jquery-1.4.4.min.js");
            Requirements::javascript("vimeoservice/javascript/jquery.prettyPhoto.js");
            Requirements::css('vimeoservice/css/prettyPhoto.css');
            
            $theme = $this->PopupTheme ? $this->PopupTheme : 'default';
            $width = $this->PopupWidth < 1 ? 400 : $this->PopupWidth;
            $height = $this->PopupHeight < 1 ? 225 : $this->PopupHeight;
            
            Requirements::customScript(<<<JS
$(document).ready(function(){
$("a[rel^='prettyPhoto']").prettyPhoto({
theme:'$theme',
default_width: $width,
default_height: $height,
});
});
JS
            );
        }
        
        parent::init();
    }
    
    public function isUserRequest()
    {
        return $this->Method == 1 ? true : false;
    }
    
    public function isGroupRequest()
    {
        return $this->Method == 2 ? true : false;
    }
    
    public function getVideo()
    {
        $params = $this->getURLParams();
        if (is_numeric($params['ID'])) {
            $vimeo = new VimeoService();
            $video = $vimeo->getVideoById($params['ID']);
            return $video;
        } else {
            return false;
        }
    }
    
    public function view()
    {
        if ($video = $this->getVideo()) {
            $this->_videoTitle = $video->Title->getValue();
            $data = array('Video' => $video);
            return $this->Customise($data);
        } else {
            return $this->httpError(404, _t('VimeoGalleryPage.VIDEO_NOT_FOUND', 'Sorry that video could not be found.'));
        }
    }
    
    ///////////////////////// Page control functions ////////////////////////////

    
//	function Breadcrumbs() {
//		//Get the default breadcrumbs
//        $Breadcrumbs = parent::Breadcrumbs();
//		
//		//Explode them into their individual parts
//        $Parts = explode(SiteTree::$breadcrumbs_delimiter, $Breadcrumbs);
//		
//		// If we are viewing a single video, add link back to the index action of this controller
//		// and add the video title to the breadcrumbs.
//		$params = $this->getURLParams();
//		if($params['Action'] == 'view') {
//			$lastIdx = count($Parts)-1;
//			$Parts[$lastIdx] = '<a href="' . $this->Link() . '">' . $Parts[$lastIdx] . '</a>';
//			$Parts[] = !$this->_videoTitle ? _t('VimeoGalleryPage.UNTITLED_VIDEO', 'Untitled Video') : $this->_videoTitle;
//		}
//		
//		//Return the imploded array
//        $Breadcrumbs = implode(SiteTree::$breadcrumbs_delimiter, $Parts);
//		
//		return $Breadcrumbs;
//	}
}

<% if Breadcrumbs %>
<p>$Breadcrumbs</p>
<% end_if %>
<% control Video %>
<div class="typeography vimeogallery">
	<h2>$Title</h2>
	<div class="video-wrapper">
		<iframe src="http://player.vimeo.com/video/{$ID}" width="{$Top.PopupWidth}" height="{$Top.PopupHeight}" frameborder="0" style="margin-bottom:8px;"></iframe>
		<iframe src="http://www.facebook.com/plugins/like.php?app_id=221594161204508&amp;href={$Url}&amp;send=false&amp;layout=button_count&amp;width=450&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=lucida+grande&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:21px;" allowTransparency="true"></iframe>
	</div>
	<div class="video-stats">
		<ul>
			<li>Number Plays: <span class="vimeo-stat">$NumberPlays</span></li>
			<li>Number Likes: <span class="vimeo-stat">$NumberLikes</span></li>
			<li>Number Comments: <span class="vimeo-stat">$NumberComments</span></li>
		</ul>
	</div>
	<div class="video-owner">
		Uploaded by <a href="{$UserUrl}" target="_blank">$UserRealName</a> on $UploadDate.Format(n/j/Y).
	</div>
	<% if Description %>
		<div class="video-description">
			$Description
		</div>
	<% end_if %>
	<a href="http://www.vimeo.com" target="_blank" id="vimeo-powered-by">&nbsp;</a>
</div>
<% end_control %>
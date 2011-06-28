<% if Breadcrumbs %>
<p>$Breadcrumbs</p>
<% end_if %>
<div class="typeography vimeogallery">
<h2>$Title</h2>
$Content
	<p>&nbsp;</p>
	<ul class="vimeo-video-list">
		<% if VimeoVideos %>
			<% control VimeoVideos %>
				<li class="vimeo-video-container">
					<div class="vimeo-video-thumb">
						<% if Top.ShowVideoInPopup %>
							<a href="{$Url}" <% if Top.ShowVideoInPopup %>rel="prettyPhoto"<% else %>target="_blank"<% end_if %>><img src="{$ThumbSmall}" width="100" alt="{$Title}" /></a>
						<% else %>
							<a href="{$Top.Link}view/{$ID}"><img src="{$ThumbSmall}" width="100" alt="{$Title}" /></a>
						<% end_if %>
					</div>
					<div class="vimeo-video-details">
						<h3>
						<% if Top.ShowVideoInPopup %>
							<a href="$Url" <% if Top.ShowVideoInPopup %>rel="prettyPhoto"<% else %>target="_blank"<% end_if %>>$Title</a>
						<% else %>
							<a href="{$Top.Link}view/{$ID}">$Title</a>
						<% end_if %>
						 <span class="vimeo-duration">($Duration seconds)</span></h3>
						<p class="vimeo-upload-date">Uploaded by <a href="{$UserUrl}" target="_blank">$UserDisplayName</a> on $UploadDate.Format(n/j/Y g:i a)</p>
						<% if Tags %>
							<p class="vimeo-tags">Tags: $Tags</p>
						<% end_if %>
						<% if Description %>
							$Description.FirstParagraph
						<% end_if %>
						<p class="vimeo-stats">
							Plays: $NumberPlays<br />
							Likes: $NumberLikes <br />
							Comments: $NumberComments
						</p>
					</div>
					<br style="clear:left;height:1px;" />
				</li>
			<% end_control %>
		<% else %>
			<li>
			<% if isUserRequest %>
				This user has no videos listed.
			<% else_if isGroupRequest %>
				This group has no videos listed.
			<% else %>
				This album has no videos listed.
			<% end_if %>
			</li>
		<% end_if %>
	</ul>
	<% if VimeoVideos.MoreThanOnePage %>
		<p class="pageNumbers">
		<% if VimeoVideos.PrevLink %>
		<a href="$VimeoVideos.PrevLink">&laquo; Prev</a>
		<% end_if %>
				
		<% control VimeoVideos.Pages %>
		<% if CurrentBool %>
		<strong>$PageNum</strong>
		<% else %>
		<a href="$Link" title="Go to page $PageNum">$PageNum</a>
		<% end_if %>
		<% end_control %>
				
		<% if VimeoVideos.NextLink %>
		<a href="$VimeoVideos.NextLink">Next &raquo;</a>
		<% end_if %>
		</p>
	<% end_if %>
	<a href="http://www.vimeo.com" target="_blank" id="vimeo-powered-by">&nbsp;</a>
<div class="clear">&nbsp;</div>
</div>
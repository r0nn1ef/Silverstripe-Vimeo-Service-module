<div class="content-container vimeogallery">
<article>
	<h1>$Title</h1>
	<div class="content">$Content</div>
</article>
	<ul class="vimeo-video-list">
		<% if VimeoVideos %>
			<% loop VimeoVideos %>
				<li class="vimeo-video-container">
					<div class="vimeo-video-thumb">
						<a href="{$Top.Link}view/{$ID}"><img src="{$ThumbSmall}" width="100" alt="{$Title}" /></a>
					</div>
					<div class="vimeo-video-details">
						<h3><a href="{$Top.Link}view/{$ID}">$Title</a></h3>
						<p class="vimeo-upload-date">Uploaded by <a href="{$UserUrl}" target="_blank">$UserDisplayName</a> on $UploadDate.Format(n/j/Y g:i a)</p>
						<% if Tags %>
							<p class="vimeo-tags">Tags: $Tags</p>
						<% end_if %>
						<% if Description %>
							$Description.FirstParagraph
						<% end_if %>
						<% include VideoStats %>
					</div>
					<div class="clearfix"></div>
				</li>
			<% end_loop %>
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
	<a href="http://www.vimeo.com" target="_blank" id="vimeo-powered-by" title="Powered by Vimeo&trade;" rel="no-follow">&nbsp;</a>
<div class="clearfix"></div>
</div>
<% include SideBar %>
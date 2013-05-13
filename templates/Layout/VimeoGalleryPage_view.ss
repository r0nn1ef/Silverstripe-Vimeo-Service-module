<div class="vimeogallery vimeo-video-details">
	<article id="vimeo-{$Video.ID}">
		<h1>$Video.Title</h1>
		<div class="video-wrapper">
			<iframe src="{$VideoURL}" width="{$VideoWidth}" height="{$VideoHeight}" frameborder="0" id="vimeo-video-{$Video.ID}" class="vimeo-video-frame"></iframe>
		</div>
		<% with Video %><% include VideoStats %><% end_with %>
		<div class="video-owner">
			Uploaded by <a href="{$Video.UserUrl}" target="_blank">$Video.UserRealName</a> on {$Video.UploadDate.Format(n/j/Y g:i a)}.
		</div>
		<% if Video.Description %>
			<div class="video-description">
				$Video.Description
			</div>
		<% end_if %>
	</article>
	<a href="http://www.vimeo.com" target="_blank" id="vimeo-powered-by">&nbsp;</a>
</div>
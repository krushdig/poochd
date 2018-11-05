<script id="tmpl-infoBubbleTemplate" type="text/template">

	<# if ( data.featuredImage ) { #>
		<span style="background-image: url({{data.featuredImage.url}})" class="list-cover has-image"></span>
	<# } #>

	<# if ( data.title ) { #>
		<h3>
			<a href="{{data.permalink}}" target="{{data.mapMarker.target}}">
				{{{data.title}}}
			</a>
		</h3>
	<# } #>

	<# if ( data.cardDisplay.rating && data.reviews ) { #>
		<span class="rating">{{data.reviews.i18n.totalStars}}</span>
	<# } #>

	<# if ( data.location.address ) { #>
		<span class="address">{{{data.location.address}}}</span>
	<# } #>

</script>

<script id="tmpl-pinTemplate" type="text/template">

	<div id="listing-{{data.id}}-map-marker" class="map-marker marker-color-{{{ data.mapMarker.term }}} type-{{{ data.mapMarker.term }}} <# if ( data.status.featured ) { #>featured<# } #>">
		<i class="{{{ data.mapMarker.icon }}}"></i>
	</div>

</script>

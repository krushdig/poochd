function YrmClassic() {

	this.id;
}

YrmClassic.prototype = new YrmMore();
YrmClassic.constructor = YrmClassic;

YrmClassic.prototype.init = function () {

	var id = this.id;
	if(typeof readMoreArgs[id] == 'undefined') {
		console.log('Invalid Data');
		return;
	}

	var data = readMoreArgs[id];

	this.setData('readMoreData', data);
	this.setData('id', id);
	this.setStyles();
	this.livePreview();

	var duration = parseInt(data['animation-duration']);

	jQuery('.yrm-toggle-expand-'+id).each(function () {
		var position = -1;
		var initialScroll = -1;

		jQuery(this).unbind('click').bind('click', function () {
            var easings = data['yrm-animate-easings'];
            var toggleContentId = jQuery(this).attr('data-rel');
			position = jQuery('#'+toggleContentId).offset().top;
            var currentStatus = JSON.parse(jQuery("#"+toggleContentId).attr('data-show-status'));

            /*if currentStatus == true must be close read more*/
            if(currentStatus) {
            	var moreName = jQuery(this).data('more');
                jQuery("#" + toggleContentId).slideToggle(duration, easings, function () {
                });
	            var currentScroll = document.documentElement.scrollTop;
	            var scrollDifference = currentScroll - initialScroll;
	            if (position != -1 && data['vertical'] != 'top' && scrollDifference && data['scroll-to-initial-position']) {
		            jQuery("html ,body").animate({scrollTop: document.documentElement.scrollTop-scrollDifference}, duration, easings);
	            }
                jQuery(this).find(".yrm-button-text").text(moreName);
                jQuery(window).trigger('YrmClose', {'id': id});
			}
			else {
	            initialScroll = document.documentElement.scrollTop;
            	var lessName = jQuery(this).data('less');
                jQuery("#"+toggleContentId).slideToggle(duration, easings, function () {
                });
                jQuery(this).find(".yrm-button-text").text(lessName);
                jQuery(window).trigger('YrmOpen', {'id': id});
			}
            jQuery("#"+toggleContentId).attr('data-show-status', !currentStatus);
        })
	});
};
(function ($) {

    $.loadingpage = $.loadingpage || {};
    $.loadingpage.graphics = $.loadingpage.graphics || {};
    
    $.loadingpage.graphics['logo'] = {
        created: false,
        attr   : {percentage:0},
		create : function(options){
            options.backgroundColor = options.backgroundColor || "#000000";
            options.foregroundColor = options.foregroundColor || "#FFFFFF";
            
            this.attr['foreground'] = options.foregroundColor;
            var css_o = {
                width: "100%",
                height: "100%",
                backgroundColor: options.backgroundColor,
                position: "fixed",
                zIndex: 666999,
                top: 0,
                left: 0
            };
            
            if( options[ 'backgroundImage' ] ){
                css_o['backgroundImage']  = 'url('+options[ 'backgroundImage' ]+')';
                css_o['background-repeat'] = options[ 'backgroundRepeat' ];
                css_o['background-position'] = 'center center';
                
                if( 
                    css_o['background-repeat'].toLowerCase() == 'no-repeat' && 
                    typeof options['fullscreen'] !== 'undefined' &&
                    options['fullscreen']*1 == 1 
                )
                {
                    css_o[ "background-attachment" ] = "fixed";
                    css_o[ "-webkit-background-size" ] = "contain";
                    css_o[ "-moz-background-size" ] = "contain";
                    css_o[ "-o-background-size" ] = "contain";
                    css_o[ "background-size" ] = "contain";
                }
            }
            
            this.attr['overlay'] = $("<div class='lp-screen'></div>").css(css_o).appendTo("body");
            
            if (options.text) {
                this.attr['text'] = $("<div class='lp-screen-text'></div>").text("0%").css({
                    lineHeight: "40px",
                    height: "40px",
                    width: "100px",
                    position: "absolute",
                    fontSize: "30px",
                    top: this.attr['overlay'].height()/2,
                    left: this.attr['overlay'].width()/2-50,
                    textAlign: "center",
                    color: options.foregroundColor
                }).appendTo(this.attr['overlay']);
            }
            
			if( 
				typeof options[ 'lp_ls' ]  != 'undefined'
			)
			{
				if(
					typeof options[ 'lp_ls' ][ 'logo' ]  != 'undefined'  && 
					typeof options[ 'lp_ls' ][ 'logo' ][ 'image' ]  != 'undefined'  && 
					!/^\s*$/.test( options[ 'lp_ls' ][ 'logo' ][ 'image' ]  )
				)
				{
					var me 	= this,
						img = $( '<img src="'+$.trim( options[ 'lp_ls' ][ 'logo' ][ 'image' ] )+'" style="float:left;" />' ).on( 'load', 
							function(){
								var e = $( this );
								e.appendTo( me.attr[ 'overlay' ] );
								var w = e.width(),
									h = e.height()+1,
									p = e.wrap( '<div style="display:inline-block;position:absolute;"></div>' ).parent();
								
								p.css(
									{
										top: ( me.attr['overlay'].height() - h )/2,
										left:( me.attr['overlay'].width() - w )/2
									}
								);
								
								p.append( '<div id="lp_ls_cover" style="width:'+(100-me.attr['percentage'])+'%; right:0; position:absolute; z-index: 9999; height:'+h+'px;background-color:'+options.backgroundColor+';opacity:.8;"  class="lp-screen-graphic"></div>' );
								
								me.attr[ 'text' ].css('top', (( me.attr['overlay'].height() + h )/2+20)+'px' );
                    
							} 
						);
				}	
			}	
            this.set(0);
            this.created = true;
        },
        
        set : function(percentage){
			this.attr['percentage'] = percentage;
            if (this.attr['text']) {
                this.attr['text'].text(Math.ceil(percentage) + "%");
            }
			$( '#lp_ls_cover' ).css( { 'width': (100-percentage)+'%', 'right':0 } );
        },
        
        complete : function(callback){
            if (this.attr['text']) {
                this.attr['text'].text("100%");
            }
            callback();
            var me = this;
            this.attr['overlay'].fadeOut(500, function () {
                me.attr['overlay'].remove();
            });
        }
    };
})(jQuery);
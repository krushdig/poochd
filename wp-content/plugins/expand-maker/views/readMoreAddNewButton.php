<?php
$params = $dataObj::params();
$type = @$_GET['type'];
if(!isset($type)) {
	$type = 'button';
}
?>
<?php if(!empty($_GET['saved'])) : ?>
	<div id="default-message" class="updated notice notice-success is-dismissible">
		<p>Read more updated.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
	</div>
<?php endif; ?>
<div class="ycf-bootstrap-wrapper">
<form method="POST" action="<?php echo admin_url();?>admin-post.php?action=save_data">
	<?php
		if(function_exists('wp_nonce_field')) {
			wp_nonce_field('read_more_save');
		}
	?>
<input type="hidden" name="read-more-type" value="<?php echo esc_attr($type); ?>">
<input type="hidden" name="read-more-id" value="<?php echo esc_attr($id); ?>">
<div class="expm-wrapper">
	<div class="titles-wrapper">
		<h2 class="expander-page-title">Change settings</h2>
		<div class="button-wrapper">
			<p class="submit">
				<?php if(YRM_PKG == YRM_FREE_PKG): ?>
					<input type="button" class="yrm-upgrade-button-red" value="Upgrade to PRO version" onclick="window.open('<?php echo YRM_PRO_URL; ?>');">
				<?php endif;?>
				<input type="submit" class="button-primary" value="<?php echo 'Save Changes'; ?>">
			</p>
		</div>
	</div>
	<div class="clear"></div>
	<div class="row">
		<div class="col-xs-12">
			<input type="text" name="expm-title" class="form-control input-md" placeholder="Read more title" value="<?php echo $readMoreTitle; ?>">
		</div>
	</div>
	<div class="options-wrapper">
		<div class="panel panel-default">
			<div class="panel-heading">General options</div>
			<div class="panel-body">
				<?php if(!ReadMore::RemoveOption('button-width')): ?>
				<div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="expm-btn-width"><?php _e('Button width', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" id="expm-btn-width" class="form-control input-md expm-options-margin expm-btn-width" name="button-width" value="<?php echo esc_attr($buttonWidth);?>"><br>
					</div>
					<div class="col-md-2 expm-option-info">(in pixels)</div>
				</div>
				<?php endif; ?>
				<?php if(!ReadMore::RemoveOption('button-height')): ?>
				<div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="button-height"><?php _e('Button height', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" id="button-height" class="form-control input-md expm-options-margin expm-btn-height" name="button-height" value="<?php echo esc_attr($buttonHeight);?>"><br>
					</div>
					<div class="col-md-2 expm-option-info">(in pixels)</div>
				</div>
				<?php endif; ?>
				<div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="font-size"><?php _e('Font size', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type='text' id="font-size" class="form-control input-md expm-option-font-size" name="font-size" value="<?php echo esc_attr($fontSize)?>"><br>
					</div>
					<div class="col-md-2 expm-option-info">(in pixels)</div>
				</div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Font weight', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<?php echo $functions::createSelectBox($params['btnFontWeight'],'yrm-btn-font-weight', esc_attr($yrmBtnFontWeight));?><br>
					</div>
				</div>
				<?php if(!ReadMore::RemoveOption('animation-duration')): ?>
				<div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Animation speed', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
					<input type="text" class="form-control input-md  expm-options-margin" name="animation-duration" value="<?php echo esc_attr($animationDuration)?>"><br>
					</div>
					<div class="col-md-2 expm-option-info"></div>
				</div>
                <div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Animation behavior', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
                        <?php echo $functions::yrmSelectBox($params['easings'], esc_attr($yrmEasings), array('name' => 'yrm-animate-easings', 'class' => 'yrm-js-select2 yrm-animate-easings'));?><br>
					</div>
				</div>
				<?php endif; ?>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button hover effect', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<?php echo $functions::createSelectBox($params['hoverEffect'],"yrm-btn-hover-animate", esc_attr($yrmBtnHoverAnimate));?><br>
					</div>
					<div class="col-md-2 expm-option-info"></div>
				</div>
                <div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('More text', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" data-type="more" class="form-control yrm-button-title" name="more-button-title" value="<?php echo esc_attr($savedObj->getOptionValue('more-button-title')); ?>">
					</div>
				</div>
                <div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Less text', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" data-type="less" class="form-control yrm-button-title" name="less-button-title" value="<?php echo esc_attr($savedObj->getOptionValue('less-button-title')); ?>">
					</div>
				</div>
				<?php if(!ReadMore::RemoveOption('scroll-to-initial-position')): ?>
                <div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="scrollToInitialPosition"><?php _e('After "Show Less" scroll to initial position', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="checkbox" name="scroll-to-initial-position" id="scrollToInitialPosition" <?php echo $savedObj->getOptionValue('scroll-to-initial-position', true); ?>>
					</div>
					<div class="col-md-2 expm-option-info"></div>
				</div>
                <?php endif; ?>
			</div>
		</div>

        <!-- Advanced option -->

		<div class="panel panel-default yrm-pro-options-wrapper">
			<div class="panel-heading"><?php _e('Advanced options', YRM_LANG);?></div>
			<div class="panel-body">
				<?php if(!ReadMore::RemoveOption('btn-background-color')): ?>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button Background Color', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="input-md background-color" name="btn-background-color" value="<?php echo $btnBackgroundColor ?>"><br>
					</div>
				</div>
				<?php endif; ?>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button Text Color', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="input-md btn-text-color" name="btn-text-color" value="<?php echo esc_attr($btnTextColor)?>"><br>
					</div>
				</div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button Font Family', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<?php echo $functions::createSelectBox($params['googleFonts'],"expander-font-family", esc_attr($expanderFontFamily));?><br>
					</div>
				</div>
                <div class="yrm-accordion-content yrm-hide-content">
                    <div class="row">
                        <div class="col-xs-5">
                            <label class="control-label" for="custom-font-family"><?php _e('button custom font family', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-4">
                            <input type="text" id="custom-font-family" class="form-control input-md custom-font-family" name="btn-custom-font-family" value="<?php echo esc_attr($dataObj->getOptionValue('btn-custom-font-family'))?>"><br>
                        </div>
                    </div>
                </div>
				<?php if(!ReadMore::RemoveOption('btn-border-radius')): ?>
				<div class="row">
					<div class="col-xs-5">
						<label class="control-label" for="btn-border-radius"><?php _e('Button Border Radius', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" id="btn-border-radius" class="form-control input-md btn-border-radius" name="btn-border-radius" value="<?php echo esc_attr($btnBorderRadius)?>"><br>
					</div>
				</div>
                <?php endif; ?>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button Horizontal alignment', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<?php echo $functions::createSelectBox($params['horizontalAlign'],"horizontal", esc_attr($horizontal));?><br>
					</div>
				</div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Button Vertical alignment', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<?php echo $functions::createSelectBox($params['vertical'],"vertical", esc_attr($vertical));?><br>
					</div>
				</div>
				<?php if(!ReadMore::RemoveOption('button-border')): ?>
                    <div class="row row-static-margin-bottom">
                        <div class="col-xs-5">
                            <label class="control-label" for="button-border"><?php _e('Button Border', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-4">
                            <input type="checkbox" id="button-border" name="button-border" class="yrm-accordion-checkbox" <?php echo $savedObj->getOptionValue('button-border', true);?>>
                        </div>
                    </div>
                    <div class="yrm-accordion-content yrm-hide-content">
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-border-width"><?php _e('border width', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="text" id="button-border-width" name="button-border-width" class="yrm-button-border-width form-control" value="<?php echo esc_attr($savedObj->getOptionValue('button-border-width'));?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-border-color"><?php _e('border color', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="text" class="button-border-color" name="button-border-color" value="<?php echo esc_attr($savedObj->getOptionValue('button-border-color'))?>"><br>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
				<?php if(!ReadMore::RemoveOption('button-box-shadow')): ?>
                    <div class="row row-static-margin-bottom">
                        <div class="col-xs-5">
                            <label class="control-label" for="button-box-shadow"><?php _e('Button Box Shadow', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-4">
                            <input type="checkbox" id="button-box-shadow" name="button-box-shadow" class="yrm-accordion-checkbox" <?php echo $savedObj->getOptionValue('button-box-shadow', true);?>>
                        </div>
                    </div>
                    <div class="yrm-accordion-content yrm-hide-content">
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-box-shadow-horizontal"><?php _e('Horizontal Length', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="input-md form-control" id="button-box-shadow-horizontal" placeholder="example 5 or -5" name="button-box-shadow-horizontal-length" value="<?php echo esc_attr($savedObj->getOptionValue('button-box-shadow-horizontal-length'))?>"><br>
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label"><?php _e('px', YRM_LANG);?></label>
                            </div>
                        </div>
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-box-shadow-vertical"><?php _e('Vertical Length', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="input-md form-control" placeholder="example 5 or -5" id="button-box-shadow-vertical" name="button-box-shadow-vertical-length" value="<?php echo esc_attr($savedObj->getOptionValue('button-box-shadow-vertical-length'))?>"><br>
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label"><?php _e('px', YRM_LANG);?></label>
                            </div>
                        </div>
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-box-blur-radius"><?php _e('Blur Radius', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="input-md form-control" placeholder="example 5 or -5" id="button-box-blur-radius" name="button-box-blur-radius" value="<?php echo esc_attr($savedObj->getOptionValue('button-box-blur-radius'))?>"><br>
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label"><?php _e('px', YRM_LANG);?></label>
                            </div>
                        </div>
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-box-spread-radius"><?php _e('Spread Radius', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="number" class="input-md form-control" placeholder="example 5 or -5" id="button-box-spread-radius" name="button-box-spread-radius" value="<?php echo esc_attr($savedObj->getOptionValue('button-box-spread-radius'))?>"><br>
                            </div>
                            <div class="col-xs-1">
                                <label class="control-label"><?php _e('px', YRM_LANG);?></label>
                            </div>
                        </div>
                        <div class="row row-static-margin-bottom">
                            <div class="col-xs-5">
                                <label class="control-label" for="button-box-shadow-color"><?php _e('Shadow Color', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-4">
                                <input type="text" class="input-md" id="button-box-shadow-color" name="button-box-shadow-color" value="<?php echo esc_attr($savedObj->getOptionValue('button-box-shadow-color'))?>"><br>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Hidden content background color', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="input-md hidden-content-bg-color" name="hidden-content-bg-color" value="<?php echo esc_attr($hiddenContentBgColor)?>"><br>
					</div>
				</div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="textinput"><?php _e('Hidden content text color', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="text" class="input-md hidden-content-text-color" name="hidden-content-text-color" value="<?php echo esc_attr($hiddenContentTextColor)?>"><br>
					</div>
				</div>
                <div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label" for="hidden-content-padding"><?php _e('Hidden content padding', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-3">
						<input type="number" class="input-md form-control js-hidden-content-padding" id="hidden-content-padding" name="hidden-content-padding" value="<?php echo esc_attr($hiddenContentPadding)?>"><br>
					</div>
                    <div class="col-xs-1">
                        <label class="control-label"><?php _e('Px', YRM_LANG);?></label>
                    </div>
				</div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label-checkbox" for="show-only-devices"><?php _e('Show on selected devices', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="checkbox" class="yrm-accordion-checkbox" id="show-only-devices" name="show-only-devices" <?php echo $showOnlyDevices;?>>
					</div>
				</div>
                <div class="yrm-accordion-content yrm-hide-content">
                    <div class="row row-static-margin-bottom">
                        <div class="col-xs-5">
                            <label class="control-label-checkbox" for="hover-effect"><?php _e('Select device(s)', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-4">
	                        <?php echo $functions::yrmSelectBox($params['devices'], $selectedDevices, array('name'=>"yrm-selected-devices[]", 'multiple'=>'multiple', 'class'=>'yrm-js-select2'));?>
                        </div>
                    </div>
                    <div class="row row-static-margin-bottom">
                        <div class="col-xs-5">
                            <label class="control-label-checkbox" for="hide-content"><?php _e('hide content if not matched devices', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-4">
                            <input type="checkbox" id="hide-content" name="hide-content" class="" <?php echo $savedObj->getOptionValue('hide-content', true);?>>
                        </div>
                    </div>
                </div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label-checkbox" for="hover-effect"><?php _e('Button Hover effect', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="checkbox" id="hover-effect" name="hover-effect" class="yrm-accordion-checkbox" <?php echo $hoverEffect;?>>
					</div>
				</div>
                <div class="yrm-accordion-content yrm-hide-content">
                    <div class="row">
                        <div class="col-xs-5">
                            <label class="control-label" for="btn-hover-text-color"><?php _e('button color', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-5">
                            <input type="text" id="btn-hover-text-color" class="input-md btn-hover-color" name="btn-hover-text-color" value="<?php echo esc_attr($btnHoverTextColor)?>" >
                        </div>
                    </div>
	                <?php if(!ReadMore::RemoveOption('btn-hover-bg-color')): ?>
                        <div class="row">
                            <div class="col-xs-5">
                                <label class="control-label" for="textinput"><?php _e('button bg color', YRM_LANG);?>:</label>
                            </div>
                            <div class="col-xs-5">
                                <input type="text" class="input-md btn-hover-color" name="btn-hover-bg-color" value="<?php echo esc_attr($btnHoverBgColor)?>" >
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
				<div class="row row-static-margin-bottom">
					<div class="col-xs-5">
						<label class="control-label-checkbox" for="button-for-post"><?php _e('Add button for posts', YRM_LANG);?>:</label>
					</div>
					<div class="col-xs-4">
						<input type="checkbox" id="button-for-post" name="button-for-post" class="yrm-accordion-checkbox" <?php echo @$buttonForPost;?>>
					</div>
				</div>
                <div class="yrm-accordion-content yrm-hide-content">
                    <div class="row">
                        <div class="col-xs-5">
                            <label class="control-label" for="textinput"><?php _e('Selected post', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-5">
                            <?php echo $functions::yrmSelectBox($params['selectedPost'],$yrmSelectedPost, array('name'=>"yrm-selected-post[]", 'multiple'=>'multiple','size'=>10));?><br>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 5px">
                        <div class="col-xs-5">
                            <label class="control-label" for="hide-after-word-count" for="textinput"><?php _e('Hide after word count', YRM_LANG);?>:</label>
                        </div>
                        <div class="col-xs-5">
                            <input type="text" id="hide-after-word-count" class="form-control input-md btn-border-radius" name="hide-after-word-count" value="<?php echo esc_attr($hideAfterWordCount)?>"><br>
                        </div>
                    </div>
                 </div>
			</div>
			<?php if(YRM_PKG == YRM_FREE_PKG) :?>
				<div class="yrm-pro-options">
					<p class="yrm-pro-options-title">PRO Features</p>
				</div>
			<?php endif;?>
		</div>
	</div>
	<div class="right-side">
		<div class="panel panel-default">
			<div class="panel-heading">Live preview</div>
			<div class="panel-body">
				<?php require_once(YRM_VIEWS."livePreview/buttonPreview.php");?>
			</div>
		</div>
		<?php $shortCode = '[expander_maker id="'.$id.'" more="Read more" less="Read less"]Read more hidden text[/expander_maker]'; ?>
		<?php if($id != 0): ?>
			<input type="text" id="expm-shortcode-info-div" class="widefat" readonly="readonly" value='<?php echo $shortCode; ?>'>
		<?php endif; ?>
		<?php if($id == 0): ?>
			<div class="no-shortcode">
				<span>Please do save read more for getting shortcode.</span>
			</div>
		<?php endif; ?>
		<?php $typeObj->includeOptionsBlock($dataObj); ?>
	</div>
</div>
</form>
	<?php echo ReadMoreFunctions::getFooterReviewBlock();?>
</div>

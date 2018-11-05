<?php
$params = ReadMoreData::params();
$userRoles = $params['userRoles'];
$dontDeleteData = (get_option('yrm-delete-data') ? 'checked': '');
?>
<div class="ycf-bootstrap-wrapper yrm-settings">
<div class="row">
	<div class="col-md-6">
        <form action="<?php echo admin_url().'admin-post.php?action=yrmSaveSettings'?>" method="post">
        <?php wp_nonce_field('YRM_ADMIN_POST_NONCE', YRM_ADMIN_POST_NONCE);?>
		<div class="panel panel-default">
			<div class="panel-heading"><?php _e('Settings', YRM_LANG);?></div>
			<div class="panel-body">
                <div class="row form-group">
                    <div class="col-md-4">
                        <label class="ycd-label-of-switch" for="yrm-dont-delete-data"><? _e('Remove Settings', YRM_LANG)?></label>
                    </div>
                    <div class="col-md-2">
                        <div class="yrm-switch-wrapper">
                            <label class="yrm-switch">
                                <input type="checkbox" id="yrm-dont-delete-data" name="yrm-dont-delete-data" class="yrm-accordion-checkbox" <?php echo $dontDeleteData ?> >
                                <span class="yrm-slider yrm-round"></span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="ycd-label-of-switch">
                            <? _e('This option will remove all settings and styles when <b>Delete plugin</b>', YRM_LANG)?>
                        </label>
                    </div>
                </div>
				<div class="row form-group">
					<div class="col-md-4">
                        <label>
	                        <?php _e('User role who can use plugin', YRM_LANG);?>
                        </label>
					</div>
					<div class="col-md-8">
                        <?php echo $functions::yrmSelectBox($userRoles, get_option('yrm-user-roles'), array('name' => 'yrm-user-roles[]', 'multiple' => 'multiple', 'class' => 'yrm-js-select2'));?>
                    </div>
				</div>
                <div class="row">
                    <div class="col-md-12">
                        <input type="submit" class="btn btn-primary" value="<? _e('Save changes', YRM_LANG)?>">
                    </div>
                </div>
			</div>
		</div>
        </form>
	</div>
</div>
</div>
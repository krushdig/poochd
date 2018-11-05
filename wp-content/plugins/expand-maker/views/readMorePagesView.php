<?php
$results = ReadMoreData::getAllData();
$ajaxNonce = wp_create_nonce("YrmNonce");
?>
<div class="ycf-bootstrap-wrapper">
<div class="wrap">
	<h2 class="add-new-buttons"><?php _e('Read More', YRM_LANG); ?><a href="<?php echo admin_url();?>admin.php?page=addNew" class="add-new-h2"><?php echo _e('Add New', YRM_LANG); ?></a></h2>
</div>
<div class="expm-wrapper">
    <div class="yrm-export-import-wrapper">
        <img src="<?php echo YRM_IMG_URL.'ajax.gif'; ?>" class="yrm-spinner yrm-hide-content">
        
        <input type="button" class="yrm-exprot-button button" value="<?php echo  _e('Export', YRM_LANG)?>">
        <input type="button" class="yrm-import-button button" data-ajaxNonce="<?php echo $ajaxNonce; ?>" value="<?php echo  _e('Import', YRM_LANG)?>">
    </div>
	<?php if(YRM_PKG == YRM_FREE_PKG): ?>
		<div class="main-view-upgrade main-upgreade-wrapper">
            <a href="<?php echo YRM_PRO_URL; ?>" target="_blank">
                <button class="yrm-upgrade-button-red">
                    <b class="h2">Upgrade</b><br><span class="h5">to PRO version</span>
                </button>
            </a>
		</div>
	<?php endif;?>
	<table class="table table-bordered expm-table">
		<tr>
			<td>Id</td>
			<td><? _e('Title', YRM_LANG)?></td>
			<td><? _e('Enabled', YRM_LANG)?></td>
			<td><? _e('Type', YRM_LANG)?></td>
			<td><? _e('Options', YRM_LANG)?></td>
		</tr>

		<?php if(empty($results)) { ?>
			<tr>
				<td colspan="4"><? _e('No Data', YRM_LANG)?></td>
			</tr>
		<?php }
		else {
			foreach ($results as $result) { ?>
                <?php
                    $id = (int)$result['id'];
				    $title = esc_attr($result['expm-title']);
				    $type = esc_attr($result['type']);

                ?>
				<tr>
					<td><?php echo $id; ?></td>
                    <td><a href="<?php echo admin_url()."admin.php?page=button&type=".$type."&readMoreId=".$id.""?>"><?php echo $title; ?></a></td>
					<td>
                        <?php $isChecked = (ReadMore::isActiveReadMore($id) ? 'checked': ''); ?>
                        <div class="yrm-switch-wrapper">
                            <label class="yrm-switch">
                                <input type="checkbox" name="yrm-status-switch" data-id="<?= $id; ?>" class="yrm-accordion-checkbox yrm-status-switch" <?php echo $isChecked;?>>
                                <span class="yrm-slider yrm-round"></span>
                            </label>
                        </div>
                    </td>
					<td><?php echo $type; ?></td>
					<td>
						<a href="<?php echo admin_url()."admin.php?page=button&type=".$type."&readMoreId=".$id.""?>"><?php _e('Edit', YRM_LANG); ?></a>
						<a class="yrm-delete-link" data-id="<?php echo $id;?>" href="<?php echo admin_url()."admin-post.php?action=delete_readmore&readMoreId=".$id.""?>"><?php _e('Delete', YRM_LANG); ?></a>
                        <a class="yrm-clone-link" href="<?php echo admin_url();?>admin-post.php?action=read_more_clone&id=<?php echo $id; ?>" ><?php _e('Clone', YRM_LANG); ?></a>
					</td>
				</tr>
		<?php } ?>

		<?php } ?>
		<tr>
			<td>Id</td>
			<td><? _e('Title', YRM_LANG)?></td>
			<td><? _e('Enabled', YRM_LANG)?></td>
			<td><? _e('Type', YRM_LANG)?></td>
			<td><? _e('Options', YRM_LANG)?></td>
		</tr>
	</table>
</div>
</div>
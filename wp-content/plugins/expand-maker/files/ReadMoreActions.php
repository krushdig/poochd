<?php
CLass ReadMoreActions {

	public function __construct() {

		$ajaxObj = new RadMoreAjax();
		$ajaxObj->init();
		add_action('admin_enqueue_scripts', array(new ReadMoreStyles(),'registerStyles'));
		add_action('wp_head', array($this, 'readMoreWpHead'));
		if(YRM_PKG > YRM_FREE_PKG) {
			add_action('the_content', array($this, 'postContentFiler'));
		}
		add_action('yrm-easings', array($this, 'easingsFilter'));
		add_action('yrm-save-easings', array($this, 'saveEasings'));
	}

	public function saveEasings($savedData) {

        if (YRM_PKG == YRM_FREE_PKG && $savedData != 'linear' && $savedData != 'swing') {
            $savedData = 'swing';
        }

        return $savedData;
    }
	public function easingsFilter($easings) {

        if (empty($easings)) {
            return $easings;
        }

        foreach ($easings as $key => $value) {

            if(YRM_PKG == YRM_FREE_PKG && $key != 'linear' && $key != 'swing') {
                $easings[$key] = $value.' (PRO) ';
            }
        }

        return $easings;
    }

	public function readMoreWpHead() {

		echo '<script>readMoreArgs = []</script>';
	}

	public function postContentFiler($cotent) {
		global $post;
		$postId = $post->ID;
		global $wpdb;
		$getSavedSql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."expm_maker_pages WHERE post_id = %d", $postId);
		$result = $wpdb->get_row($getSavedSql, ARRAY_A);
		
		if(!empty($result)) {
			return ReadMoreContentManager::doFilterContent($cotent, $post, $result);
		}

		return $cotent;
	}
}
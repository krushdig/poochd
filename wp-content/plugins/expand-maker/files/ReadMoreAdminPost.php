<?php
Class ReadMoreAdminPost {

	public function __construct() {

		$this->actions();
	}

	public function actions() {

		add_action('admin_post_save_data', array($this, 'expmSaveData'));
		add_action('admin_post_delete_readmore', array($this, 'expmDeleteData'));
		add_action('admin_post_read_more_clone', array($this, 'cloneReadMore'));
		add_action('admin_post_yrmSaveSettings', array($this, 'saveSettings'));
	}

	public function saveSettings() {
		$postData = $_POST;
		if(
			!isset($_POST[YRM_ADMIN_POST_NONCE])
			|| !wp_verify_nonce($_POST[YRM_ADMIN_POST_NONCE], 'YRM_ADMIN_POST_NONCE')
		) {
			_e('Sorry, your nonce did not verify.', YRM_LANG);die();
		}
		if (isset($_POST['yrm-dont-delete-data'])) {
			update_option('yrm-delete-data', true);
		}
		else {
			delete_option('yrm-delete-data');
		}

		update_option('yrm-user-roles', @$postData['yrm-user-roles']);

		wp_redirect(admin_url().'admin.php?page=rmmore-settings');
	}

	public function cloneReadMore() {

		$id = (int)$_GET['id'];
		$dataObj = new ReadMoreData();
		$dataObj->setId($id);
		$savedData = $dataObj->getSavedOptions();
		global $wpdb;

		$data = array(
			'type' => $savedData['type'],
			'expm-title' => $savedData['expm-title'].'(clone)',
			'button-width' => $savedData['button-width'],
			'button-height' => $savedData['button-height'],
			'animation-duration' => $savedData['animation-duration'],
			'options' => json_encode($savedData)
		);

		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		$wpdb->insert($wpdb->prefix.'expm_maker', $data, $format);
		wp_redirect(admin_url()."admin.php?page=readMore");
	}

	public function expmSanitizeData($optionName) {

		if(isset($_POST[$optionName])) {
			return sanitize_text_field($_POST[$optionName]);
		}
		return '';
	}

	public function expmDeleteData() {

		global $wpdb;
		$id = $_GET['readMoreId'];
		$wpdb->delete($wpdb->prefix.'expm_maker', array('id'=>$id), array('%d'));
		wp_redirect(admin_url()."admin.php?page=ExpMaker");
	}

	public function addToPosts($posts, $postId, $options) {
		global $wpdb;

		$format = array(
			'%d',
			'%d',
			'%s',
		);
		$wpdb->delete($wpdb->prefix.'expm_maker_pages', array('button_id'=>$postId), array('%d'));
		if(!empty($posts)) {
			foreach ($posts as $post) {
				$wpdb->delete($wpdb->prefix.'expm_maker_pages', array('post_id'=>$post), array('%d'));
				$data = array(
					'post_id' => $post,
					'button_id' => $postId,
					'options' => json_encode($options)
				);

				$wpdb->insert($wpdb->prefix.'expm_maker_pages', $data, $format);
			}
		}
		
	}

	public function expmSaveData() {

		global $wpdb;
		if(isset($_POST)) {
			// Check for CSRF
			check_admin_referer('read_more_save');
		}
		$options = array(
			'font-size'=> $this->expmSanitizeData('font-size'),
			'yrm-btn-hover-animate' => $_POST['yrm-btn-hover-animate']
		);
		$options['scroll-to-initial-position'] = $this->expmSanitizeData('scroll-to-initial-position', true);
		
		if(YRM_PKG > YRM_FREE_PKG) {
			$options['btn-background-color'] = $this->expmSanitizeData('btn-background-color');
			$options['btn-text-color'] = $this->expmSanitizeData('btn-text-color');
			$options['btn-border-radius'] = $this->expmSanitizeData('btn-border-radius');
			$options['horizontal'] = $this->expmSanitizeData('horizontal');
			$options['vertical'] = $this->expmSanitizeData('vertical');
			$options['expander-font-family'] = $this->expmSanitizeData('expander-font-family');
			$options['btn-custom-font-family'] = $this->expmSanitizeData('btn-custom-font-family');
			$options['show-only-devices'] = $this->expmSanitizeData('show-only-devices');
			$options['hide-content'] = $this->expmSanitizeData('hide-content');
			$options['yrm-selected-devices'] = @$_POST['yrm-selected-devices'];
			$options['hover-effect'] = $this->expmSanitizeData('hover-effect');
			$options['button-border'] = $this->expmSanitizeData('button-border');
			$options['button-border-width'] = $this->expmSanitizeData('button-border-width');
			$options['button-border-color'] = $this->expmSanitizeData('button-border-color');
			$options['button-box-shadow'] = $this->expmSanitizeData('button-box-shadow');
			$options['button-box-shadow-horizontal-length'] = $this->expmSanitizeData('button-box-shadow-horizontal-length');
			$options['button-box-shadow-vertical-length'] = $this->expmSanitizeData('button-box-shadow-vertical-length');
			$options['button-box-spread-radius'] = $this->expmSanitizeData('button-box-spread-radius');
			$options['button-box-blur-radius'] = $this->expmSanitizeData('button-box-blur-radius');
			$options['button-box-shadow-color'] = $this->expmSanitizeData('button-box-shadow-color');
			$options['btn-hover-text-color'] = $this->expmSanitizeData('btn-hover-text-color');
			$options['btn-hover-bg-color'] = $this->expmSanitizeData('btn-hover-bg-color');
			$options['hidden-content-bg-color'] = $this->expmSanitizeData('hidden-content-bg-color');
			$options['hidden-content-text-color'] = $this->expmSanitizeData('hidden-content-text-color');
			$options['hidden-content-padding'] = $this->expmSanitizeData('hidden-content-padding');
			$selectedPosts = @$_POST['yrm-selected-post'];
			$options['yrm-selected-post'] = $selectedPosts;
			$options['button-for-post'] = $this->expmSanitizeData('button-for-post');
			$options['hide-after-word-count'] = $this->expmSanitizeData('hide-after-word-count');
			

			$pagesOptions = array(
				'button-for-post' => $options['button-for-post'],
				'hide-after-word-count' => $options['hide-after-word-count']
			);
		}
		
		if(YRM_PKG > YRM_SILVER_PKG) {
			$options['yrm-popup-theme'] = $this->expmSanitizeData('yrm-popup-theme');
			$options['yrm-popup-width'] = $this->expmSanitizeData('yrm-popup-width');
			$options['yrm-popup-height'] = $this->expmSanitizeData('yrm-popup-height');
			$options['yrm-popup-max-width'] = $this->expmSanitizeData('yrm-popup-max-width');
			$options['yrm-popup-max-height'] = $this->expmSanitizeData('yrm-popup-max-height');
			$options['yrm-popup-initial-width'] = $this->expmSanitizeData('yrm-popup-initial-width');
			$options['yrm-popup-initial-height'] = $this->expmSanitizeData('yrm-popup-initial-height');
			$options['yrm-popup-esc-key'] = $this->expmSanitizeData('yrm-popup-esc-key');
			$options['yrm-popup-close-button'] = $this->expmSanitizeData('yrm-popup-close-button');
			$options['yrm-popup-overlay-click'] = $this->expmSanitizeData('yrm-popup-overlay-click');
			$options['yrm-popup-overlay-color'] = $this->expmSanitizeData('yrm-popup-overlay-color');
			$options['yrm-popup-content-color'] = $this->expmSanitizeData('yrm-popup-content-color');
			$options['yrm-popup-content-padding'] = $this->expmSanitizeData('yrm-popup-content-padding');
		}
		$options['more-button-title'] = $this->expmSanitizeData('more-button-title');
		$options['less-button-title'] = $this->expmSanitizeData('less-button-title');
		
		$options['yrm-btn-font-weight'] = $this->expmSanitizeData('yrm-btn-font-weight');
		$options['yrm-animate-easings'] = apply_filters('yrm-save-easings', $this->expmSanitizeData('yrm-animate-easings'));
		$options = json_encode($options);
		$id = $this->expmSanitizeData('read-more-id');
		$title = $this->expmSanitizeData('expm-title');
		$type = $this->expmSanitizeData('read-more-type');
		$width = $this->expmSanitizeData('button-width');
		$height = $this->expmSanitizeData('button-height');
		$duration = $this->expmSanitizeData('animation-duration');
	
		$data = array(
			'type' => $type,
			'expm-title' => $title,
			'button-width' => $width,
			'button-height' => $height,
			'animation-duration' => $duration,
			'options' => $options
		);
	
		$format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);
		if(!$id) {
			$wpdb->insert($wpdb->prefix.'expm_maker', $data, $format);
			$readMoreId = $wpdb->insert_id;
		}
		else {
			$data['id'] = $id;
			$wpdb->update($wpdb->prefix.'expm_maker', $data, array('id'=>$id), $format, array('%d'));
			$readMoreId = $id;
		}
		if(YRM_PKG > YRM_FREE_PKG) {
			$this->addToPosts($selectedPosts,$readMoreId,$pagesOptions);
		}
		wp_redirect(admin_url()."admin.php?page=button&readMoreId=".$readMoreId."&type=".$type."&saved=1");
	}
}

$readMoreAdminPost = new ReadMoreAdminPost();
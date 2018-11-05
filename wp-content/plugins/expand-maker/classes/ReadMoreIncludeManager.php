<?php
class ReadMoreIncludeManager {

	private $data;
	private $id;
	private $toggleContent;
	private $rel;
	private $dataObj;

	public function __call($name, $args) {

		$methodPrefix = substr($name, 0, 3);
		$methodProperty = lcfirst(substr($name,3));

		if ($methodPrefix == 'get') {
			return $this->$methodProperty;
		}
		else if ($methodPrefix == 'set') {
			$this->$methodProperty = $args[0];
		}
	}

	private function randomName($length = 10) {

		return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
	}

	public function render() {

		if (!ReadMore::allowRender($this->getId())) {
			return $this->getToggleContent();;
		}

		$rel = 'yrm-'.$this->randomName(5);
		$this->setRel($rel);

		$data = $this->includeData();
		$data .= $this->includeScripts();
		$loadDataAction = array(
			'isAdmin' => is_admin()
		);
		
		do_action('readMoreLoaded', $loadDataAction);
		return $data;
	}

	private function includeData(){

		$allContent = '';
		$data = $this->getData();
		$buttonData = $this->buttonContent();
		$accordionContent = $this->accordionContent();

		if(isset($data['vertical']) && $data['vertical'] == 'top') {
			$allContent = $buttonData.$accordionContent;
			return $allContent;
		}

		$allContent = $accordionContent.$buttonData;

		return $allContent;
	}

	private function accordionContent() {

		$rel = $this->getRel();
		$id = $this->getId();

		$content = $this->getToggleContent();
		return "<div class=\"yrm-content yrm-content-$id\" id='".$rel."' data-show-status='false'>
			<div id='yrm-cntent-$id' class='yrm-inner-content-wrapper yrm-cntent-$id'>$content</div>
		</div>";
	}

	private function buttonContent() {

		$data = $this->getData();
		
		$id = $this->getId();
		$rel = $this->getRel();
		$more = __('Read More', YRM_LANG);
		$lessName = __('Read Less', YRM_LANG);
		
		if (!empty($data['more-button-title'])) {
			$more = $data['more-button-title'];
		}
		if (!empty($data['attrMoreName'])) {
			$more = $data['attrMoreName'];
		}
		
		if (!empty($data['less-button-title'])) {
			$lessName = $data['less-button-title'];
		}
		if (!empty($data['attrLessName'])) {
			$lessName = $data['attrLessName'];
		}
		$type = $data['type'];

		$button = "<div class='yrm-btn-wrapper yrm-btn-wrapper-".$id."'>
			<span class='yrm-toggle-expand  yrm-toggle-expand-$id' data-rel='".esc_attr($rel)."' data-more='".esc_attr($more)."' data-less='".esc_attr($lessName)."'>
				<span class=\"yrm-button-text-$id yrm-button-text\">$more</span>
			</span>
		</div>";

		if($type == 'inline') {
			$button = "<div class='yrm-btn-wrapper yrm-btn-wrapper-".esc_attr($id)."'>
				<span class='yrm-toggle-expand  yrm-toggle-expand-$id' data-rel='".esc_attr($rel)."' data-more='".esc_attr($more)."' data-less='".esc_attr($lessName)."' style='border: none; width: 100%;'>
					<span class=\"yrm-button-text-$id yrm-button-text\">$more</span>
				</span>
			</div>";
		}
		return $button;
	}

	private function includeScripts() {

		$id = $this->getId();
		$savedData = $this->getData();
		$type = $savedData['type'];

		echo "<script>
			readMoreArgs[$id] = ".json_encode($savedData).";
		</script>";
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-effects-core');
		wp_register_script('readMoreJs', YRM_JAVASCRIPT.'yrmMore.js', array(), EXPM_VERSION);
		wp_register_script('yrmMorePro', YRM_JAVASCRIPT.'yrmMorePro.js', array(), EXPM_VERSION);
		wp_enqueue_script('readMoreJs');
		if(YRM_PKG > 1) {
			wp_register_script('yrmGoogleFonts', YRM_JAVASCRIPT.'yrmGoogleFonts.js');
			wp_enqueue_script('yrmGoogleFonts');
			wp_enqueue_script('yrmMorePro');
		}
		wp_register_style('readMoreStyles', YRM_CSS_URL."readMoreStyles.css", array(), EXPM_VERSION);
		wp_enqueue_style('readMoreStyles');
		wp_register_style('yrmanimate', YRM_CSS_URL."animate.css");
		wp_enqueue_style('yrmanimate');

		if($type == 'popup') {
			wp_register_script('YrmPopup', YRM_JAVASCRIPT.'YrmPopup.js', array('readMoreJs'), EXPM_VERSION);
			wp_enqueue_script('YrmPopup');
			wp_register_script('jquery.colorbox', YRM_JAVASCRIPT.'jquery.colorbox.js', array('YrmPopup'), EXPM_VERSION);
			wp_enqueue_script('jquery.colorbox');
			wp_register_style('colorbox.css', YRM_CSS_URL."colorbox/colorbox.css");
			wp_enqueue_style('colorbox.css');

			echo "<script>
				yrmAddEvent(window,'load',function(){	
					var obj = new YrmPopup();
					obj.id = $id;
					obj.init();
				});
			</script>";
		}
		if($type == 'button') {
			wp_register_script('YrmClassic', YRM_JAVASCRIPT.'YrmClassic.js', array('readMoreJs', 'jquery-effects-core'), EXPM_VERSION);
			wp_enqueue_script('YrmClassic');
			echo "<script>
				yrmAddEvent(window,'load',function(){	
					var obj = new YrmClassic();
					obj.id = $id;
					obj.init();
				});
			</script>";
		}
		if($type == 'inline') {
			wp_register_script('YrmInline', YRM_JAVASCRIPT.'YrmInline.js', array('readMoreJs'), EXPM_VERSION);
			wp_enqueue_script('YrmInline');
			echo "<script>
				yrmAddEvent(window,'load',function(){	
					var obj = new YrmInline();
					obj.id = $id;
					obj.init();
				});
			</script>";
		}
		$this->includeCustomStyle();
	}

	public function includeCustomStyle() {

		$id = $this->getId();
		$savedData = $this->getData();
		$type = $savedData['type'];
		$dataObj = $this->getDataObj();
		$hiddenContentPadding = (int)$dataObj->getOptionValue('hidden-content-padding').'px';

		$important = '!important';

		if(is_admin()) {
			$important = '';
		}

		echo "<style>
			.yrm-cntent-$id {
				padding: $hiddenContentPadding;
			}
		</style>";
		$hoverTextColor = $dataObj->getOptionValue('btn-hover-text-color');
		$fontSize = $dataObj->getOptionvalue('font-size');
		$fontWeight = $dataObj->getOptionvalue('yrm-btn-font-weight');
		$btnColor = $dataObj->getOptionvalue('btn-text-color');
		$fontFamily = $dataObj->getOptionvalue('expander-font-family');
		$borderRadius = $dataObj->getOptionvalue('btn-border-radius');
		
		$generalStyles = '<style type="text/css" id="toggle-expand-style">';
		$generalStyles .= '.yrm-toggle-expand-'.$id.' {';
			$generalStyles .= 'font-size: '.$fontSize.';';
			$generalStyles .= 'font-weight: '.$fontWeight.';';
			if(YRM_PKG > 1) {
				$generalStyles .= 'color: '.$btnColor.';';
				$generalStyles .= 'font-family: '.$fontFamily.';';
				$generalStyles .= 'border-radius: '.$borderRadius.';';
			}
		$generalStyles .= '}';
		$generalStyles .= '.yrm-toggle-expand-'.$id.':hover {';
		$generalStyles .= '		color: '.$hoverTextColor.' !important;';
		$generalStyles .= '}';
		
		$generalStyles .= '</style>';
		
		echo $generalStyles;
		
		if($type == 'button' || $type == 'popup') {
			$hoverBgColor = $dataObj->getOptionValue('btn-hover-bg-color');
			$buttonBorderWidth = $dataObj->getOptionValue('button-border-width');
			$buttonBorderColor = $dataObj->getOptionValue('button-border-color');
			
			// general styling of button type
			$buttonWidth = $dataObj->getOptionValue('button-width');
			$buttonHeight = $dataObj->getOptionValue('button-height');
			$btnBackgroundColor = $dataObj->getOptionValue('btn-background-color');
			
			$buttonGeneralStyles = '<style type="text/css" id="toggle-expand-style">';
			$buttonGeneralStyles .= '.yrm-toggle-expand-'.$id.' {';
			$buttonGeneralStyles .= 'width: '.$buttonWidth.';';
			$buttonGeneralStyles .= 'height: '.$buttonHeight.';';
			if(YRM_PKG > 1) {
				$buttonGeneralStyles .= 'background-color: '.$btnBackgroundColor.';';
			}
			$buttonGeneralStyles .= '}';
			$buttonGeneralStyles .= '</style>';
			
			echo $buttonGeneralStyles;
			
			if($dataObj->getOptionValue('hover-effect')) {
				echo "<style>
					.yrm-toggle-expand-$id:hover {
						background-color: $hoverBgColor !important;
						color: $hoverTextColor !important;
					}
				</style>";
			}

			if($dataObj->getOptionValue('button-border')) {
				echo "<style>
				.yrm-toggle-expand.yrm-toggle-expand-$id {
					border-width: $buttonBorderWidth $important;
					border-color: $buttonBorderColor $important;
				}
				</style>";
			}

			if($dataObj->getOptionValue('button-box-shadow')) {
				$shadowHorizontal = $dataObj->getOptionValue('button-box-shadow-horizontal-length').'px';
				$shadowVertical = $dataObj->getOptionValue('button-box-shadow-vertical-length').'px';
				$shadowBlurRadius = $dataObj->getOptionValue('button-box-blur-radius').'px';
				$shadowSpreadRadius = $dataObj->getOptionValue('button-box-spread-radius').'px';
				$shadowColor = $dataObj->getOptionvalue('button-box-shadow-color');
				echo "<style id='shadowTest'>
					.yrm-toggle-expand {
						-webkit-box-shadow: $shadowHorizontal $shadowVertical $shadowBlurRadius $shadowSpreadRadius $shadowColor;
						-moz-box-shadow: $shadowHorizontal $shadowVertical $shadowBlurRadius $shadowSpreadRadius $shadowColor;
						box-shadow: $shadowHorizontal $shadowVertical $shadowBlurRadius $shadowSpreadRadius $shadowColor;
					}
				</style>";
			}
		}
	}

}
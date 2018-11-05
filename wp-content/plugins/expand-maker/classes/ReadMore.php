<?php
abstract class ReadMore {

	public function getRemoveOptions() {

		return array();
	}

	public function includeOptionsBlock($dataObj) {

	}

	public static function RemoveOption($option) {

		global $YrmRemoveOptions;
		return isset($YrmRemoveOptions[$option]);
	}

	public static function isActiveReadMore($id) {
		$isActiveSaved = get_option('yrm-read-more-'.$id);

		if ($isActiveSaved == -1) {
			return false;
		}

		return true;
	}

	public static function allowRender($id) {
		if (is_admin()) {
			return true;
		}
		return self::isActiveReadMore($id);
	}
}
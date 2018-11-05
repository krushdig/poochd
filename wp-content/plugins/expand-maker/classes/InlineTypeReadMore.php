<?php
class InlineTypeReadMore extends ReadMore {

	public function getRemoveOptions() {

		return array(
			'button-width' => 1,
			'button-height' => 1,
			'btn-background-color' => 1,
			'btn-border-radius' => 1,
			'button-border' => 1,
			'button-box-shadow' => 1,
			'btn-hover-bg-color' => 1
		);
	}

	public static function params() {

		$data = array();

		return $data;
	}

	public function includeOptionsBlock($dataObj) {

	}
}
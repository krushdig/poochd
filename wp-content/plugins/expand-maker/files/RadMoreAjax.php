<?php
class RadMoreAjax {
	
	public function init() {
		
		add_action('wp_ajax_delete_rm', array($this, 'deleteRm'));
		add_action('wp_ajax_yrm_switch_status', array($this, 'switchStatus'));
		add_action('wp_ajax_yrm_export', array($this, 'exportData'));
		add_action('wp_ajax_yrm_import_data', array($this, 'importData'));
	}
	
	public function importData()
	{
		check_ajax_referer('YrmNonce', 'ajaxNonce');
		$url = sanitize_text_field($_POST['attachmentUrl']);
		$contents = unserialize(base64_decode(file_get_contents($url)));
		global $wpdb;
		
		foreach ($contents as $tableName => $tableData) {
			foreach ($tableData as $rowData) {
				$values = "'".implode(array_values($rowData), "','")."'";
				$columns = "`".implode(array_keys($rowData), "`, ")."'";
				$contentsStr = '';
				foreach (array_keys($rowData) as $key => $value) {
					$contentsStr .= '`'.$value.'`'.', ';
				}
				$contentsStr = rtrim($contentsStr, ', ');
				$customInsertSql = $wpdb->prepare("INSERT INTO ".$wpdb->prefix.$tableName."($contentsStr) VALUES ($values)");
				$wpdb->query($customInsertSql);
			}
		}
		wp_die();
	}
	
	public function exportData() {
		check_ajax_referer('YrmNonce', 'ajaxNonce');
		global $wpdb;
		$data = array();
		
		$tables = array('expm_maker', 'expm_maker_pages');
		
		foreach ($tables as $table) {
			$dataSql = 'SELECT * FROM '.$wpdb->prefix.$table;
			$getAllData = $wpdb->get_results($dataSql, ARRAY_A);
			$currentTable = array();
			foreach ($getAllData as $currentData) {
				$currentTable[] =  $currentData;
			}
			$data[$table] = $currentTable;
		}
		
		print base64_encode(serialize($data));
		wp_die();
	}
	
	public function deleteRm() {

		check_ajax_referer('YrmNonce', 'ajaxNonce');
		$id  = (int)$_POST['readMoreId'];

		$dataObj = new ReadMoreData();
		$dataObj->setId($id);
		$dataObj->delete();

		echo '';
		die();
	}

	public function switchStatus() {
		check_ajax_referer('YrmNonce', 'ajaxNonce');
		$postId = $_POST['readMoreId'];
		$status = -1;

		if ($_POST['isChecked'] == 'true') {
			$status = true;
		}
		update_option('yrm-read-more-'.$postId, $status);
		wp_die();
	}
}
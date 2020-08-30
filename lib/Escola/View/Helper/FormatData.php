<?php
class Escola_View_Helper_FormatData extends Zend_View_Helper_Abstract {
	function formatData($data) {
		if ($data) {
			$obj = new Zend_Date($data);
			$filter = new Zend_Validate_Date();
			if ($filter->isValid($obj->get("Y-MM-dd"))) {
				return $obj->get("dd/MM/Y");
			}
		} 
		return "";
	}
}
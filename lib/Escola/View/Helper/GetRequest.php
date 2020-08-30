<?php 
class Escola_View_Helper_GetRequest extends Zend_View_Helper_Abstract {
	public function getRequest() {
		return Zend_Controller_Front::getInstance()->getRequest();
	}
}
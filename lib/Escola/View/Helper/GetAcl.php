<?php 
class Escola_View_Helper_GetAcl extends Zend_View_Helper_Abstract {
	public function getAcl() {
		return Escola_Acl::getInstance();
	}
}
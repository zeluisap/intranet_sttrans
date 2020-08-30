<?php
class Escola_Validate_Cnpj extends Zend_Validate_Abstract {
	
	function isValid($value) {
		return Escola_Util::isCnpjValid($value);
	}

}
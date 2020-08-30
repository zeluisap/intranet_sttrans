<?php
class Escola_View_Helper_FormatCpf extends Zend_View_Helper_Abstract {
	function formatCpf($cpf) {
		return Escola_Util::formatCpf($cpf);
	}
}
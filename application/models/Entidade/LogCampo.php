<?php
class LogCampo extends Escola_Entidade {
	public function serialize() {
		Zend_Debug::dump($this);
		die();
	}
}
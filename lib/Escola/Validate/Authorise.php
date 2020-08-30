<?php
class Escola_Validate_Authorise extends Zend_Validate_Abstract {
	const NOT_AUTHORISED = "notAuthorised";
	protected $_authAdapter;
	protected $_messageTemplates = array( self::NOT_AUTHORISED => "Nenhum usuário encontrado!" );
	
	public function getAuthAdapter() {
		return $this->_authAdapter;
	}
	
	public function isValid($value, $context = null) {
		$value = (string)$value;
		$this->_setValue($value);
		if (is_array($context)) {
			if (!isset($context["login_senha"])) {
				return false;
			}
		}
		$dbAdapter = Zend_Registry::get("db");
		$this->_authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);
		$this->_authAdapter->setTableName("usuario");
		$this->_authAdapter->setIdentityColumn("id_usuario");
		$this->_authAdapter->setCredentialColumn("senha");
		$senha = sha1($context["login_senha"]); 
		$tb_usuario = new TbUsuario();
		$usuario = $tb_usuario->getPorCPF($context["login_cpf"]);
		if (!$usuario) {
			$this->_error("NENHUM USUÁRIO LOCALIZADO!");
			return false;
		}
		$this->_authAdapter->setIdentity($usuario->id_usuario);
		$this->_authAdapter->setCredential($senha);
		$auth = Zend_Auth::getInstance();
		$result = $auth->authenticate($this->_authAdapter);
		if (!$result->isValid()) {
			$this->_error("USUÁRIO OU SENHA INVÁLIDO!");
			return false;
		}
		return true;
	}
}
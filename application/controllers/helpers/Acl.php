<?php
class Zend_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract {
	protected $_action;
	protected $_auth;
	protected $_acl;
	protected $_controlerName;
	
	public function __construct(Zend_View_Interface $view = null, array $options = array()) {
		$this->_auth = Zend_Auth::getInstance();
		$this->_acl = Escola_Acl::getInstance();
	}
	
	public function init() {
		$this->_action = $this->getActionController();
		$controller = $this->_action->getRequest()->getControllerName();
	}
	
	public function logado() {
		if ($this->_auth->hasIdentity()) {
			return $this->_acl->getUsuarioLogado();
		}
		return false;
	}	
}
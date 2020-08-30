<?php
class Escola_Acl extends Zend_Acl {
	private $_db;
	static private $_acl = null;
	
	public function __construct() {
		$this->_db = Zend_Registry::get("db");
		$this->carregarRoles();
		$this->carregarResources();
		$this->carregarPermissoes();
	}
	
	public function getInstance() {
		if (self::$_acl == null) {
			self::$_acl = new self();
		}
		return self::$_acl;
	}
	
	protected function carregarRoles() {
		$tb_grupos = new TbGrupo();
		$grupos = $tb_grupos->fetchAll();
		if ($grupos->count()) {
			foreach ($grupos as $grupo) {
				$parent = null;
				if ($grupo->id_grupo_inferior) {
					$parent = $grupo->id_grupo_inferior;
				}
				$this->addRole(new Zend_Acl_Role($grupo->id_grupo, $parent));
			}
		}
	}
	
	protected function carregarResources() {
		$tb = new TbModulo();
		$rg = $tb->fetchAll();
		if (count($rg)) {
			foreach ($rg as $obj) {
				$this->add(new Zend_Acl_Resource($obj->id_modulo));
			}
		}
	}
	
	protected function carregarPermissoes() {
		$tb = new TbPermissao();
		$rg = $tb->fetchAll();
		if (count($rg)) {
			foreach ($rg as $obj) {
				$acao = $obj->findParentRow("TbAcao");
				$modulo = $acao->findParentRow("TbModulo");
				$this->allow($obj->id_grupo, $modulo->id_modulo, $acao->id_acao);
			}
		}
	}
	
	public function getUsuarioLogado() {
		$auth = Zend_Auth::getInstance();
		if ($auth->hasIdentity()) {
			$rg = $auth->getIdentity();
			$tb = new TbUsuario();
			return $tb->fetchRow(" id_usuario = {$rg->id_usuario} ");
		}
		return false;
	}
	
	public function addModulo($modulo) {
		$resource = new Zend_Acl_Resource($modulo->getId());
		if (!$this->has($resource)) {
			$this->add($resource);
		}
	}
	
	public function permitir($controller, $action) {
		$user = $this->getUsuarioLogado();
		if (!$user) {
			return false;
		}
		$grupos = $user->pegaTbGrupo();
		$allowed = false;
		if ($grupos) {
			$tb = new TbModulo();
			$modulo = $tb->getPorController($controller);
			if ($modulo) {
				$tb = new TbAcao();
				$acao = $tb->getPorAction($modulo, $action);
				if ($acao) {
					foreach ($grupos as $grupo) {
						if ($this->isAllowed($grupo->id_grupo, $modulo->id_modulo, $acao->id_acao)) {
							$allowed = true;
							break;
						}
					}
					return $allowed;
				}
			}
		}
		return true;
	}
}
<?php
class Grupo extends Escola_Entidade {
	
	public function setFromArray(array $dados) {
		if (isset($dados["descricao"])) {
			$dados["descricao"] = strtoupper($dados["descricao"]);
		}
		parent::setFromArray($dados);
	}
	
	public function padrao() {
		return ($this->padrao == "S");
	}
	
	public function save() {
		if (!$this->id_grupo_inferior) {
			$this->id_grupo_inferior = null;
		}
		if ($this->padrao()) {
			$tb = new TbGrupo();
			$tb->update(array("padrao" => "N"), array());
		}
		parent::save();
	}
	
	public function permitir($acao) {
		$tb = new TbPermissao();
		$row = $tb->createRow();
		$row->setFromArray(array("id_acao" => $acao->id_acao,
								 "id_grupo" => $this->id_grupo));
		if (!$row->getErrors()) {
			$row->save();
		}
		$modulo = $acao->findParentRow("TbModulo");
		Escola_Acl::getInstance()->allow($this->getId(), $modulo->getId(), $acao->getId());
	}
	
	public function negar($acao) {
		$tb = new TbPermissao();
		$rg = $tb->listar(array("id_acao" => $acao->id_acao,
								"id_grupo" => $this->id_grupo));
		if ($rg) {
			foreach ($rg as $obj) {
				$obj->delete();
			}
		}
		$modulo = $acao->findParentRow("TbModulo");
		Escola_Acl::getInstance()->deny($this->getId(), $modulo->getId(), $acao->getId());
	}
	
	public function delete() {
		$rg = $this->findDependentRowSet("TbPermissao");
		foreach ($rg as $obj) {
			$obj->delete();
		}
		parent::delete();
	}
	
	public function getErrors() {
		$errors = array();
		if (!trim($this->descricao)) {
			$errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$sql = $this->select();
		$sql->where("descricao = '{this->descricao}'");
		$sql->where("id_grupo <> " . $this->getId());
		$rg = $this->getTable()->fetchAll($sql);
		if (count($rg)) {
			$errors[] = "GRUPO JÁ CADASTRADO!";
		}
		if (count($errors)) {
			return $errors;
		}
		return false;
	}
	
	public function mostrarPadrao() {
		if ($this->padrao == "S") {
			return "SIM";
		}
		return "NÃO";
	}
	
	public function isAllowed($acao) {
		$modulo = $acao->findParentRow("TbModulo");
		$acl = Escola_Acl::getInstance();
		return $acl->isAllowed($this->getId(), $modulo->getId(), $acao->getId());
	}
	
	public function getUsuarios() {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("ug" => "usuario_grupo"), array("id_usuario"));
		$sql->join(array("u" => "usuario"), "ug.id_usuario = u.id_usuario");
		$sql->join(array("pf" => "pessoa_fisica"), "u.id_pessoa_fisica = pf.id_pessoa_fisica");
		$sql->where("ug.id_grupo = " . $this->getId());
		$sql->group("ug.id_usuario");
		$sql->order("pf.nome");
		$stmt = $db->query($sql);
		$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
		if (count($rg)) {
			$tb = new TbUsuario();
			$items = array();
			foreach ($rg as $obj) {
				$items[] = $tb->getPorId($obj->id_usuario);
			}
			return $items;
		}
		return false;
	}
		
	public function getUsuariosPorPagina($dados = array()) {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("ug" => "usuario_grupo"), array("id_usuario"));
		$sql->join(array("u" => "usuario"), "ug.id_usuario = u.id_usuario", array());
		$sql->join(array("pf" => "pessoa_fisica"), "u.id_pessoa_fisica = pf.id_pessoa_fisica", array());
		$sql->where("ug.id_grupo = " . $this->getId());
		$sql->group("ug.id_usuario");
		$sql->order("pf.nome");
		$adapter = new Zend_Paginator_Adapter_DbSelect($sql);
		$paginator = new Zend_Paginator($adapter);
		if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
			$paginator->setCurrentPageNumber($dados["pagina_atual"]);
		}
		$paginator->setItemCountPerPage(50);
		return $paginator;
	}

	public function limparUsuarios() {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = "delete from usuario_grupo where id_grupo = {$this->id_grupo}";
			$rg = $db->query($sql);
			try {
				$rg->execute();
			} catch (Exception $e) {
				
			}
		}
	}
    
    public function toString() {
        return $this->descricao;
    }
}
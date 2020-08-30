<?php
class Usuario extends Escola_Entidade {
	protected $_pessoa_fisica;
	protected $_senha_confirmar;
	
	public function init() {
		parent::init();
		$this->_pessoa_fisica = $this->getPessoaFisica();
		$this->_senha_confirmar = "";
	}
	
	public function pega_pessoa_fisica() {
		return $this->_pessoa_fisica;
	}
	
	public function atualizaGrupo() {
		$tb = new TbGrupo();
		$grupo = $tb->getPadrao();
		if ($grupo) {
			$this->addGrupo($grupo);
		}
	}
	
	public function setFromArray(array $dados) {
		if (isset($dados["nascimento_id_municipio"]) && !$dados["nascimento_id_municipio"]) {
			if (isset($dados["municipio"]) && $dados["municipio"]) {
				$tb = new TbMunicipio();
				$sql = $tb->select();
				$sql->where(" descricao = '" . $dados["municipio"] . "' ");
				$rg = $tb->fetchAll($sql);
				if (count($rg)) {
					$dados["nascimento_id_municipio"] = $rg[0]->current()->getId();
				} else {
					$id_uf = 0;
					if (isset($dados["id_uf"]) && $dados["id_uf"]) {
						$id_uf = $dados["id_uf"];
					} elseif (isset($dados["dinamic_id_uf"]) && $dados["dinamic_id_uf"]) {
						$id_uf = $dados["dinamic_id_uf"];
					} elseif (isset($dados["dinamic_uf"]) && $dados["dinamic_uf"]) {
						if (isset($dados["id_pais"]) && $dados["id_pais"]) {
							$tb_uf = new TbUf();
							$uf = $tb_uf->createRow();
							$uf->setFromArray(array("descricao" => $dados["dinamic_uf"],
													"id_pais" => $dados["id_pais"]));
							$uf->save();
							$id_uf = $uf->getId();
						}
					}
					if ($id_uf) {
						$municipio = $tb->createRow();
						$municipio->setFromArray(array("descricao" => $dados["municipio"],
													   "id_uf" => $id_uf));
						$municipio->save();
						$dados["nascimento_id_municipio"] = $municipio->getId();
					}
				}
			}
		}
		
		if (isset($dados["id_pessoa_fisica"]) && $dados["id_pessoa_fisica"]) {
			$tb = new TbPessoaFisica();
			$this->_pessoa_fisica = $tb->getPorId($dados["id_pessoa_fisica"]);
		}

		$this->_pessoa_fisica->setFromArray($dados);

		if (isset($dados["senha"])) {
			$dados["senha"] = md5($dados["senha"]);
		}

		if (isset($dados["senha_confirmar"])) {
			$this->_senha_confirmar = md5($dados["senha_confirmar"]);
		}

		if (!$this->senha) {
			$dados["senha"] = md5('123mudar');
			$dados["senha_confirmar"] = md5('123mudar');
		}

		parent::setFromArray($dados);
	}
	
	public function getPessoaFisica() {
		$pessoa = $this->findParentRow("TbPessoaFisica");
		if ($pessoa) {
			return $pessoa;
		}
		if ($this->_pessoa_fisica) {
			return $this->_pessoa_fisica;
		}
		$tb = new TbPessoaFisica();
		return $tb->createRow();;
	}
	
	public function save() {
		$this->id_pessoa_fisica = $this->_pessoa_fisica->save();
		$id = parent::save();
		if ($id && !$this->pegaTbGrupo()) {
			$this->atualizaGrupo();
		}
		return $id;
	}
	
	public function getErrors() {
		$msgs = array();
		$err = $this->_pessoa_fisica->getErrors();
		if ($err) {
			$msgs = $err;
		}
		if (empty($this->id_usuario_situacao)) {
			$msgs[] = "CAMPO SITUAÇÃO DO USUÁRIO OBRIGATÓRIO!";
		}
		if (empty($this->senha)) {
			// $msgs[] = "CAMPO SENHA OBRIGATÓRIO!";
		} elseif ($this->_senha_confirmar && $this->senha != $this->_senha_confirmar) {
			$msgs[] = "SENHAS NÃO COINCIDEM!";
		}
		$tb = new TbUsuario();
		$rg = $tb->fetchAll(" id_pessoa_fisica = '{$this->id_pessoa_fisica}' and id_usuario <> '{$this->id_usuario}' ");
		if ($rg->count()) {
			$msgs[] = "USUÁRIO JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function addGrupo($grupo) {
		if ($this->id_usuario) {
			$sql = "select * from usuario_grupo where id_usuario = {$this->id_usuario} and id_grupo = {$grupo->id_grupo}";
			$db = Zend_Registry::get("db");
			$rg = $db->query($sql);
			$rows = $rg->fetchAll();
			if (!count($rows)) {
				$sql = "insert into usuario_grupo (id_usuario, id_grupo) values ({$this->id_usuario}, {$grupo->id_grupo})";
				$rg = $db->query($sql);
				try {
					$rg->execute();
				} catch (Exception $e) {
					
				}
			}
		}
	}
	
	public function removeGrupo($grupo) {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = "delete from usuario_grupo where id_usuario = {$this->id_usuario} and id_grupo = {$grupo->id_grupo}";
			$db = Zend_Registry::get("db");
			$rg = $db->query($sql);
			try {
				$rg->execute();
			} catch (Exception $e) {
				
			}
            return (!$this->pertence($grupo));
		}
	}
	
	public function pegaTbGrupo() {
		$db = Zend_Registry::get("db");
		$sql = "select *
				from usuario_grupo a, grupo b
				where (a.id_grupo = b.id_grupo)
				and (a.id_usuario = {$this->id_usuario})
				order by b.descricao";
		$stmt = $db->query($sql);
		$rows = $stmt->fetchAll();
		if (count($rows)) {
			$tb = new TbGrupo();
			$items = array();
			foreach ($rows as $row) {
				$items[] = $tb->fetchRow(" id_grupo = " . $row["id_grupo"] . " ");
			}
			return $items;
		}
		return false;
	}
	
	public function ativo() {
		$sit = $this->findParentRow("TbUsuarioSituacao");
		return $sit->ativo();
	}
	
	public function criaUsuarioSenha() {
		$id = sha1($this->id_usuario);
		$tb = new TbUsuarioSenha();
		$row = $tb->createRow();
		$row->setFromArray(array("hash" => $id,
								 "id_usuario" => $this->id_usuario));
		$id = $row->save();
		if ($id) {
			return $row;
		}
        return false;
	}
	
	public function geraLinkRecuperaSenha() {
		$row = $this->criaUsuarioSenha();
        return $_SERVER["PHP_SELF"] . Escola_Util::url(array("controller" => "auth", "action" => "recovery", "hash" => $row->hash));
	}
	
	public function ultimoLogin() {
		$tb_log = new TbLog();
		$logins = $tb_log->listarLogin($this);
		if ($logins && (count($logins) > 1)) {
			return $logins[1];
		}
		return false;
	}
	
	public function getTbGrupo() {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("ug" => "usuario_grupo"), array("id_grupo"));
		$sql->join(array("u" => "usuario"), "ug.id_usuario = u.id_usuario");
		$sql->join(array("g" => "grupo"), "ug.id_grupo = g.id_grupo");
		$sql->where("ug.id_usuario = " . $this->getId());
		$sql->group("ug.id_grupo");
		$sql->group("g.descricao");
		$stmt = $db->query($sql);
		$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
		if (count($rg)) {
			$tb = new TbGrupo();
			$items = array();
			foreach ($rg as $obj) {
				$items[] = $tb->getPorId($obj->id_grupo);
			}
			return $items;
		}
		return false;
	}
	
	public function toString() {
		return $this->getPessoaFisica()->toString();
	}
	
	public function limparTbGrupo() {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = "delete from usuario_grupo where id_usuario = {$this->id_usuario}";
			$rg = $db->query($sql);
			try {
				$rg->execute();
			} catch (Exception $e) {
				
			}
		}
	}
	
	public function verificaPermissao($dados) {
		$grupos = $this->pegaTbGrupo();
		$allowed = false;
		if ($grupos) {
			$modulo = false;
			if (isset($dados["modulo"]) && $dados["modulo"]) {
				$modulo = $dados["modulo"];
			} elseif (isset($dados["controller"]) && $dados["controller"]) {
				$tb = new TbModulo();
				$modulo = $tb->getPorController($dados["controller"]);
			}
			if ($modulo) {
				$acao = false;
				if (isset($dados["action"]) && $dados["action"]) {
					$tb = new TbAcao();
					$acao = $tb->getPorAction($modulo, $dados["action"]);
				}
				if ($acao) {
					$acl = Escola_Acl::getInstance();
					foreach ($grupos as $grupo) {
						if ($acl->isAllowed($grupo->id_grupo, $modulo->id_modulo, $acao->id_acao)) {
							$allowed = true;
							break;
						}
					}
				}
			}
		}
		return $allowed;
	}
	
	public function pertence($grupo) {
		if ($this->getId()) {
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("usuario_grupo"));
			$sql->where("id_usuario = " . $this->getId());
			$sql->where("id_grupo = " . $grupo->getId());
			$stmt = $db->query($sql);
			$rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
			if ($rg && count($rg)) {
				return true;
			}
		}
		return false;
	}
	
	public function administrador() {
		$tb = new TbGrupo();
		$grupo = $tb->getPorDescricao("ADMINISTRADOR");
		if ($this->pertence($grupo)) {
			return true;
		}
		return false;
	}
	
	public function permissao($modulo) {
		$action = "index";
		$acao = $modulo->pegaAcaoPrincipal();
		if ($acao) {
			$action = $acao->action;
		}
		if ($this->verificaPermissao(array("modulo" => $modulo, "action" => $action))) {
			return true;
		}
		return false;
	}
        
    
    public function getDeleteErrors() {
        $errors = array();

        if ($this->getId()) {
            $tb = new TbCredencialOcorrencia();
            $sql = $tb->select();
            $sql->where("id_usuario = {$id}");
            $objs = $tb->fetchAll($sql);
            if ($objs && count($objs)) {
                $errors[] = "Falha ao Executar Operação, Usuario Vinculado a Ocorrencias de Credencial!";
            }
        }
        
        if (!count($errors)) {
            return false;
        }
        
        return $errors;
    }
}
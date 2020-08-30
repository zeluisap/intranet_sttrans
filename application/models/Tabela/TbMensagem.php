<?php
class TbMensagem extends Escola_Tabela {
	protected $_name = "mensagem";
	protected $_rowClass = "Mensagem";
	protected $_referenceMap = array("MensagemTipo" => array("columns" => array("id_mensagem_tipo"),
												   "refTableClass" => "TbMensagemTipo",
												   "refColumns" => array("id_mensagem_tipo")),
									 "Usuario" => array("columns" => array("id_usuario"),
												   "refTableClass" => "TbUsuario",
												   "refColumns" => array("id_usuario")));
	
	public function buscarStatus($funcionario) {
		return array("total" => $this->buscarTotalMensagem($funcionario),
					 "nao_lidas" => $this->buscarTotalNaoLidas($funcionario));
	}
	
	public function buscarTotalMensagem($funcionario) {
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("m" => "mensagem"), "count(m.id_mensagem) as quantidade");
		$sql->join(array("mt" => "mensagem_tipo"), "m.id_mensagem_tipo = mt.id_mensagem_tipo", array());
		$sql->where("mt.chave = 'T'");
		$lotacaos = $funcionario->pegaLotacao();
		if ($lotacaos) {
			$ids = array();
			foreach ($lotacaos as $lotacao) {
				if ($lotacao->ativo()) {
					$setor = $lotacao->findParentRow("TbSetor");
					$sups = $setor->getIdSuperior();
					$ids = array_merge($ids, $sups);
					$sql->orWhere("mt.chave = 'A' and chave_destino = " . $setor->getId());
				}
			}
                        if (count($ids)) {
                            $sql->orWhere("((mt.chave = 'S') or (mt.chave = 'E')) and chave_destino in (" . implode(", ", $ids) . ")");
                        }
		}
		$sql->orWhere("mt.chave = 'P' and chave_destino = " . $funcionario->getId());
		$sql->order("m.data");
		$sql->order("m.hora");
		$stmt = $db->query($sql);
		if ($stmt) {
			$items = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
			if ($items && count($items)) {
				return $items[0]->quantidade;
			}
		}
		return 0;
	}
	
	public function buscarTotalNaoLidas($funcionario) {
		$usuarios = TbUsuario::getPorPessoaFisica($funcionario->pega_pessoa_fisica());
		if ($usuarios) {
			$usuario = $usuarios[0];
		}
		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("m" => "mensagem"), "count(m.id_mensagem) as quantidade");
		$sql->join(array("mt" => "mensagem_tipo"), "m.id_mensagem_tipo = mt.id_mensagem_tipo", array());
		$where = array("(mt.chave = 'T')");
		$lotacaos = $funcionario->pegaLotacao();
		if ($lotacaos) {
			$ids = array();
			foreach ($lotacaos as $lotacao) {
				if ($lotacao->ativo()) {
					$setor = $lotacao->findParentRow("TbSetor");
					$sups = $setor->getIdSuperior();
					$ids = array_merge($ids, $sups);
					$where[] = "(mt.chave = 'A' and chave_destino = " . $setor->getId() . ")";
				}
			}
                        if (count($ids)) {
                            $where[] = "(((mt.chave = 'S') or (mt.chave = 'E')) and chave_destino in (" . implode(", ", $ids) . "))";
                        }
		}
		$where[] = "(mt.chave = 'P' and chave_destino = " . $funcionario->getId() . ")";
		$sql->where("(" . implode(" or ", $where) . ")");
		$sql->where("not exists(select * from usuario_mensagem where id_usuario = " . $usuario->getId() . " and id_mensagem = m.id_mensagem)");
		$sql->order("m.data");
		$sql->order("m.hora");
		$stmt = $db->query($sql);
		if ($stmt) {
			$items = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
			if ($items && count($items)) {
				return $items[0]->quantidade;
			}
		}
		return 0;
	}
	
	public function buscarEntrada($dados) {
		if (isset($dados["funcionario"])) {
			$funcionario = $dados["funcionario"];
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("m" => "mensagem"), "m.id_mensagem");
			$sql->join(array("mt" => "mensagem_tipo"), "m.id_mensagem_tipo = mt.id_mensagem_tipo", array());
			$sql->where("mt.chave = 'T'");
			$lotacaos = $funcionario->pegaLotacao();
			if ($lotacaos) {
				$ids = array();
				foreach ($lotacaos as $lotacao) {
					if ($lotacao->ativo()) {
						$setor = $lotacao->findParentRow("TbSetor");
						$sups = $setor->getIdSuperior();
						$ids = array_merge($ids, $sups);
						$sql->orWhere("mt.chave = 'A' and chave_destino = " . $setor->getId());
					}
				}
                                if (count($ids)) {
                                    $sql->orWhere("((mt.chave = 'S') or (mt.chave = 'E')) and chave_destino in (" . implode(", ", $ids) . ")");
                                }
			}
			$sql->orWhere("mt.chave = 'P' and chave_destino = " . $funcionario->getId());
			$sql->order("m.data desc");
			$sql->order("m.hora desc");
			$adapter = new Zend_Paginator_Adapter_DbSelect($sql);
			$paginator = new Zend_Paginator($adapter);
			if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
				$paginator->setCurrentPageNumber($dados["pagina_atual"]);
			}
			$paginator->setItemCountPerPage(50);
			return $paginator;
		}
		return false;
	}
	
	public function listar($dados) {
		if (isset($dados["funcionario"])) {
			$funcionario = $dados["funcionario"];
			$usuarios = TbUsuario::getPorPessoaFisica($funcionario->pega_pessoa_fisica());
			if ($usuarios) {
				$usuario = $usuarios[0];
			}
			$sql = $this->select();
			$sql->where("id_usuario = " . $usuario->getId());
			$sql->order("data");
			$sql->order("hora");
			//$adapter = new Zend_Paginator_Adapter_DbSelect($sql);
			$adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
			$paginator = new Zend_Paginator($adapter);
			if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
				$paginator->setCurrentPageNumber($dados["pagina_atual"]);
			}
			$paginator->setItemCountPerPage(50);
			return $paginator;
		}
		return false;
	}
}
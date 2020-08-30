<?php 
class TbPacote extends Escola_Tabela {
	protected $_name = "pacote";
	protected $_rowClass = "Pacote";
	protected $_dependentTables = array("TbModulo");

	
	public function getSql($dados = array()) {
		$select = $this->select();
		$select->from(array("p" => "pacote"));
		if (isset($dados["id_modulo"]) && $dados["id_modulo"]) {
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("mp" => "modulo_pacote"));
			$sql->where("mp.id_modulo = ?", $dados["id_modulo"]);
			$sql->where("mp.id_pacote = p.id_pacote");
			$select->where("exists({$sql})");
		}
		$select->order("descricao");
		return $select;
	}

    public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$item = $this->createRow();
			$item->setFromArray(array("sigla" => "DEF", 
									  "descricao" => "Principal", 
									  "resumo" => "Pacote Principal do Sistema",
									  "icone" => "icon-home"));
			$item->save();
            $tb = new TbModulo();
            $modulos = $tb->listarTodos();
            if ($modulos && count($modulos)) {
                foreach ($modulos as $modulo) {
                    $item->addModulo($modulo);
                }
            }
		}
	}
	
	public function buscarPacotes($usuario) {
		if ($usuario && $usuario->getId()) {
			$db = Zend_Registry::get("db");
			$sql = $db->select();
			$sql->from(array("p" => "pacote"), array("p.id_pacote", "p.descricao"));
			$sql->join(array("mp" => "modulo_pacote"), "p.id_pacote = mp.id_pacote", array());
			$sql->join(array("m" => "modulo"), "mp.id_modulo = m.id_modulo", array());
			$sql->join(array("a" => "acao"), "m.id_modulo = a.id_modulo", array());
			$sql->join(array("pe" => "permissao"), "a.id_acao = pe.id_acao", array());
			$sql->join(array("f" => "grupo"), "f.id_grupo = pe.id_grupo", array());
			$sql->join(array("ug" => "usuario_grupo"), "f.id_grupo = ug.id_grupo", array());
                        $sql->where("m.status = 'A'");
                        $sql->where("p.status = 'A'");
			$sql->group("p.id_pacote");
			$sql->group("p.descricao");
			$sql->order("p.ordem");
			$stmt = $db->query($sql);
			if ($stmt && $stmt->rowCount()) {
				$items = array();
				while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
					$items[] = TbPacote::pegaPorId($obj->id_pacote);
				}
				return $items;
			}
		}
		return false;
	}
	
	public function pegaAtual() {
		$session = Escola_Session::getInstance();
		if (isset($session->default_id_pacote)) {
			$pacote = $this->pegaPorId($session->default_id_pacote);
			if ($pacote->getId()) {
				return $pacote;
			}
		}
	}
    
    public function pegaUltimaOrdem() {
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("pacote"), array("maximo" => "max(ordem)"));
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $obj = $stmt->fetch(Zend_Db::FETCH_OBJ);
            return $obj->maximo;
        }
        return 0;
    }
    
    public function getPorOrdem($ordem) {
        if ($ordem) {
            $rg = $this->fetchAll("ordem = {$ordem}");
            if ($rg && count($rg)) {
                return $rg->current();
            }
        }
        return false;
    }
}
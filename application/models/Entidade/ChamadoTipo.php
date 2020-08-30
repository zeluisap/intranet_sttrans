<?php
class ChamadoTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
        }
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_chamado_tipo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE CHAMADO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function pegaSetor($setor = false) {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("cts" => "chamado_tipo_setor"), array("id_setor"));
            $sql->join(array("s" => "setor"), "cts.id_setor = s.id_setor", array());
            $sql->where("cts.id_chamado_tipo = " . $this->getId());
            if ($setor) {
                $sql->where("cts.id_setor = " . $setor->getId());
            }
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
                $items = array();
                foreach ($rg as $obj) {
                    $items[] = TbSetor::pegaPorId($obj->id_setor);
                }
                return $items;
            }
        }
        return false;
    }
    
    public function addSetor($setor) {
        if ($setor && !$this->pegaSetor($setor)) {
            $db = Zend_Registry::get("db");
            $sql = "insert into chamado_tipo_setor
                    (id_chamado_tipo, id_setor)
                    values
                    (" . $this->getId() . ", " . $setor->getId() . ")";
            $db->query($sql);
        }
    }
    
    public function excluirSetor($setor) {
        if ($setor && $this->pegaSetor($setor)) {
            $db = Zend_Registry::get("db");
            $sql = "delete from chamado_tipo_setor
                    where id_chamado_tipo = " . $this->getId() . "
                    and id_setor = " . $setor->getId();
            $db->query($sql);
        }
    }
}
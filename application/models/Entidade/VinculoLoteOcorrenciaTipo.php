<?php
class VinculoLoteOcorrenciaTipo extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_vinculo_lote_ocorrencia <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE OCORRÊNCIA DO LOTE JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $db = Zend_Registry::get("db");
        $sql = $db->select();
        $sql->from(array("vlo" => "vinculo_lote_ocorrencia"));
        $sql->where("vlo.id_vinculo_lote_ocorrencia_tipo = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Existem Registros Vinculados a este Tipo, Exclua os Registros antes de efetuar esta operação!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;   
    }
    
    public function envio_pc() {
        return ($this->chave == "EPC");
    }
    
    public function pagamento() {
        return ($this->chave == "PG");
    }
}
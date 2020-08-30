<?php
class AditivoTipo extends Escola_Entidade {
    
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
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_aditivo_tipo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE ADITIVO JÁ CADASTRADO!";
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
        $sql->from(array("a" => "aditivo"));
        $sql->where("a.id_aditivo_tipo = {$this->getId()}");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $msgs[] = "Existem Aditivos Vinculados a este tipo de Aditivo, Exclua os Aditivos antes de efetuar esta operação!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;   
    }
    
    public function valor() {
        return ($this->chave == "V");
    }
    
    public function data() {
        return ($this->chave == "D");
    }
}
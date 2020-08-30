<?php
class Moeda extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["simbolo"])) {
            $dados["simbolo"] = Escola_Util::maiuscula($dados["simbolo"]);
        }
        if (isset($dados["valor_ref"])) {
            $dados["valor_ref"] = Escola_Util::montaNumero($dados["valor_ref"]);
        }
        parent::setFromArray($dados);
    }
    
    public function padrao() {
        return ($this->padrao == "S");
    }
    
    public function save() {
        if ($this->padrao()) {
            $db = Zend_Registry::get("db");
            $db->query("update moeda set padrao = 'N'");
        }
        return parent::save();
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->simbolo)) {
			$msgs[] = "CAMPO SÍMBOLO OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" descricao = '{$this->descricao}' and id_moeda <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "MOEDA JÁ CADASTRADA!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function mostrar_padrao() {
        switch ($this->padrao) {
            case "S": return "SIM"; break;
            case "N": return "NÃO"; break;
        }
        return "";
    }
}
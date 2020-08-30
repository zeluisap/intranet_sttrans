<?php
class Relatorio extends Escola_Entidade {
    
    public function toString() {
        return $this->descricao;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
		$msgs = array();
    	if (!$this->id_relatorio_tipo) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!trim($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" id_relatorio_tipo = '{$this->id_relatorio_tipo}' and chave = '{$this->chave}' and id_relatorio <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "RELATÓRIO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}
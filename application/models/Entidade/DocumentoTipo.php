<?php
class DocumentoTipo extends Escola_Entidade {
    
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
		if (!$this->id_documento_tipo_target) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_documento_tipo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "TIPO DE DOCUMENTO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function save() {
        /*
    	if (!$this->findParentRow("TbDocumentoTipoTarget")->administrativo()) {
    		$this->possui_numero = "S";
    	}
         */
    	return parent::save();
    }
    
    public function mostrarPossuiNumero() {
    	if ($this->possui_numero()) {
    		return "SIM";
    	}
    	return "NÃO";
    }
    
    public function possui_numero() {
    	return ($this->possui_numero == "S");
    }
    
    public function processo() {
    	return ($this->chave == "P");
    }
}
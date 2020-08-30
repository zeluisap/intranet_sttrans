<?php
class InfoTipo extends Escola_Entidade {
	
	public function init() {
		if (!$this->getId() || !$this->imagem) {
			$this->imagem = "N";
		}
	}
    
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
        $tb = $this->getTable();
        $sql = $tb->select();
        $sql->where(" chave = '{$this->chave}' ");
        $sql->where(" id_info_tipo <> '" . $this->getId() . "' ");
        $rg = $tb->fetchAll($sql);
        if ($rg && count($rg)) {
            $msgs[] = "REGISTRO JÁ ADICIONADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
    }
    
    public function galeria() {
        return ($this->chave == "G");
    }
    
    public function imagem() {
    	return ($this->imagem == "S");
    }
}
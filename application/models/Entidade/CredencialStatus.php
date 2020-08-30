<?php
class CredencialStatus extends Escola_Entidade {
    
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
        $filter = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"])) {
			$dados["chave"] = $filter->filter(utf8_decode($dados["chave"]));
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter(utf8_decode($dados["descricao"]));
		}
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_credencial_status <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "TIPO DE CREDENCIAL JÁ CADASTRADO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function pendente() {
        return ($this->chave == "P");
    }
    
    public function deferido() {
        return ($this->chave == "D");
    }
    
    public function indeferido() {
        return ($this->chave == "I");
    }
    
    public function getDeleteErrors() {
        $erros = array();
        
        if ($this->getId()) {
            $tb = new TbCredencial();
            $sql = $tb->select("count(c.id_credencial) as total");
            $sql->from(array("c" => "credencial"));
            $sql->where("c.id_credencial_status = {$this->getId()}");
            
            $objs = $tb->fetchAll($sql);
            if ($objs) {
                $obj = $objs->current();
                if ($obj->total > 0) {
                    $erros[] = "Existem Credenciais Vinculadas ao Status.";
                }
            }
        }
        
        if (count($erros)) {
            return $erros;
        }
        return false;
    }
}
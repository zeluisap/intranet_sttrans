<?php
class BolsaTipo extends Escola_Entidade {
    
    protected $_valor = false;
    
    public function init() {
        parent::init();
        $this->_valor = $this->pega_valor();
    }
    
    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }
    
	public function toString() {
		return $this->descricao;
	}

	public function setFromArray(array $dados) {
        $filter = new Zend_Filter_StringToUpper();
		if (isset($dados["chave"])) {
			$dados["chave"] = $filter->filter($dados["chave"]);
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = $filter->filter($dados["descricao"]);
		}
        $this->_valor->setFromArray($dados);
		parent::setFromArray($dados);
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->id_vinculo)) {
			$msgs[] = "CAMPO VÍNCULO OBRIGATÓRIO!";
		}
		if (empty($this->id_previsao_tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (empty($this->chave)) {
			$msgs[] = "CAMPO CHAVE OBRIGATÓRIO!";
		}
		if (empty($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!$this->_valor->valor) {
			$msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
		}
		$rg = $this->getTable()->fetchAll("(chave = '{$this->chave}' and id_vinculo = '{$this->id_vinculo}' and id_previsao_tipo = '{$this->id_previsao_tipo}') and id_bolsa_tipo <> '" . $this->getId() . "'");
		if ($rg && count($rg)) {
			$msgs[] = "TIPO DE BOLSA JÁ CADASTRADO PARA ESTE CONVÊNIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function save() {
        $this->id_valor = $this->_valor->save();
        parent::save();
    }
    
    public function mostrar_valor() {
        return $this->_valor->toString();
    }
    
    public function getDeleteErrors() {
        $errors = array();
        if ($this->getId()) {
            $tb = new TbBolsista();
            $sql = $tb->select();
            $sql->where("id_bolsa_tipo = {$this->getId()}");
            $db = Zend_Registry::get("db");
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $errors[] = "Existem Bolsistas Vinculados a Este Tipo de Bolsa, Exclua os Bolsistas e Tente Novamente!";
            }
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }
    
    public function delete() {
        parent::delete();
        $this->_valor->delete();
    }
}
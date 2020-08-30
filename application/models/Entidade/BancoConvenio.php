<?php
class BancoConvenio extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->padrao = 'N';
        }
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["descricao"])) {
            $dados["descricao"] = Escola_Util::maiuscula($dados["descricao"]);
        }
        if (isset($dados["convenio"])) {
            $dados["convenio"] = Escola_Util::maiuscula($dados["convenio"]);
        }
        if (isset($dados["contrato"])) {
            $dados["contrato"] = Escola_Util::maiuscula($dados["contrato"]);
        }
        if (isset($dados["padrao"])) {
            $dados["padrao"] = Escola_Util::maiuscula($dados["padrao"]);
        }
        parent::setFromArray($dados);
    }
        
    public function getErrors() {
		$msgs = array();
        if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
        if (!trim($this->convenio)) {
			$msgs[] = "CAMPO CONVÊNIO OBRIGATÓRIO!";
		}
        if (!trim($this->contrato)) {
			$msgs[] = "CAMPO CONTRATO OBRIGATÓRIO!";
		}
        $rg = $this->getTable()->fetchAll(" convenio = '{$this->convenio}' and id_banco_convenio <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "CONVÊNIO BANCÁRIO JÁ CADASTRADO!";
        }        
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransporteGrupo");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function save($flag = false) {
        if ($this->padrao()) {
            $db = Zend_Registry::get("db");
            $db->query("update banco_convenio set padrao = 'N'");
        }
        parent::save($flag);
    }
    
    public function pega_info_bancaria() {
        if ($this->getId()) {
            $tb = new TbInfoBancariaRef();
            $ibrs = $tb->listar(array("tipo" => "C", "chave" => $this->getId()));
            if ($ibrs && count($ibrs)) {
                return $ibrs->current()->findParentRow("TbInfoBancaria");
            }
        }
        return false;
    }
    
    public function delete() {
        $ib = $this->pega_info_bancaria();
        if ($ib) {
            $ib->delete();
        }
        parent::delete();
    }
    
    public function toString() {
        $txt = array();
        $txt[] = $this->descricao;
        $txt[] = $this->convenio;
        return implode(" - ", $txt);
    }
    
    public function padrao() {
        return ($this->padrao == 'S');
    }
    
    public function mostrarPadrao() {
        if ($this->padrao()) {
            return "SIM";
        }
        return "NÃO";
    }
}
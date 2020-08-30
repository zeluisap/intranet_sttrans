<?php
class Lotacao extends Escola_Entidade {
    protected $_funcionario;
    
    public function chefe() {
        return ($this->chefe == "S");
    }
    
	public function init() {
		parent::init();
		$this->_funcionario = $this->getFuncionario();
        if (!$this->chefe) { $this->chefe = "N"; }
        if (!$this->data_inicial) { $this->data_inicial = date("Y-m-d"); }
        if (!$this->id_lotacao_tipo) {
            $tb = new TbLotacaoTipo();
            $lt = $tb->getPorChave("N");
            if ($lt) {
                $this->id_lotacao_tipo = $lt->getId();
            }
        }
	}
    
    public function pega_funcionario() {
        return $this->_funcionario;
    }
    
    public function setFromArray(array $dados) {
        $filter = new Zend_Validate_Date(array("format" => "dd/MM/Y"));
        if (isset($dados["data_inicial"])) {
            if ($filter->isValid($dados["data_inicial"])) {
                $data = new Zend_Date($dados["data_inicial"]);
                $dados["data_inicial"] = $data->get("Y-MM-dd");
            } else {
                $dados["data_inicial"] = NULL;
            }
        }
        if (isset($dados["data_final"])) {
            if ($filter->isValid($dados["data_final"])) {
                $data = new Zend_Date($dados["data_final"]);
                $dados["data_final"] = $data->get("Y-MM-dd");
            } else {
                $dados["data_final"] = NULL;
            }
        }
		if (isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
			$tb = new TbFuncionario();
			$this->_funcionario = $tb->getPorId($dados["id_funcionario"]);
		}
        $this->_funcionario->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
	public function getFuncionario() {
		$func = $this->findParentRow("TbFuncionario");
		if ($func) {
			return $func;
		}
		if ($this->_funcionario) {
			return $this->_funcionario;
		}
		$tb = new TbFuncionario();
		return $tb->createRow();;
	}
	
	public function save() {
            $this->id_funcionario = $this->_funcionario->save();
            if ($this->findParentRow("TbLotacaoTipo")->normal()) {
                $lotacaos = $this->_funcionario->pegaLotacao("N");
                if ($lotacaos && count($lotacaos)) {
                    foreach ($lotacaos as $lotacao) {
                        if ($lotacao->getId() != $this->getId()) {
                            $lotacao->delete();
                        }
                    }
                }
            }
            if ($this->chefe()) {
                $tb = $this->getTable();
                $sql = $tb->select();
                $sql->where("id_setor = '{$this->id_setor}'");
                $sql->where("chefe = 'S'");
                $items = $tb->fetchAll($sql);
                if ($items && count($items)) {
                    foreach ($items as $item) {
                        $item->chefe = 'N';
                        $item->save();
                    }
                }
            }
            if ($this->id_funcionario_funcao) {
                $ff = TbFuncionarioFuncao::pegaPorId($this->id_funcionario_funcao);
                if ($ff) {
                    $ff->zeraLotacao();
                }
            } else {
                $this->id_funcionario_funcao = NULL;
            }
            return parent::save();
	}
	
	public function getErrors() {
        $filter_date = new Zend_Validate_Date();
		$msgs = array();
		$err = $this->_funcionario->getErrors();
		if ($err) {
			$msgs = $err;
		}
		if (empty($this->id_setor)) {
			$msgs[] = "CAMPO SETOR OBRIGATÓRIO!";
		}
		if (empty($this->id_lotacao_tipo)) {
			$msgs[] = "CAMPO TIPO DE LOTAÇÃO OBRIGATÓRIO!";
		}
        if (empty($this->data_inicial)) {
            $msgs[] = "CAMPO DATA INICIAL OBRIGATÓRIO!";
        } elseif (!$filter_date->isValid($this->data_inicial)) {
            $msgs[] = "CAMPO DATA INICIAL INVÁLIDO!";
        } 
        if (!$this->findParentRow("TbLotacaoTipo")->normal() && !$this->data_final) {
            $msgs[] = "CAMPO DATA FINAL OBRIGATÓRIO NA LOTAÇÃO PROVISÓRIA!";
        }
        if ($this->data_inicial && $this->data_final) {
            $dtini = new Zend_Date($this->data_inicial);
            $dtfim = new Zend_Date($this->data_final);
            if ($dtfim->isEarlier($dtini)) {
                $msgs[] = "CAMPO DATA FINAL NÃO PODE SER ANTERIOR AO CAMPO DATA INICIAL!";
            }
        }
        $tb = $this->getTable();
        $select = $tb->select();
        $select->where("id_funcionario = '{$this->id_funcionario}'");
        $select->where("id_setor = '{$this->id_setor}'");
        $select->where("id_lotacao <> '{$this->id_lotacao}'");
        if (count($tb->fetchAll($select))) {
            $msgs[] = "FUNCIONÁRIO JÁ LOTADO NESTE SETOR!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function mostrarChefe() {
        if ($this->chefe()) {
            return "SIM";
        }
        return "NÃO";
    }
    
    public function ativo() {
        if ($this->data_final) {
            $agora = new Zend_Date();
            $data = new Zend_Date($this->data_final);
            if ($data->isLater($agora)) {
                return true;
            }
            return false;
        }
        return true;
    }
}
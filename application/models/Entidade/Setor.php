<?php
class Setor extends Escola_Entidade {
    
    public function init() {
        if (!$this->getId()) {
            $tb = new TbSetorTipo();
            $st = $tb->getPorChave("I");
            if ($st) {
                $this->id_setor_tipo = $st->getId();
            }
            $this->protocolo = "N";
        }
    }
    
    public function setFromArray(array $dados) {
        $maiuscula = new Zend_Filter_StringToUpper();
        if (isset($dados["sigla"])) { $dados["sigla"] = $maiuscula->filter($dados["sigla"]); }
        if (isset($dados["descricao"])) { $dados["descricao"] = $maiuscula->filter($dados["descricao"]); }
        parent::setFromArray($dados);
    }
    
    public function save() {
        if (!$this->id_setor_superior) {
            $this->id_setor_superior = null;
        }
        if (!$this->id_funcionario_funcao_tipo) {
            $this->id_funcionario_funcao_tipo = null;
        }
        return parent::save();
    }
    
    public function getErrors() {
        $errors = array();
        if (!trim($this->sigla)) {
            $errors[] = "CAMPO SIGLA OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->id_setor_nivel)) {
            $errors[] = "CAMPO NÍVEL OBRIGATÓRIO!";
        }
        if (!trim($this->id_setor_tipo)) {
            $errors[] = "CAMPO TIPO OBRIGATÓRIO!";
        }
        $tb = new TbSetor();
        $sql = " sigla = '{$this->sigla}' and id_setor <> '{$this->getId()}' ";
        if ($this->id_setor_superior) {
            $sql .= " and id_setor_superior = {$this->id_setor_superior} ";
        } else {
            $sql .= " and id_setor_superior is null ";
        }
        $rg = $tb->fetchAll($sql);
        if (count($rg)) {
            $errors[] = "SETOR JÁ CADASTRADO!";
        }
        $st = $this->findParentRow("TbSetorTipo");        
        $sn = $this->findParentRow("TbSetorNivel");
        if ($st && $st->interno() && $sn && $sn->eInstituicao()) {
            $rg = $tb->fetchAll(" id_setor_tipo = " . $st->getId() . " and id_setor_nivel = " . $sn->getId() . " and id_setor <> '{$this->id_setor}' ");
            if (count($rg)) {
                $errors[] = "SOMENTE PODE HAVER UM SETOR DO NÍVEL INSTITUIÇÃO DO TIPO INTERNO!";
            }
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }
    
    public function toString() {
        return $this->sigla . " - " . $this->descricao;
    }
    
    public function pegaLotacao($funcionario = false) {
        $tb = new TbLotacao();
        $sql = $tb->select();
        $sql->where(" id_setor = '" . $this->getId() . "' ");
        if ($funcionario) {
            $sql->where(" id_funcionario = '" . $funcionario->getId() . "' ");
        }
        $db = Zend_Registry::get("db");
        $rg = $this->getTable()->fetchAll($sql);
        if (count($rg)) {
            $items = array();
            foreach ($rg as $obj) {
                $items[] = $tb->getPorId($obj->id_lotacao);
            }
            return $items;
        }
        return false;
    }
    
    public function pegaLotacaoAtiva($funcionario = false) {
    	$tb = new TbLotacao();
    	$sql = $tb->select();
    	$sql->from(array("l" => "lotacao"));
    	$sql->join(array("f" => "funcionario"), "l.id_funcionario = f.id_funcionario", array());
    	$sql->join(array("pf" => "pessoa_fisica"), "f.id_pessoa_fisica = pf.id_pessoa_fisica", array());
    	$sql->where(" l.id_setor = '" . $this->getId() . "' ");
    	if ($funcionario) {
    		$sql->where(" l.id_funcionario = '" . $funcionario->getId() . "' ");
    	}
    	$tb = new TbFuncionarioSituacao();
    	$fs = $tb->getPorChave("A");
		if ($fs) {
			$sql->where("f.id_funcionario_situacao = " . $fs->getId());
		}
		$sql->order("pf.nome");
    	$rg = $this->getTable()->fetchAll($sql);
    	if (count($rg)) {
    		$tb = new TbLotacao();
    		$items = array();
    		foreach ($rg as $obj) {
    			$items[] = $tb->getPorId($obj->id_lotacao);
    		}
    		return $items;
    	}
    	return false;
    }
    
    public function getIdSuperior() {
        $ids = array();
        $ids[] = $this->getId();
        $setor = $this->findParentRow("TbSetor");
        if ($setor) {
            $ids = array_merge($ids, $setor->getIdSuperior());
        }
        return $ids;
    }
    
    public function protocolo() {
        return ($this->findParentRow("TbSetorTipo")->interno() && ($this->protocolo == "S"));
    }
    
    public function mostrarProtocolo() {
        return ($this->protocolo == "S")?"SIM":"NÃO";
    }
}
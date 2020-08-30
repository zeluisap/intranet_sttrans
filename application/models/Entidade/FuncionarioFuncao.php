<?php
class FuncionarioFuncao extends Escola_Entidade {
    
    public function setFromArray(array $dados) {
        $maiuscula = new Zend_Filter_StringToUpper();
        if (isset($dados["codigo"])) { $dados["codigo"] = $maiuscula->filter($dados["codigo"]); }
        if (isset($dados["descricao"])) { $dados["descricao"] = $maiuscula->filter($dados["descricao"]); }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $errors = array();
        if (!trim($this->codigo)) {
            $errors[] = "CAMPO CÓDIGO OBRIGATÓRIO!";
        }
        if (!trim($this->descricao)) {
            $errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->id_funcionario_funcao_tipo)) {
            $errors[] = "CAMPO TIPO OBRIGATÓRIO!";
        }
        $items = $this->getTable()->fetchAll("codigo = '{$this->codigo}' and id_funcionario_funcao <> '{$this->id_funcionario_funcao}'");
        if ($items && count($items)) {
            $errors[] = "FUNÇÃO JÁ CADASTRADA!";
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }
    
    public function toString() {
        return $this->codigo . " - " . $this->descricao;
    }
    
    public function pegaLotacao() {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("l" => "lotacao"), array("id_lotacao"));
            $sql->where("id_funcionario_funcao = " . $this->getId());
            $stmt = $db->query($sql);
            $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
            if ($rg && count($rg)) {
                return TbLotacao::pegaPorId($rg[0]->id_lotacao);
            }
        }
        return false;
    }
    
    public function pegaFuncionario() {
        $lotacao = $this->pegaLotacao();
        if ($lotacao && $lotacao->getId()) {
            return $lotacao->findParentRow("TbFuncionario");
        }
        return false;
    }
    
    public function zeraLotacao() {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $db->query("update lotacao set id_funcionario_funcao = null where id_funcionario_funcao = " . $this->getId());
        }
    }
    
    public function delete() {
        $this->zeraLotacao();
        parent::delete();
    }
}
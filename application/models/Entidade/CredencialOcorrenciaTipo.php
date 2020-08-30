<?php
class CredencialOcorrenciaTipo extends Escola_Entidade {
    
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
        if (!count($msgs)) {
            $rg = $this->getTable()->fetchAll("descricao = '{$this->descricao}' and id_credencial_ocorrencia_tipo <> '" . $this->getId() . "'");
            if ($rg && count($rg)) {
                $msgs[] = "TIPO DE OCORRÊNCIA DE CREDENCIAL JÁ CADASTRADO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function criacao() {
        return ($this->chave == "C");
    }
    
    public function deferimento() {
        return ($this->chave == "D");
    }
    
    public function indeferimento() {
        return ($this->chave == "I");
    }
    
    public function cancelamento() {
        return ($this->chave == "CA");
    }
    
    public function getDeleteErrors() {
        $erros = array();
        
        if ($this->getId()) {
            $tb = new TbCredencial();
            $sql = $tb->select("count(co.id_credencial_ocorrencia) as total");
            $sql->from(array("co" => "credencial_ocorrencia"));
            $sql->where("co.id_credencial_ocorrencia_tipo = {$this->getId()}");
            
            $objs = $tb->fetchAll($sql);
            if ($objs) {
                $obj = $objs->current();
                if ($obj->total > 0) {
                    $erros[] = "Existem Ocorrências de Credencial Vinculadas ao Tipo.";
                }
            }
        }
        
        if (count($erros)) {
            return $erros;
        }
        return false;
    }
}
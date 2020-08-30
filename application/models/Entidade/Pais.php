<?php
class Pais extends Escola_Entidade {
    
	public function toString() {
		return $this->descricao;
	}
    
	public function getErrors() {
        $msg = array();
        if (!$this->descricao) {
            $msg[] = "Campo Descrição Obrigatório!";
        }
        $tb = $this->getTable();
        $sql = $tb->select();
        $sql->where("descricao = '{$this->descricao}'");
        $sql->where("id_pais <> '{$this->getId()}'");
        $objs = $tb->fetchAll($sql);
		if ($objs && count($objs)) {
			$msg[] = "País Já Cadastrado!";
		}
		if (count($msg)) {
			return $msg;
		}
		return false;
	}
}
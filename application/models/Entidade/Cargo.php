<?php
class Cargo extends Escola_Entidade {
    public function setFromArray(array $dados) {
        $maiuscula = new Zend_Filter_StringToUpper();
        if (isset($dados["descricao"])) { $dados["descricao"] = $maiuscula->filter($dados["descricao"]); }
        parent::setFromArray($dados);
    }
    
	public function getErrors() {
		$msgs = array();
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->id_cargo_tipo)) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
        $tb = new TbCargo();
        $rg = $tb->fetchAll("descricao = '{$this->descricao}' and id_cargo <> '{$this->id_cargo}' ");
        if (count($rg)) {
            $msgs[] = "CARGO JÁ CADASTRADO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
    public function toString() {
        return $this->descricao;
    }
    
    public function pegaTbFuncionario() {
        if ($this->getId()) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("f" => "funcionario"), array("id_funcionario"));
            $sql->join(array("p" => "pessoa_fisica"), "f.id_pessoa_fisica = p.id_pessoa_fisica", array());
            $sql->where("f.id_cargo = " . $this->getId());
            $sql->order("p.nome");
            $stmt = $db->query($sql);
            if (count($stmt)) {
                $rg = $stmt->fetchAll(Zend_Db::FETCH_OBJ);
                $items = array();
                foreach ($rg as $obj) {
                    $items[] = TbFuncionario::pegaPorId($obj->id_funcionario);
                }
                return $items;
            }
        }
        return false;
    }
}
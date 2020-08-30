<?php
class TbDiaTipo extends Escola_Tabela {
	protected $_name = "dia_tipo";
	protected $_rowClass = "DiaTipo";
	protected $_dependentTables = array("TbRotaDia");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function getSql($dados = array()) {
        $sql = $this->select();
        $sql->order("chave");
        return $sql;
    }
    
    public function recuperar() {
        $dados = array("U" => "UTIL",
                       "S" => "SÁBADO",
                       "D" => "DOMINGO");
        foreach ($dados as $chave => $descricao) {
            $obj = $this->getPorChave($chave);
            if (!$obj) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
                $item->save();
            }
        }
    }
}
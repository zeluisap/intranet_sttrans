<?php
class TbRotaTipo extends Escola_Tabela {
	protected $_name = "rota_tipo";
	protected $_rowClass = "RotaTipo";
	protected $_dependentTables = array("TbRota");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
        
        public function getPorDescricao($descricao) {
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
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
            $dados = array("RA" => "RADIAL",
                           "DI" => "DIAMETRAL",
                           "CI" => "CIRCULAR");
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
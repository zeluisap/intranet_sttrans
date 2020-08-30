<?php
class TbTransportePessoaTipo extends Escola_Tabela {
	protected $_name = "transporte_pessoa_tipo";
	protected $_rowClass = "TransportePessoaTipo";
	protected $_dependentTables = array("TbTransportePessoa");
	
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
        $sql->order("descricao");
        return $sql;
    }

    public function recuperar() {
		$items = $this->listar();
		if (!$items) {
			$dados = array("PR" => "Proprietário",
						   "MO" => "Motorista",
                           "AU" => "Auxiliar");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
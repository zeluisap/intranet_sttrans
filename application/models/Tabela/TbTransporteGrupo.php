<?php
class TbTransporteGrupo extends Escola_Tabela {
	protected $_name = "transporte_grupo";
	protected $_rowClass = "TransporteGrupo";
	protected $_dependentTables = array("TbTransporte", "TbServicoTransporteGrupo");
	protected $_referenceMap = array("BancoConvenio" => array("columns" => array("id_banco_convenio"),
                                                              "refTableClass" => "TbBancoConvenio",
                                                              "refColumns" => array("id_banco_convenio")));
	
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
			$dados = array("TX" => "Taxi",
						   "OB" => "Ônibus",
                           "MT" => "Moto-Taxi");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
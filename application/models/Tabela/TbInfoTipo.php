<?php
class TbInfoTipo extends Escola_Tabela {
	protected $_name = "info_tipo";
	protected $_rowClass = "InfoTipo";
	protected $_dependentTables = array("TbInfo");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
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
			$dados = array("E" => "EVENTO",
						   "N" => "NOTÃCIA",
						   "I" => "INFORMAÃÃO",
						   "L" => "LICITAÃÃO",
						   "G" => "GALERIA");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}
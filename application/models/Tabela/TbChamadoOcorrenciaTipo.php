<?php
class TbChamadoOcorrenciaTipo extends Escola_Tabela {
	protected $_name = "chamado_ocorrencia_tipo";
	protected $_rowClass = "ChamadoOcorrenciaTipo";
	protected $_dependentTables = array("TbChamadoOcorrencia");
	
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
			$dados = array("A" => "ATENDIMENTO",
						   "T" => "TRAMITAÃÃO",
						   "F" => "FINALIZAÃÃO");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
				$item->save();
			}
		}
	}
}
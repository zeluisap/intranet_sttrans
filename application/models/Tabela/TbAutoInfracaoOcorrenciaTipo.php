<?php
class TbAutoInfracaoOcorrenciaTipo extends Escola_Tabela {
	protected $_name = "auto_infracao_ocorrencia_tipo";
	protected $_rowClass = "AutoInfracaoOcorrenciaTipo";
	protected $_dependentTables = array("TbAutoInfracaoOcorrencia");
	
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
			$dados = array("C" => "CRIAÇÃO",
						   "EN" => "ENTREGA PARA O AGENTE",
                           "DV" => "DEVOLUÇÃO",
                           "CE" => "CANCELAMENTO DE ENTREGA");
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}
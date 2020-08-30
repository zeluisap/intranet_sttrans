<?php
class TbServicoSolicitacaoOcorrenciaTipo extends Escola_Tabela {
	protected $_name = "servico_solicitacao_ocorrencia_tipo";
	protected $_rowClass = "ServicoSolicitacaoOcorrenciaTipo";
	protected $_dependentTables = array("TbServicoSolicitacaoOcorrencia");
	
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
                           "CA" => "CANCELAMENTO DA SOLICITAÇÃO",
                           "P" => "PAGAMENTO",
                           "CP" => "CANCELAMENTO DO PAGAMENTO");
            foreach ($dados as $chave => $descricao) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
                $item->save();
            }
        }
    }
}
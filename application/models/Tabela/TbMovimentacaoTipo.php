<?php
class TbMovimentacaoTipo extends Escola_Tabela {
	protected $_name = "movimentacao_tipo";
	protected $_rowClass = "MovimentacaoTipo";
	protected $_dependentTables = array("TbMovimentacao");
	
	public function getPorChave($chave) {
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss->count()) {
			return $uss->current();
		}
		return false;
	}
	
	public function listar() {
		$sql = $this->select();
		$sql->order("descricao");
		$rg = $this->fetchAll($sql);
		if (count($rg)) {
			return $rg;
		}
		return false;
	}
	
	public function recuperar() {
        $dados = array("I" => "CRIAÇÃO",
                       "E" => "ENVIO",
                       "R" => "RECEBIMENTO",
                       "A" => "ARQUIVAMENTO",
                       "C" => "CANCELAMENTO DE ARQUIVAMENTO",
                       "MO" => "MOVIMENTAÇÃO DOC ORIGINAL",
                       "V" => "VÍNCULO",
                       "T" => "TORNAR PROCESSO");
         foreach ($dados as $chave => $descricao) {
             $mt = $this->getPorChave($chave);
             if (!$mt) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
                $item->save();
             }
         }
	}
}
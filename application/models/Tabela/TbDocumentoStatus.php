<?php
class TbDocumentoStatus extends Escola_Tabela {
	protected $_name = "documento_status";
	protected $_rowClass = "DocumentoStatus";
	protected $_dependentTables = array("TbDocumento");
	
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
        $dados = array("E" => "EM TRÃMITE",
                       "R" => "AGUARDANDO RECEBIMENTO",
                       "A" => "ARQUIVADO",
                       "V" => "VINCULADO",
                       "P" => "TORNOU-SE PROCESSO");
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
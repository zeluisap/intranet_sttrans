<?php
class TbDocumentoTipoTarget extends Escola_Tabela {
	protected $_name = "documento_tipo_target";
	protected $_rowClass = "DocumentoTipoTarget";
	protected $_dependentTables = array("TbDocumentoTipo");
	
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
        $dados = array("D" => "DOCUMENTO NORMAL",
                       "A" => "DOCUMENTO ADMINISTRATIVO",
                       "P" => "DOCUMENTO PESSOAL",
                       "W" => "DOCUMENTO PORTAL");
        foreach ($dados as $chave => $descricao) {
            $dtt = $this->getPorChave($chave);
            if (!$dtt) {
                $item = $this->createRow();
                $item->setFromArray(array("chave" => $chave, "descricao" => utf8_decode($descricao)));
                $item->save();
            }
        }
	}
	
	public function pegaDocumentoAdministrativo() {
		$dtt = $this->getPorChave("A");
		if ($dtt) {
			return $dtt;
		}
		return false;
	}
}
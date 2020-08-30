<?php
class TbVinculoPessoaTipo extends Escola_Tabela {
	protected $_name = "vinculo_pessoa_tipo";
	protected $_rowClass = "VinculoPessoaTipo";
	protected $_dependentTables = array("TbVinculoPessoa");
	
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
        $dados = array("CO" => "COORDENADOR DO PROJETO",
                       "RF" => "RESPONSAVEL FINANCEIRO",
                       "RC" => "RESPONSAVEL CONTABIL",
                       "RT" => "RESPONSAVEL TECNICO");
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
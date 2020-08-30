<?php
class PessoaRef extends Escola_Entidade {
    
	public function getErrors() {
		$msgs = array();
		if (empty($this->tipo)) {
			$msgs[] = "NENHUM TIPO INFORMADO!";
		}
		if (empty($this->id_pessoa)) {
			$msgs[] = "NENHUMA PESSOA INFORMADA!";
		}
		$dados = $this->toArray();
		$tb = new TbPessoaRef();
		$select = $tb->select();
		$select->where(" tipo = '{$this->tipo}' ");
		$select->where(" chave = '{$this->chave}' ");
		$select->where(" id_pessoa = '{$this->id_pessoa}' ");
		$select->where(" id_pessoa_ref <> '{$this->id_pessoa_ref}' ");
		$prs = $tb->fetchAll($select);
		if ($prs->count()) {
			$msgs[] = "JÁ EXISTE UM REGISTRO COM ESSAS INFORMAÇÕES!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function getObjeto() {
		switch ($this->tipo) {
			case "E": $tb = new TbEndereco();
					  $ends = $tb->find($this->chave);
					  if ($ends->count()) {
						return $ends->current();
					  }
					  break;
            case "T": $telefone = TbTelefone::pegaPorId($this->chave);
                      if ($telefone) {
                          return $telefone;
                      }
					  break;
			case "F": return TbArquivo::pegaPorId($this->chave);
					  break;
		}
		return false;
	}
    
    public function telefone() {
        return ($this->tipo == "T");
    }
}
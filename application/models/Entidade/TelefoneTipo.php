<?php
class TelefoneTipo extends Escola_Entidade {
	public function setFromArray(array $dados) {
		if (isset($dados["chave"])) {
			$dados["chave"] = strtoupper($dados["chave"]);
		}
		if (isset($dados["descricao"])) {
			$dados["descricao"] = strtoupper($dados["descricao"]);
		}
		parent::setFromArray($dados);
	}
    
    public function toString() {
        return $this->descricao;
    }
}
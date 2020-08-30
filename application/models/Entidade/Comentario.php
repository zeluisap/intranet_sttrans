<?php
class Comentario extends Escola_Entidade {
	
	public function init() {
		if (!$this->getId()) {
			$tb = new TbComentarioStatus();
			$cs = $tb->getPorChave("A");
			if ($cs) {
				$this->id_comentario_status = $cs->getId();
				$this->data = date("Y-m-d");
				$this->hora = date("H:i:s");
			}
		}
	}
	
    public function setFromArray(array $dados) {
        if (isset($dados["data"])) {
			$dados["data"] = Escola_Util::montaData($dados["data"]);
		}
        parent::setFromArray($dados);
    }
    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_info) {
			$msgs[] = "CAMPO INFORMAÇÃO OBRIGATÓRIO!";
		}
		if (!$this->id_comentario_status) {
			$msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
		}
		if (!trim($this->nome)) {
			$msgs[] = "CAMPO NOME OBRIGATÓRIO!";
		}
		if (!Escola_Util::validaEmail($this->email)) {
			$msgs[] = "CAMPO E-MAIL INVÁLIDO!";
		}
		if (!trim($this->comentario)) {
			$msgs[] = "CAMPO COMENTÁRIO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function permitido() {
		return $this->findParentRow("TbComentarioStatus")->permitido();
	}
	
	public function negado() {
		return $this->findParentRow("TbComentarioStatus")->negado();
	}
	
	public function permitir() {
		if (!$this->permitido()) {
			$tb = new TbComentarioStatus();
			$cs = $tb->getPorChave("P");
			$this->id_comentario_status = $cs->getId();
			$this->save();
		}
	}
	
	public function negar() {
		if (!$this->negado()) {
			$tb = new TbComentarioStatus();
			$cs = $tb->getPorChave("N");
			$this->id_comentario_status = $cs->getId();
			$this->save();
		}
	}
}
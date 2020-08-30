<?php
class BolsistaOcorrencia extends Escola_Entidade {
    
    public function init() {
        if (!$this->getId()) {
            $this->data = date("Y-m-d");
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
		if (!trim($this->id_bolsista)) {
			$msgs[] = "CAMPOS OBRIGATÓRIOS VAZIOS!";
		}
		if (!Escola_Util::validaData($this->data)) {
			$msgs[] = "CAMPO DATA INVÁLIDO!";
		}
		if (!trim($this->descricao)) {
			$msgs[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}
		if (!trim($this->id_usuario)) {
			$msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
}
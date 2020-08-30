<?php
class ChamadoOcorrencia extends Escola_Entidade {
	
    public function init() {
		if (!$this->getId()) {
			$this->data_ocorrencia = date("Y-m-d");
			$this->hora_ocorrencia = date("H:i:s");
		}
	}
	
    public function setFromArray(array $dados) {
		if (isset($dados["data_ocorrencia"])) {
			$dados["data_ocorrencia"] = Escola_Util::montaData($dados["data_ocorrencia"]);
		}
        parent::setFromArray($dados);
    }
	    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_chamado) {
			$msgs[] = "CAMPO CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->id_chamado_ocorrencia_tipo) {
			$msgs[] = "CAMPO TIPO DE OCORRÊNCIA DE CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->id_setor) {
			$msgs[] = "CAMPO SETOR DA OCORRÊNCIA DO CHAMADO OBRIGATÓRIO!";
		}
		if (!$this->id_funcionario) {
			$msgs[] = "CAMPO FUNCIONÁRIO DA OCORRÊNCIA DO CHAMADO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}	
}
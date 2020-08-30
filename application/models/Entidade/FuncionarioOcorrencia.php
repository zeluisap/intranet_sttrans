<?php
class FuncionarioOcorrencia extends Escola_Entidade {
	
	public function init() {
		if (!$this->getId()) {
			$this->data_inicio = date("Y-m-d");
			$this->data_final = date("Y-m-d");
		}
	}
		
    public function setFromArray(array $dados) {
		if (isset($dados["data_inicio"])) {
			$dados["data_inicio"] = Escola_Util::montaData($dados["data_inicio"]);
		}
		if (isset($dados["data_final"])) {
			$dados["data_final"] = Escola_Util::montaData($dados["data_final"]);
		}
        parent::setFromArray($dados);
    }
	    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_funcionario) {
			$msgs[] = "CAMPO FUNCIONÁRIO OBRIGATÓRIO!";
		}
		if (!$this->id_funcionario_ocorrencia_tipo) {
			$msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
		}
		if (!Escola_Util::validaData($this->data_inicio)) {
			$msgs[] = "CAMPO DATA DE INÍCIO INVÁLIDO!";
		}
		if (!Escola_Util::validaData($this->data_final)) {
			$msgs[] = "CAMPO DATA FINAL INVÁLIDO!";
		}
		$inicio = new Zend_Date($this->data_inicio);
		$final = new Zend_Date($this->data_final);
		if ($final->isEarlier($inicio)) {
			$msgs[] = "CAMPO DATA FINAL NÃO PODE SER ANTERIOR A DATA INÍCIAL!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}	
}
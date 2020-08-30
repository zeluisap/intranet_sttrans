<?php
class Telefone extends Escola_Entidade {
    
    public function setFromArray(array $data) {
        if (isset($data["numero"])) {
            $data["numero"] = Escola_Util::limpaNumero($data["numero"]);
        }
        parent::setFromArray($data);
    }
    
	public function setFormatado($value) {
		$dados = explode("( ", $value);
		if (count($dados) > 1) {
			$value = $dados[1];
			$dados = explode(" )", $value);
			if (count($dados) > 1) {
				$this->ddd = $dados[0];
				$filter = new Zend_Filter_Digits();
				$this->numero = $filter->filter($dados[1]);
			}
		}
	}
	
	public function getErrors() {
		$msgs = array();
		if (empty($this->id_telefone_tipo)) {
			$msgs[] = "CAMPO TIPO DE TELEFONE OBRIGATÓRIO!";
		}
		if (empty($this->ddd)) {
			$msgs[] = "CAMPO DDD OBRIGATÓRIO!";
		}
		if (empty($this->numero)) {
			$msgs[] = "CAMPO NÚMERO OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
	
	public function toString() {
		return "( " . $this->ddd . " ) " . preg_replace('/(\d{4})(\d{4})/i', '$1-$2', $this->numero);
	}
    
    public function delete() {
        $tb = new TbPessoaRef();
        $rs = $tb->listar(array("tipo" => "T", "chave" => $this->getId()));
        if ($rs && count($rs)) {
            foreach ($rs as $obj) {
                $obj->delete();
            }
        }
        parent::delete();
    }
}
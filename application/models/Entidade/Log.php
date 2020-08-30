<?php
class Log extends Escola_Entidade {
	public function save($val = true) {
		$save = parent::save();
		if ($val) {
			$this->validador = $this->calculaValidador();
			$this->save(false);
		}
		return $save;
	}
	
	public function calculaValidador() {
		$dados = array("id_log" => $this->id_log,
					   "ip" => $this->ip,
					   "cpf" => $this->cpf,
					   "tabela" => $this->tabela,
					   "id_log_operacao" => $this->id_log_operacao,
					   "data" => $this->data,
					   "campos" => array());
		$rows = $this->findDependentRowSet("TbLogCampo");
		if (count($rows)) {
			foreach ($rows as $row) {
				$dados["campos"][] = $row->serialize();
			}
		}
		$ser = serialize($dados);
		return md5($ser);
	}
	
	public function validar() {
		$val = $this->calculaValidador();
		return ($this->validador == $val);
	}
	
	public function pegaCampos() {
		if ($this->getId()) {
			$tb = new TbLogCampo();
			$items = $tb->fetchAll("id_log = " . $this->getId());
			if ($items && count($items)) {
				return $items;
			}
		}
		return false;
	}
}
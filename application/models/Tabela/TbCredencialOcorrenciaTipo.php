<?php
class TbCredencialOcorrenciaTipo extends Escola_Tabela
{
	protected $_name = "credencial_ocorrencia_tipo";
	protected $_rowClass = "CredencialOcorrenciaTipo";
	protected $_dependentTables = array("TbCredencialOcorrencia");

	public function getSql($dados = array())
	{
		$sql = $this->select();
		$sql->order("descricao");
		return $sql;
	}

	public function getPorChave($chave)
	{
		$uss = $this->fetchAll(" chave = '{$chave}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function getPorDescricao($descricao)
	{
		$uss = $this->fetchAll(" descricao = '{$descricao}' ");
		if ($uss && count($uss)) {
			return $uss->current();
		}
		return false;
	}

	public function recuperar()
	{
		$items = $this->listar();
		if (!$items) {
			$dados = array(
				"C" => "CRIAÇÃO",
				"D" => "DEFERIMENTO",
				"I" => "INDEFERIMENTO",
				"CA" => "CANCELAMENTO DE ANÁLISE"
			);
			foreach ($dados as $chave => $descricao) {
				$item = $this->createRow();
				$item->setFromArray(array("chave" => $chave, "descricao" => $descricao));
				$item->save();
			}
		}
	}
}

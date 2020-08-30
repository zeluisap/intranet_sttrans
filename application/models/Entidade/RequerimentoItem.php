<?php
class RequerimentoItem extends Escola_Entidade
{

	public function getErrors()
	{
		$errors = array();

		if (!$this->id_requerimento) {
			$errors[] = "CAMPO REQUERIMENTO OBRIGATÓRIO!";
		}

		if (!$this->id_servico && !trim($this->descricao)) {
			$errors[] = "CAMPO(S) SERVIÇO OU DESCRIÇÃO OBRIGATÓRIO(S)!";
		}

		if (count($errors)) {
			return $errors;
		}

		$sql = $this->getTable()->select();

		$sql->where("id_requerimento = ?", $this->id_requerimento);
		if ($this->id_servico) {
			$sql->where("id_servico = ?", $this->id_servico);
		} else if ($this->descricao) {
			$sql->where("lower(descricao) = lower(?)", $this->descricao);
		}

		$id = $this->getId();
		if (!$id) {
			$id = 0;
		}

		$sql->where("id_requerimento_item <> ?", $id);

		$rg = $this->getTable()->fetchAll($sql);
		if (count($rg)) {
			$errors[] = "Ítem já cadastrado para esse requerimento!";
		}

		if (count($errors)) {
			return $errors;
		}

		return false;
	}

	public function getServico()
	{
		if (!$this->id_servico) {
			return $this->servico;
		}

		$servico = TbServico::pegaPorId($this->id_servico);
		if (!$servico) {
			return null;
		}

		return $servico->toArray();
	}

	public function getServicoDescricao()
	{
		if (!$this->id_servico) {
			return $this->servico;
		}

		$servico = TbServico::pegaPorId($this->id_servico);
		if (!$servico) {
			return '';
		}

		return $servico->descricao;
	}

	public function toArray()
	{
		$array = array_merge(parent::toArray(), [
			"servico" => $this->getServico(),
		]);

		$array["descricao"] = Escola_Util::valorOuCoalesce($array, "servico->descricao", Escola_Util::valorOuNulo($array, "servico"));
		$array["pendente"] = $this->pendente();
		$array["deferido"] = $this->deferido();
		$array["indeferido"] = $this->indeferido();

		return $array;
	}

	/**
	 * @return Requerimento
	 */
	public function getRequerimento()
	{
		return $this->findParentRow("TbRequerimento");
	}

	public function pendente()
	{
		if (!$this->situacao) {
			return true;
		}
		return $this->situacao == Requerimento::$SITUACAO_PENDENTE;
	}

	public function deferido()
	{
		return $this->situacao == Requerimento::$SITUACAO_DEFERIDO;
	}

	public function indeferido()
	{
		return $this->situacao == Requerimento::$SITUACAO_INDEFERIDO;
	}
}

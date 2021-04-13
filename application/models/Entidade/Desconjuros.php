<?php
class Desconjuros extends Escola_Entidade
{

	public function setFromArray(array $dados)
	{
		if (isset($dados["ativo"]) && !is_bool($dados["ativo"])) {
			$dados["ativo"] = (strtolower($dados["ativo"]) == "s");
			if (!$dados["ativo"]) {
				$dados["ativo"] = 0;
			}
		}

		parent::setFromArray($dados);
	}

	public function getErrors()
	{
		$errors = array();

		if (!trim($this->tipo)) {
			$errors[] = "CAMPO TIPO OBRIGATÓRIO!";
		} else {
			$tipo = strtolower(trim($this->tipo));
			if (!in_array($tipo, ["descontos", "juros", "multa"])) {
				$errors[] = "CAMPO TIPO INVÁLIDO!";
			}
		}

		if (!trim($this->nome_classe)) {
			$errors[] = "CAMPO NOME DA CLASSE OBRIGATÓRIO!";
		} elseif (!$this->validaNomeClasse()) {
			$errors[] = "CLASSE ESPECIFICADA NÃO DISPONÍVEL!";
		}

		if (!trim($this->descricao)) {
			$errors[] = "CAMPO DESCRIÇÃO OBRIGATÓRIO!";
		}

		$sql = $this->getTable()->select();
		$sql->where("lower(nome_classe) = lower(?)", $this->nome_classe);
		$sql->where("id_desconjuros <> ?", $this->getId());
		$rg = $this->getTable()->fetchAll($sql);

		if (count($rg)) {
			$errors[] = "CLASSE [" . $this->nome_classe . "] JÁ CADASTRADA!";
		}

		if (count($errors)) {
			return $errors;
		}

		return false;
	}

	private function getFullClassName()
	{
		if (!$this->nome_classe) {
			return null;
		}

		return implode("_", [
			"Escola",
			"Desconjuros",
			$this->nome_classe
		]);
	}

	private function validaNomeClasse()
	{
		$fullClassName = $this->getFullClassName();
		if (!$fullClassName) {
			return null;
		}
		return class_exists($fullClassName);
	}

	public function getObjeto()
	{
		$fullClassName = $this->getFullClassName();
		if (!$fullClassName) {
			return null;
		}
		return new $fullClassName();
	}

	public function calcular($ss)
	{
		$objeto = $this->getObjeto();
		if (!$objeto) {
			return null;
		}
		return $objeto->calcular($ss);
	}
}

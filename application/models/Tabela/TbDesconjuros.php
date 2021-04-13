<?php
class TbDesconjuros extends Escola_Tabela
{
	protected $_name = "desconjuros";
	protected $_rowClass = "Desconjuros";
	protected $_referenceMap = array("Boleto" => array(
		"columns" => array("id_boleto"),
		"refTableClass" => "TbBoleto",
		"refColumns" => array("id_boleto")
	));

	public static function carregaClassesDaPasta()
	{

		$path = implode(DIRECTORY_SEPARATOR, [
			ROOT_DIR,
			"lib", "Escola", "Desconjuros", "*.php"
		]);
		$files = glob($path);

		if (!count($files)) {
			return;
		}

		foreach ($files as $file) {
			$info = pathinfo($file);
			$filename = Escola_Util::valorOuNulo($info, "filename");

			if (!$filename) {
				continue;
			}

			$nome_classe = implode("_", [
				"Escola", "Desconjuros", $filename
			]);

			if (!class_exists($nome_classe)) {
				continue;
			}

			$obj = new $nome_classe();

			$dados = [
				"tipo" => $obj->getTipo(),
				"nome_classe" => $filename,
				"descricao" => $obj->getDescricao(),
				"ativo" => true
			];

			$tbclass = get_class();
			$tb = new $tbclass();
			$obj = $tb->createRow($dados);
			$errors = $obj->getErrors();

			if ($errors && is_array($errors) && count($errors)) {
				continue;
			}

			$obj->save();
		}
	}

	public static function calcular($ss)
	{
		if (!$ss) {
			return null;
		}

		if (!$ss->aguardando_pagamento()) {
			return null;
		}

		if (!$ss->aposVencimento()) {
			return null;
		}

		$tb = new TbDesconjuros();
		$sql = $tb->select();
		$sql->from(["d" => "desconjuros"]);
		$sql->where("d.ativo = ?", true);

		$djs = $tb->fetchAll($sql);
		if (!($djs->count())) {
			return null;
		}

		$retorno = [];
		foreach ($djs as $dj) {
			$retorno[] = $dj->calcular($ss);
		}

		$retorno = array_filter($retorno, function ($item) {
			return Escola_Util::valorOuNulo($item, "valor");
		});

		return $retorno;
	}

	public static function calcularBoleto($boleto)
	{
		if (!$boleto) {
			return null;
		}

		return [
			"juros" => 0,
			"multas" => 0,
			"desconto" => 0
		];
	}

	public static function calcularGrupos($ss)
	{
		$desconjuros = self::calcular($ss);
		if (!($desconjuros && count($desconjuros))) {
			return null;
		}
		$retorno = [];
		foreach ($desconjuros as $desconjuro) {
			$tipo = Escola_Util::valorOuNulo($desconjuro, "tipo");
			$valor = Escola_Util::valorOuNulo($desconjuro, "valor");
			if (!($tipo && $valor)) {
				continue;
			}

			if (!isset($retorno[$tipo])) {
				$retorno[$tipo] = 0;
			}

			$retorno[$tipo] += $valor;
		}

		if (!count($retorno)) {
			return null;
		}

		return $retorno;
	}
}

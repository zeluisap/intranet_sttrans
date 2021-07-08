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

		$isPeriodico = $ss->isPeriodico();
		$aposVencimento = $ss->aposVencimento();

		if (!$isPeriodico && $aposVencimento) {
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


	public static function pegaUltimoServicoPago($ss)
	{
		return Escola_DbUtil::first("
            select ss.*
            from servico_solicitacao ss 
                left outer join servico_solicitacao_status sss on ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status
				left outer join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
            where (stg.id_servico_transporte_grupo = :id_stg)
            and (lower(sss.chave) = 'pg')
			and (stg.id_periodicidade > 0)
			and (ss.id_transporte = :id_transporte)
			and (ss.tipo = :tipo)
			and (ss.chave = :chave)
			order by ss.data_validade desc
        ", [
			"id_stg" => $ss->id_servico_transporte_grupo,
			"id_transporte" => $ss->id_transporte,
			"tipo" => $ss->tipo,
			"chave" => $ss->chave
		]);
	}

	public static function pegaDataVencimento($ss)
	{
		if (!$ss) {
			return null;
		}

		$ultimo = self::pegaUltimoServicoPago($ss);
		if ($ultimo) {
			return $ultimo->data_validade;
		}

		$dataVencimento = $ss->data_vencimento;
		if ($dataVencimento) {
			return $dataVencimento;
		}

		return null;
	}

	private static function pegaPorTipo($ss, $tipo)
	{
		if (!$ss) {
			return 0;
		}
		$desconjuros = self::calcular($ss);
		if (!Escola_Util::isResultado($desconjuros)) {
			return 0;
		}

		$total = 0;
		foreach ($desconjuros as $desconj) {
			$descTipo = Escola_Util::valorOuNulo($desconj, "tipo");
			if ($descTipo != $tipo) {
				continue;
			}
			$valor = Escola_Util::valorOuNulo($desconj, "valor");
			if (!$valor) {
				continue;
			}
			$total += $valor;
		}
		return $total;
	}

	public static function pegaJuros($ss)
	{
		return self::pegaPorTipo($ss, "juros");
	}

	public static function pegaMultas($ss)
	{
		return self::pegaPorTipo($ss, "multa");
	}
}

<?php
class Requerimento extends Escola_Entidade
{

	public static $SITUACAO_PENDENTE     = 'PENDENTE';
	public static $SITUACAO_DEFERIDO     = 'DEFERIDO';
	public static $SITUACAO_DEFERIDO_PARCIALMENTE = 'DEFERIDO PARCIALMENTE';
	public static $SITUACAO_INDEFERIDO   = 'INDEFERIDO';

	public function getErrors()
	{
		$errors = array();
		if (!$this->id_pessoa) {
			$errors[] = "CAMPO PESSOA OBRIGATÓRIO!";
		}

		if (count($errors)) {
			return $errors;
		}

		return false;
	}

	public function save()
	{

		if (!$this->situacao) {
			$this->situacao = self::$SITUACAO_PENDENTE;
		}

		if (!$this->data_criacao) {
			$this->data_criacao = date("Y-m-d");
		}

		if (!$this->hora_criacao) {
			$this->hora_criacao = date("H:i:s");
		}

		$this->geraAnoNumero();
		return parent::save();
	}

	private function geraAnoNumero()
	{
		if (!$this->ano) {
			$this->ano = date("Y");
		}

		$db = Zend_Registry::get("db");
		$sql = $db->select();
		$sql->from(array("requerimento"), array("maximo" => "max(numero)"));
		$sql->where("ano = :ano");

		$stmt = $db->query($sql, [
			"ano" => $this->ano
		]);

		if (!($stmt && $stmt->rowCount())) {
			$this->numero = 1;
		}

		$obj = $stmt->fetch(Zend_db::FETCH_OBJ);

		$this->numero = $obj->maximo + 1;
	}

	function toArray()
	{

		$parent = parent::toArray();

		$p = null;
		$pessoa = $this->getPessoa();
		if ($pessoa) {
			$p = $pessoa->toArray();
			$filho = $pessoa->pegaPessoaFilho();
			if ($filho) {
				$p = array_merge($p, $filho->toArray());
			}
		}

		$i = null;
		$itens = $this->getItens();
		if ($itens && is_array($itens) && count($itens)) {
			$i = array_map(function ($item) {
				return $item->toArray();
			}, $itens);
		}

		return array_merge($parent, [
			"pessoa" => $p,
			"itens" => $i
		]);
	}

	public function getItens()
	{
		return TbRequerimentoItem::listarPorRequerimento($this);
	}

	function toView()
	{
		$id = $this->id_requerimento;
		if (!$id) {
			$id = 0;
		}

		return [
			"id" => $id,
			"numero" => $this->mostrarNumero(),
			"pessoa" => $this->mostrarPessoa(),
			"data_criacao" => $this->mostrarDataCriacao(),
			"hora_criacao" => $this->hora_criacao,
			"criacao" => $this->mostrarCriacao(),
			"situacao" => $this->situacao,
		];
	}

	public function mostrarCriacao()
	{
		try {
			$data_hora = new DateTime($this->data_criacao . " " . $this->hora_criacao);
			return $data_hora->format("d/m/Y H:i");
		} catch (Exception $ex) {
			return '';
		}
	}

	public function mostrarDataCriacao()
	{
		try {
			$dh = new DateTime($this->data_criacao);
			return $dh->format("d/m/Y");
		} catch (Exception $ex) {
			return "--";
		}
	}

	public function mostrarNumero()
	{
		if (!($this->ano && $this->numero)) {
			return "--";
		}

		return Escola_Util::zero($this->numero, "4") . " / " . $this->ano;
	}

	public function mostrarPessoa()
	{
		$pessoa = $this->getPessoa();
		if (!$pessoa) {
			return "--";
		}
		return $pessoa->toString();
	}

	private function getPessoa()
	{
		if (!$this->id_pessoa) {
			return null;
		}

		$pessoa = TbPessoa::pegaPorId($this->id_pessoa);
		if (!$pessoa) {
			return null;
		}

		return $pessoa;
	}

	public function pendente()
	{
		return $this->situacao == self::$SITUACAO_PENDENTE;
	}

	public function deferido()
	{
		return $this->situacao == self::$SITUACAO_DEFERIDO;
	}

	public function indeferido()
	{
		return $this->situacao == self::$SITUACAO_INDEFERIDO;
	}

	public function deferido_parcialmente()
	{
		return $this->situacao == self::$SITUACAO_DEFERIDO_PARCIALMENTE;
	}

	function finalizarAnalise()
	{

		$usuario = TbUsuario::pegaLogado();
		if (!$usuario) {
			throw new Error("Usuário logado não identificado.");
		}

		if (!$this->pendente()) {
			throw new Error("Requerimento já analisado.");
		}

		$itens = $this->getItens();
		if ($itens) {
			$deferidos = 0;
			$indeferidos = 0;
			foreach ($itens as $item) {
				if ($item->pendente()) {
					throw new Error("Nem todos os ítens do requerimento foram analisados, analise todos antes de efetuar a finalização.");
				}

				if ($item->deferido()) {
					$deferidos++;
					continue;
				}

				if ($item->indeferido()) {
					$indeferidos++;
					continue;
				}
			}
		}

		if ($deferidos && $indeferidos) {
			$this->situacao = self::$SITUACAO_DEFERIDO_PARCIALMENTE;
		} elseif ($deferidos && !$indeferidos) {
			$this->situacao = self::$SITUACAO_DEFERIDO;
		} else {
			$this->situacao = self::$SITUACAO_INDEFERIDO;
		}

		$this->data_analise = date("Y-m-d");
		$this->hora_analise = date("H:i:s");
		$this->analise_usuario_id = $usuario->getId();

		$this->save();
	}

	public function getUsuarioAnalise()
	{
		if ($this->pendente()) {
			return null;
		}

		if (!$this->analise_usuario_id) {
			return null;
		}

		$usuario = TbUsuario::pegaPorId($this->analise_usuario_id);
		if (!$usuario) {
			return null;
		}

		return $usuario;
	}

	public function mostrarUsuarioAnalise()
	{
		$usuario = $this->getUsuarioAnalise();
		if (!$usuario) {
			return '';
		}
		return $usuario->toString();
	}

	public function mostrarDataHoraAnalise()
	{
		if ($this->pendente()) {
			return '';
		}

		try {
			$dt = new DateTime($this->data_analise . " " . $this->hora_analise);
			return $dt->format("d/m/Y H:i");
		} catch (Exception $ex) {
			return '';
		}
	}

	public function delete()
	{

		if (!$this->pendente()) {
			throw new Escola_Exception("Somente requerimentos pendentes podem ser excluídos.");
		}

		$itens = $this->getItens();

		foreach ($itens as $item) {
			$item->delete();
		}

		parent::delete();
	}
}

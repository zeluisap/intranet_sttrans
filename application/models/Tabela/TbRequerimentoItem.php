<?php
class TbRequerimentoItem extends Escola_Tabela
{
	protected $_name = "requerimento_item";
	protected $_rowClass = "RequerimentoItem";
	protected $_referenceMap = array("Requerimento" => array(
		"columns" => array("id_requerimento"),
		"refTableClass" => "TbRequerimento",
		"refColumns" => array("id_requerimento")
	));

	/**
	 * @return RequerimentoItem
	 */
	public static function pegaPorId($id)
	{
		return parent::pegaPorId($id);
	}

	public function salvarServicoAvulso($requerimento, $params)
	{
		if (Escola_Util::valorOuNulo($params, "id_servico")) {
			return;
		}

		$id = Escola_Util::valorOuNulo($params, "id");

		if (!$descricao = Escola_Util::valorOuNulo($params, "descricao")) {
			throw new Escola_Exception("Campo descrição não identificado em ítem de requerimento avulso.");
		}

		if (!$req_id = $requerimento->id_requerimento) {
			return;
		}

		$sql = $this->select();
		$sql->where("id_requerimento = ?", $req_id);
		$sql->where("lower(servico) = lower(?)", $descricao);


		$objs = $this->fetchAll($sql);
		if ($objs && is_array($objs) && count($objs)) {
			return;
		}

		$req_item = null;
		if ($id) {
			$req_item = TbRequerimentoItem::pegaPorId($id);
		}
		if (!$req_item) {
			$req_item = $this->createRow();
		}

		$req_item->id_requerimento = $req_id;
		$req_item->servico = $descricao;
		$req_item->obs = Escola_Util::valorOuNulo($params, "obs");

		$req_item->save();

		return $req_item;
	}

	public function salvarServico($requerimento, $params)
	{
		if (!$id_servico = Escola_Util::valorOuNulo($params, "id_servico")) {
			return;
		}

		$id = Escola_Util::valorOuNulo($params, "id");

		if (!$req_id = $requerimento->id_requerimento) {
			return;
		}

		$sql = $this->select();
		$sql->where("id_requerimento = ?", $req_id);
		$sql->where("id_servico = ?", $id_servico);

		$objs = $this->fetchAll($sql);
		if ($objs && is_array($objs) && count($objs)) {
			return;
		}

		$req_item = null;
		if ($id) {
			$req_item = TbRequerimentoItem::pegaPorId($id);
		}
		if (!$req_item) {
			$req_item = $this->createRow();
		}

		$req_item = $this->createRow();
		$req_item->id_requerimento = $req_id;
		$req_item->id_servico = $id_servico;

		$req_item->save();

		return $req_item;
	}

	public static function listarPorRequerimento($req)
	{
		if (!$req) {
			return null;
		}

		$id = $req->getId();
		if (!$id) {
			return null;
		}

		$tb = new TbRequerimentoItem();

		$sql = $tb->select();
		$sql->from(["ri" => "requerimento_item"]);
		$sql->joinLeft(["s" => "servico"], "ri.id_servico = s.id_servico", []);
		$sql->where("ri.id_requerimento = ?", $id);
		$sql->order("ri.servico");
		$sql->order("s.descricao");

		$txt = "" . $sql;

		$objs = $tb->fetchAll($sql);

		if (!($objs && $objs->count())) {
			return null;
		}

		$itens = [];
		foreach ($objs as $obj) {
			$itens[] = $obj;
		}

		usort($itens, function ($a, $b) {
			$servico_a = $a->getServicoDescricao();
			$servico_b = $b->getServicoDescricao();

			if ($servico_a < $servico_b) {
				return -1;
			} elseif ($servico_a > $servico_b) {
				return 1;
			}

			return 0;
		});

		return $itens;
	}
}

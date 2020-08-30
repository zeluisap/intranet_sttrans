<?php
class TbPrevisao extends Escola_Tabela {
	protected $_name = "previsao";
	protected $_rowClass = "Previsao";
	protected $_referenceMap = array("Vinculo" => array("columns" => array("id_vinculo"),
												   "refTableClass" => "TbVinculo",
												   "refColumns" => array("id_vinculo")),
                                     "Valor" => array("columns" => array("id_valor"),
												   "refTableClass" => "TbValor",
												   "refColumns" => array("id_valor")),
                                     "BolsaTipo" => array("columns" => array("id_bolsa_tipo"),
												   "refTableClass" => "TbBolsaTipo",
												   "refColumns" => array("id_bolsa_tipo")),
                                     "PrevisaoTipo" => array("columns" => array("id_previsao_tipo"),
												   "refTableClass" => "TbPrevisaoTipo",
												   "refColumns" => array("id_previsao_tipo")));
	
    public function getSql($dados = array()) {
        $sql = $this->select();
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["id_bolsa_tipo"]) && $dados["id_bolsa_tipo"]) {
            $sql->where("id_bolsa_tipo = {$dados["id_bolsa_tipo"]}");
        }
        if (isset($dados["id_previsao_tipo"]) && $dados["id_previsao_tipo"]) {
            $sql->where("id_previsao_tipo = '{$dados["id_previsao_tipo"]}'");
        }
        if (isset($dados["ano"]) && $dados["ano"]) {
            $sql->where("ano = '{$dados["ano"]}'");
        }
        if (isset($dados["mes"]) && $dados["mes"]) {
            $sql->where("mes = '{$dados["mes"]}'");
        }
        if (isset($dados["filtro_id_vinculo"]) && $dados["filtro_id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["filtro_id_vinculo"]}");
        }
        if (isset($dados["filtro_id_bolsa_tipo"]) && $dados["filtro_id_bolsa_tipo"]) {
            $sql->where("id_bolsa_tipo = {$dados["filtro_id_bolsa_tipo"]}");
        }
        if (isset($dados["filtro_id_previsao_tipo"]) && $dados["filtro_id_previsao_tipo"]) {
            $sql->where("id_previsao_tipo = '{$dados["filtro_id_previsao_tipo"]}'");
        }
        if (isset($dados["filtro_ano"]) && $dados["filtro_ano"]) {
            $sql->where("ano = '{$dados["filtro_ano"]}'");
        }
        if (isset($dados["filtro_mes"]) && $dados["filtro_mes"]) {
            $sql->where("mes = '{$dados["filtro_mes"]}'");
        }
        $sql->order("id_vinculo");
		$sql->order("ano"); 
        $sql->order("mes"); 
        $sql->order("id_previsao_tipo");
        $sql->order("id_bolsa_tipo");
        return $sql;
    }
    
    public function pega_valor_total($dados = array()) {
        $db = Zend_Registry::get("db");
        $sql = $this->select();
        $sql->from(array("p" => "previsao"), array("p.id_previsao_tipo", "p.id_bolsa_tipo", "valor_total" => "sum(v.valor)"));
        $sql->join(array("v" => "valor"), "p.id_valor = v.id_valor", array());
        if (isset($dados["id_vinculo"]) && $dados["id_vinculo"]) {
            $sql->where("id_vinculo = {$dados["id_vinculo"]}");
        }
        if (isset($dados["ano"]) && $dados["ano"]) {
            $sql->where("p.ano = {$dados["ano"]}");
        }
        if (isset($dados["mes"]) && $dados["mes"]) {
            $sql->where("p.mes = {$dados["mes"]}");
        }
        $sql->group("p.id_previsao_tipo");
        $sql->group("p.id_bolsa_tipo");
        $stmt = $db->query($sql);
        if ($stmt && $stmt->rowCount()) {
            $valor_total = 0;
            while ($obj = $stmt->fetch(Zend_Db::FETCH_OBJ)) {
                $pt = TbPrevisaoTipo::pegaPorId($obj->id_previsao_tipo);
                if ($pt) {
                    if ($pt->bolsista()) {
                        $bt = TbBolsaTipo::pegaPorId($obj->id_bolsa_tipo);
                        $valor_total = $valor_total + ($bt->pega_valor()->valor * $obj->valor_total);
                    } else {
                        $valor_total += $obj->valor_total;
                    }
                }
            }
            return $valor_total;
        }
        return false;
    }
}
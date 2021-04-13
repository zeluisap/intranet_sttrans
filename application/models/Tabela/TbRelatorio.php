<?php
class TbRelatorio extends Escola_Tabela
{
    protected $_name = "relatorio";
    protected $_rowClass = "Relatorio";
    protected $_referenceMap = array("RelatorioTipo" => array(
        "columns" => array("id_relatorio_tipo"),
        "refTableClass" => "TbRelatorioTipo",
        "refColumns" => array("id_relatorio_tipo")
    ));

    public function getInstance($id_relatorio)
    {
        $relatorio = TbRelatorio::pegaPorId($id_relatorio);
        if (!$relatorio) {
            return false;
        }

        $class_name = "Escola_Relatorio_Default";
        $rt = $relatorio->findParentRow("TbRelatorioTipo");

        if ($rt) {
            $class_name .= "_{$rt->chave}";
            $class_name .= "_{$relatorio->chave}";
        }

        try {
            Zend_Loader::loadClass($class_name);
            $obj = new $class_name;
            $obj->set_relatorio($relatorio);
            return $obj;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getSql($dados = array())
    {
        $sql = $this->select();
        $sql->from(array("r" => "relatorio"));
        if (isset($dados["tipos"]) && $dados["tipos"]) {
            $sql->join(array("rt" => "relatorio_tipo"), "r.id_relatorio_tipo = rt.id_relatorio_tipo", array());
            $sql->where("rt.chave in ('" . implode("','", $dados["tipos"]) . "')");
        }
        if (isset($dados["filtro_id_relatorio_tipo"]) && $dados["filtro_id_relatorio_tipo"]) {
            $sql->where("id_relatorio_tipo = {$dados["filtro_id_relatorio_tipo"]}");
        }
        $sql->order("r.descricao");
        return $sql;
    }

    public function getPorChave($chave)
    {
        $rs = $this->fetchAll("chave = '{$chave}'");
        if ($rs && count($rs)) {
            return $rs->current();
        }
        return false;
    }
}

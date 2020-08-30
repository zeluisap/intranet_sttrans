<?php
class TbMotorista extends Escola_Tabela
{
    protected $_name = "motorista";
    protected $_rowClass = "Motorista";
    protected $_referenceMap = array(
        "PessoaMotorista" => array(
            "columns" => array("id_pessoa_motorista"),
            "refTableClass" => "TbPessoaMotorista",
            "refColumns" => array("id_pessoa_motorista")
        ),
        "TransporteGrupo" => array(
            "columns" => array("id_transporte_grupo"),
            "refTableClass" => "TbTransporteGrupo",
            "refColumns" => array("id_transporte_grupo")
        )
    );

    public function getSql($dados = array())
    {
        $sql = $this->select();
        $sql->from(array("m" => "motorista"));
        $sql->join(array("pm" => "pessoa_motorista"), "m.id_pessoa_motorista = pm.id_pessoa_motorista", array());
        $sql->join(array("pf" => "pessoa_fisica"), "pf.id_pessoa_fisica = pm.id_pessoa_fisica", array());
        if (isset($dados["filtro_id_transporte_grupo"]) && $dados["filtro_id_transporte_grupo"]) {
            $sql->where("m.id_transporte_grupo = {$dados["filtro_id_transporte_grupo"]} ");
        }
        if (isset($dados["id_transporte_grupo"]) && $dados["id_transporte_grupo"]) {
            $sql->where("m.id_transporte_grupo = {$dados["id_transporte_grupo"]} ");
        }
        if (isset($dados["filtro_matricula"]) && $dados["filtro_matricula"]) {
            $sql->where("m.matricula = '{$dados["filtro_matricula"]}'");
        }
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $dados["filtro_cpf"] = Escola_Util::limparNumero($dados["filtro_cpf"]);
            $sql->where("pf.cpf = '{$dados["filtro_cpf"]}'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $sql->where("pf.nome like '%{$dados["filtro_nome"]}%' ");
        }
        if (isset($dados["filtro_id_cnh_categoria"]) && $dados["filtro_id_cnh_categoria"]) {
            $sql->where("pm.id_cnh_categoria = {$dados["filtro_id_cnh_categoria"]} ");
        }
        if (isset($dados["filtro_id_pessoa_motorista"]) && $dados["filtro_id_pessoa_motorista"]) {
            $sql->where("pm.id_pessoa_motorista = '{$dados["filtro_id_pessoa_motorista"]}'");
        }
        $sql->order("m.id_transporte_grupo");
        if (isset($dados["ordem"]) && $dados["ordem"]) {
            $sql->order("{$dados["ordem"]}");
        }
        return $sql;
    }

    public function pegaProximaMatricula($id_transporte_grupo)
    {
        if ($id_transporte_grupo) {
            $db = Zend_Registry::get("db");
            $sql = $db->select();
            $sql->from(array("m" => "motorista"), array("ultima" => "max(matricula)"));
            $sql->where("id_transporte_grupo = {$id_transporte_grupo}");
            $stmt = $db->query($sql);
            if ($stmt && $stmt->rowCount()) {
                $obj = $stmt->fetch(Zend_Db::FETCH_OBJ);
                return $obj->ultima + 1;
            }
        }
        return 1;
    }
}

<?php

class TbBoleto extends Escola_Tabela
{

    protected $_name = "boleto";
    protected $_rowClass = "Boleto";
    protected $_dependentTables = array("TbBoletoItem", "TbRetornoItem");
    protected $_referenceMap = array(
        "Pessoa" => array(
            "columns" => array("id_pessoa"),
            "refTableClass" => "TbPessoa",
            "refColumns" => array("id_pessoa")
        ),
        "BancoConvenio" => array(
            "columns" => array("id_banco_convenio"),
            "refTableClass" => "TbBancoConvenio",
            "refColumns" => array("id_banco_convenio")
        )
    );

    public function getSql($dados = array())
    {
        $sql = $this->select();
        $sql->from(array("b" => "boleto"));
        if (isset($dados["filtro_id_boleto"]) && $dados["filtro_id_boleto"]) {
            $sql->where("b.id_boleto = {$dados["filtro_id_boleto"]}");
        }
        if (isset($dados["filtro_nosso_numero"]) && $dados["filtro_nosso_numero"]) {
            $sql->where("lower(b.nosso_numero) = lower('{$dados["filtro_nosso_numero"]}')");
        }
        if (isset($dados["filtro_convenio"]) && $dados["filtro_convenio"]) {
            $sql->join(array("bc" => "banco_convenio"), "b.id_banco_convenio = bc.id_banco_convenio", array());
            $sql->where("bc.convenio = '{$dados["filtro_convenio"]}'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $db = Zend_Registry::get("db");
            $sql_pf = $db->select();
            $sql_pf->from(array("pf" => "pessoa_fisica"), array("id_pessoa"));
            $sql_pf->where("pf.nome like '%{$dados["filtro_nome"]}%'");
            $sql_pj = $db->select();
            $sql_pj->from(array("pj" => "pessoa_juridica"), array("id_pessoa"));
            $sql_pj->where("pj.razao_social like '%{$dados["filtro_nome"]}%'");
            $sql->Where("(b.id_pessoa in ({$sql_pf}) or b.id_pessoa in ({$sql_pj}))");
        }
        $sql->order("b.data_criacao desc");
        $sql->order("b.id_boleto desc");
        return $sql;
    }

    public function criaBoleto($sss, $id_pessoa, $data_vencimento_default = false, $correcao = 0)
    {
        if (!$sss) {
            return false;
        }

        if (!count($sss)) {
            return false;
        }

        try {
            $db = Zend_Registry::get("db");
            $db->beginTransaction();
            $dados = array();
            $dados["correcao"] = 0;
            if ($correcao) {
                $dados["correcao"] = Escola_Util::montaNumero($correcao);
            }
            if (count($sss) > 1) {
                if (!$data_vencimento_default) {
                    $data_vencimento = new Zend_Date();
                    foreach ($sss as $ss) {
                        $vencimento = new Zend_Date($ss->data_vencimento);
                        if ($vencimento < $data_vencimento) {
                            $data_vencimento = $vencimento;
                        }
                    }
                    $dados["data_vencimento"] = $data_vencimento->toString("dd/MM/YYYY");
                } else {
                    $dados["data_vencimento"] = $data_vencimento_default;
                }

                $bc = TbBancoConvenio::pegaPadrao();
                if ($bc) {
                    $dados["id_banco_convenio"] = $bc->getId();
                }
            } else {
                $ss = $sss[0];
                $dados["data_vencimento"] = Escola_Util::formatData($ss->data_vencimento);
                $bc = $ss->pegaBancoConvenio();
                if ($bc) {
                    $dados["id_banco_convenio"] = $bc->getId();
                }
            }
            if ($id_pessoa) {
                $dados["id_pessoa"] = $id_pessoa;
            }
            $boleto = $this->createRow();
            $boleto->setFromArray($dados);
            $errors = $boleto->getErrors();
            if ($errors) {
                throw new Exception("Falha Gerar Boleto: <ul><li>" . implode("<li></li>", $errors) . "</li></ul>");
            }

            $boleto->save();
            if ($boleto->getId()) {
                $tb = new TbBoletoItemTipo();
                $bit = $tb->getPorChave("SS");
                if ($bit) {
                    $tb = new TbBoletoItem();
                    foreach ($sss as $ss) {
                        $dados = array();
                        $dados["id_boleto"] = $boleto->getId();
                        $dados["id_boleto_item_tipo"] = $bit->getId();
                        $dados["chave"] = $ss->getId();

                        $dados["valor"] = Escola_Util::number_format($ss->pega_valor_pagar());
                        $bi = $tb->createRow();
                        $bi->setFromArray($dados);
                        $errors = $bi->getErrors();
                        if (!$errors) {
                            $bi->save();
                        }
                    }
                }

                $db->commit();
                return $boleto;
            }

            $db->rollBack();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}

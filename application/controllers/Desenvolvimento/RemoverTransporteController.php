<?php

class Desenvolvimento_RemoverTransporteController extends Escola_Controller_Logado
{
    public function remover_transporte()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $ids = [
                11,
                4,
                15,
                3,
                7,
                16,
                8
            ];

            if (Escola_Util::hasParametro("--id")) {
                $ids = [Escola_Util::getParametro("--id")];
            }

            foreach ($ids as $id) {
                $tg = Escola_DbUtil::first("
                    select id_transporte_grupo, descricao 
                    from transporte_grupo
                    where (id_transporte_grupo = ?)
                ", [$id]);

                if (!$tg) {
                    continue;
                }

                Escola_Util::log("Removendo ... {$tg->descricao}.");

                $this->excluir_transporte($id);
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex);
        }
    }

    public function excluir_transporte($id = null)
    {
        $db = Zend_Registry::get("db");

        if (!$id) {
            throw new Exception("Falha ao Excluir Transporte, Nenhum Grupo Especificado!");
        }

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {

            // apaga grupo origem
            Escola_Util::log("Excluindo Grupo Origem ... ", false);
            $ids = Escola_DbUtil::listArray("
                select ss.id_servico_solicitacao
                from servico_solicitacao ss
                where (ss.id_servico_transporte_grupo in (
                    select stg.id_servico_transporte_grupo
                    from servico_transporte_grupo stg 
                    where (stg.id_transporte_grupo = ?)
                ))
            ", [$id]);

            if (Escola_Util::isResultado($ids)) {

                $this->excluir_servico_solicitacao($ids);

                $db->query("
                delete from servico_solicitacao 
                where (id_servico_transporte_grupo in (
                    select stg.id_servico_transporte_grupo
                    from servico_transporte_grupo stg 
                    where (stg.id_transporte_grupo = ?)
                ))
                ", [$id]);
            }

            $db->query("
            delete from servico_transporte_grupo 
            where (id_transporte_grupo = ?)
            ", [$id]);

            $db->query("
            delete from servico_transporte_grupo 
            where (id_transporte_grupo = ?)
            ", [$id]);

            $db->query("
            delete from transporte_grupo 
            where (id_transporte_grupo = ?)
            ", [$id]);
            Escola_Util::log("OK");

            if (!$in) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if (!$in) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

    public function excluir_servico_solicitacao($ids)
    {

        Escola_DbUtil::query("
        delete from servico_solicitacao_ocorrencia 
        where (id_servico_solicitacao in (
            " . implode(",", $ids) . "
        ))");

        // verificar c posso excluir os serviços antigos q sobraram
        // $pagamentos = Escola_DbUtil::listar("
        //     select *
        //     from servico_solicitacao_pagamento
        //     where (id_servico_solicitacao in (" . implode(",", $ids) . "));
        // ");
        // if (Escola_Util::isResultado($pagamentos)) {
        //     print_r($pagamentos);
        //     throw new Escola_Exception("Falha! Solicitações Possuem Pagamentos!");
        // }

        Escola_DbUtil::query("
        delete from servico_solicitacao_pagamento 
        where (id_servico_solicitacao in (
            " . implode(",", $ids) . "
        ))");

        Escola_DbUtil::query("
        delete from servico_solicitacao 
        where (id_servico_solicitacao in (
            " . implode(",", $ids) . "
        ))");
    }
}

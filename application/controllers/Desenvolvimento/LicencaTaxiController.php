<?php

class Desenvolvimento_LicencataxiController extends Escola_Controller_Logado
{
    public function licenca_taxi()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $servico_destino = Escola_DbUtil::first("
                select *
                from servico
                where (lower(codigo) = ?)
            ", ['ct']);

            if (!$servico_destino) {
                throw new Escola_Exception("Serviço destino não disponível!");
            }

            //listar todas as licenças de taxi .. para geração de novos números
            $licencas = Escola_DbUtil::listar("
                select s.id_servico, s.descricao as servico,
                    tg.id_transporte_grupo, tg.descricao as grupo, 
                    ss.*
                from servico_solicitacao ss
                    inner join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                    inner join servico s on stg.id_servico = s.id_servico
                    inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                where (lower(tg.chave) = ?)
                and (lower(s.codigo) = ?)
                order by ss.ano_referencia, ss.codigo;
            ", ["taxi", "lt"]);

            if (!Escola_Util::isResultado($licencas)) {
                throw new Escola_Exception("Nenhuma Licença Disponível!");
            }

            $qtd = 0;
            $total = count($licencas);
            foreach ($licencas as $ss) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual, 10),
                    [$ss->servico, 40],
                    [$ss->grupo, 50],
                    "Código: " . $ss->codigo . " / " . $ss->ano_referencia
                ];

                Escola_Util::log($row, false);

                $stg_destinos = Escola_DbUtil::listar("
                    select *
                    from servico_transporte_grupo
                    where (id_servico = ?)
                    and (id_transporte_grupo = ?)
                ", [
                    $servico_destino->id_servico,
                    $ss->id_transporte_grupo
                ]);

                if (!Escola_Util::isResultado($stg_destinos)) {
                    print_r([
                        $stg_destinos,
                        $ss
                    ]);
                    throw new Escola_Exception("Nenhum serviço de destino!");
                }

                if (count($stg_destinos) > 1) {
                    print_r([
                        $stg_destinos,
                        $ss
                    ]);
                    throw new Escola_Exception("Mais de um serviço de destino!");
                }

                $stg_destino = $stg_destinos[0];

                // atualiza somente serviço
                Escola_DbUtil::query("
                    update servico_solicitacao
                    set id_servico_transporte_grupo = ?
                    where (id_servico_solicitacao = ?)
                ", [
                    $stg_destino->id_servico_transporte_grupo,
                    $ss->id_servico_solicitacao
                ]);

                $objs = Escola_DbUtil::listar("
                    select 
                        s.descricao as servico,
                        tg.descricao as grupo,
                        ss.*
                    from servico_solicitacao ss
                        inner join servico_transporte_grupo stg on stg.id_servico_transporte_grupo = ss.id_servico_transporte_grupo
                        inner join servico s on stg.id_servico = s.id_servico
                        inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                    where (ss.id_servico_transporte_grupo = ?)
                    and (ss.ano_referencia = ?)
                    and (ss.codigo = ?)
                    order by ss.ano_referencia, ss.codigo
                ", [
                    $stg_destino->id_servico_transporte_grupo,
                    $ss->ano_referencia,
                    $ss->codigo
                ]);

                $novo_codigo = $ss->codigo;

                if (!Escola_Util::isResultado($objs)) {
                    continue;
                }

                $tb = new TbServicoSolicitacao();
                $obj_ss = $tb->getPorId($ss->id_servico_solicitacao);
                if (!$obj_ss) {
                    print_r($ss);
                    throw new Escola_Exception("Falha ao Tentar Gerar numeração!");
                }

                $novo_codigo = $obj_ss->pega_proximo_codigo();

                Escola_DbUtil::query("
                    update servico_solicitacao
                    set codigo = ?,
                        id_servico_transporte_grupo = ?
                    where (id_servico_solicitacao = ?)
                ", [
                    $novo_codigo,
                    $stg_destino->id_servico_transporte_grupo,
                    $ss->id_servico_solicitacao
                ]);

                Escola_Util::log(" ... OK!");
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex);
        }
    }
}

<?php

class Desenvolvimento_ReagrupaController extends Escola_Controller_Logado
{
    public function reagrupa()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $sql = "
                select tg.id_transporte_grupo, tg.descricao as transporte_grupo,
                    pt.chave as pessoa_tipo,
                    pf.nome as pf_nome,
                    pj.nome_fantasia as pj_nome,
                    count(distinct t.id_transporte) as quantidade
                from transporte_pessoa tp
                    inner join transporte t on tp.id_transporte = t.id_transporte
                    inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                    inner join transporte_veiculo tv on t.id_transporte = tv.id_transporte
                    inner join pessoa p on tp.id_pessoa = p.id_pessoa
                    left outer join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo
                    left outer join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
                    left outer join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                where (tp.id_transporte_pessoa_status = 1)
                and (tp.id_transporte_pessoa_tipo = 1)
                and (tv.id_transporte_veiculo_status = 1)
                and (not t.id_transporte_grupo in (21, 18, 17))

                and (lower(pj.nome_fantasia) like '%bertolini%')
                
                    group by pf.nome, pj.nome_fantasia, tg.id_transporte_grupo, tg.descricao
                having count(distinct t.id_transporte) > 1
                order by tg.descricao, count(distinct t.id_transporte) desc
            ";

            $objs = Escola_DbUtil::listar($sql);
            if (!Escola_Util::isResultado($objs)) {
                throw new Exception("Falha! Nenhum Registro com Problemas!");
            }

            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual, 15),
                    [$obj->transporte_grupo, 40]
                ];

                if (isset($obj->pessoa_tipo)) {
                    if (strtolower($obj->pessoa_tipo) == 'pf') {
                        $row[] = [$obj->cpf, 14];
                        $row[] = [$obj->pf_nome, 45];
                    } elseif (strtolower($obj->pessoa_tipo) == 'pj') {
                        $row[] = [$obj->cnpj, 14];
                        $row[] = [$obj->pj_nome, 45];
                    }
                }

                $row[] = "Cadastros: " . $obj->quantidade;

                Escola_Util::log($row);

                $transportes = Escola_DbUtil::listar("
                    select t.id_transporte, t.codigo,
                            tg.descricao as transporte_grupo,
                            pt.chave as pessoa_tipo,
                            pf.cpf, pf.nome as pf_nome,
                            pj.cnpj, pj.nome_fantasia as pj_nome, count(tv.id_transporte_veiculo) as veiculos
                    from transporte_pessoa tp
                        inner join transporte t on tp.id_transporte = t.id_transporte
                        inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                        inner join pessoa p on tp.id_pessoa = p.id_pessoa
                        inner join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo
                        left outer join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
                        left outer join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                    
                        left outer join transporte_veiculo tv on t.id_transporte = tv.id_transporte and (tv.id_transporte_veiculo_status = 1)
                    where (tp.id_transporte_pessoa_status = 1)
                    and (tp.id_transporte_pessoa_tipo = 1)
                    
                    and (t.id_transporte_grupo = ?)
                    and (lower(pj.nome_fantasia) = ?)
                    group by t.id_transporte, t.codigo, tg.descricao, pt.chave, pf.cpf, pf.nome, pj.cnpj, pj.nome_fantasia
                    order by pf.nome, pj.nome_fantasia, t.codigo;
                ", [$obj->id_transporte_grupo, strtolower($obj->pj_nome)]);

                if (!Escola_Util::isResultado($transportes)) {
                    throw new Exception("Falha! Nenhum Transporte Especificado!");
                }

                // para onde vão todos os veículos
                $destino = $transportes[0];

                unset($transportes[0]);

                foreach ($transportes as $transporte) {

                    Escola_Util::log(PHP_EOL . "       " . $transporte->codigo . " ", false);

                    $pessoas = Escola_DbUtil::listar("
                        select *
                        from transporte_pessoa 
                        where (id_transporte = ?)
                        and (id_transporte_pessoa_status = 1)
                        and (id_pessoa <> ?)
                    ", [$transporte->id_transporte, $transporte->id_pessoa]);

                    // validando pessoas, verificando se tem alguém diferente do autorizatário
                    if (Escola_Util::isResultado($pessoas)) {
                        print_r([
                            "transporte" => $transporte,
                            "pessoas" => $pessoas
                        ]);
                        throw new Exception("Falha! Pessoa Diferente do Autorizatário Cadastrado!");
                    }

                    // pegando e validando os veículos
                    $veiculos = Escola_DbUtil::listar("
                        select tv.id_transporte_veiculo, v.placa
                        from transporte_veiculo tv
                            inner join veiculo v on tv.id_veiculo = v.id_veiculo
                        where (tv.id_transporte = ?)
                        and (tv.id_transporte_veiculo_status = 1)
                    ", [$transporte->id_transporte]);

                    if (!Escola_Util::isResultado($veiculos)) {
                        print_r($transporte);
                        throw new Exception("Falha! Nenhum Veículo Encontrado para o Transporte!");
                    }

                    $veiculo_ids = [];
                    foreach ($veiculos as $veiculo) {
                        $quant = Escola_DbUtil::valor("
                            select count(tv.id_transporte_veiculo) as quantidade
                            from transporte_veiculo tv
                                inner join veiculo v on tv.id_veiculo = v.id_veiculo
                            where (tv.id_transporte = ?)
                            and (trim(lower(v.placa)) = ?)
                        ", [$destino->id_transporte, strtolower(trim($veiculo->placa))]);

                        if (!$quant) {
                            $veiculo_ids[] = $veiculo->id_transporte_veiculo;
                            continue;
                        }

                        Escola_DbUtil::query("
                            update transporte_veiculo
                            set id_transporte_veiculo_status = 2
                            where (id_transporte_veiculo = ?)
                        ", [$veiculo->id_transporte_veiculo]);
                    }

                    if (!Escola_Util::isResultado($veiculo_ids)) {
                        continue;
                    }

                    // modificar transporte dos serviços vinculados ao transporte principal ???
                    // modificar serviços vinculados a pessoas ???

                    // modificar serviços vinculados aos veículos
                    Escola_DbUtil::query("
                        update 
                        servico_solicitacao
                        set id_transporte = ?
                        where (id_transporte = ?)
                        and (lower(tipo) = ?)
                        and (chave in (" . implode(",", $veiculo_ids) . "))
                    ", [$destino->id_transporte, $transporte->id_transporte, 'tv']);

                    // modificar transporte dos veículos
                    Escola_DbUtil::query("
                        update 
                        transporte_veiculo
                        set id_transporte = ?
                        where (id_transporte = ?)
                    ", [$destino->id_transporte, $transporte->id_transporte]);
                }

                Escola_Util::log();
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
            Escola_Util::trataErro($ex, false);
        }
    }

    public function excluir()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $objs = Escola_DbUtil::listar("
                select t.id_transporte, t.codigo, tg.descricao as transporte_grupo
                from transporte t
                    inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                where (not t.id_transporte_grupo in (21, 18, 17))
                and (not exists (
                        select tv.id_transporte_veiculo
                        from transporte_veiculo tv
                        where (tv.id_transporte = t.id_transporte)
                        and (tv.id_transporte_veiculo_status = 1)
                    ))
                order by tg.descricao, t.codigo;
            ");

            if (!Escola_Util::isResultado($objs)) {
                throw new Exception("Falha! Nenhum Registro a Excluir!");
            }

            $controller = Escola_Util::getController("desenvolvimento", "dividir_carga");
            if (!$controller) {
                throw new Exception("Falha! Controller Não Localizado!");
            }

            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual),
                    [$obj->codigo, 6],
                    $obj->transporte_grupo
                ];

                Escola_Util::log($row);

                $controller->excluir($obj);
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
            Escola_Util::trataErro($ex, false);
        }
    }
}

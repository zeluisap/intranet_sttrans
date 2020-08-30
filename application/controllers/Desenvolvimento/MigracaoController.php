<?php

class Desenvolvimento_MigracaoController extends Escola_Controller_Logado
{
    public function migracao()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {
            $ids = [
                [2, 13],
                [9, 6],
                [5, 8],
                [
                    [307, 309, 313, 146, 288, 531, 328],
                    8
                ],
                [
                    [1043, 1087],
                    6
                ],
                [7, 10]
            ];

            foreach ($ids as $id) {
                $this->migrar($id[0], $id[1]);
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
            Escola_Util::trataErro($ex, false);
        }
    }

    public function migrar_transportes($ids, $id_grupo_destino)
    {

        if (!($ids && is_array($ids) && count($ids))) {
            return;
        }

        $db = Zend_Registry::get("db");

        $sql = "select descricao from transporte_grupo where id_transporte_grupo = ?";
        $obj_destino = Escola_DbUtil::first($sql, [$id_grupo_destino]);

        if (!$obj_destino) {
            return;
        }

        Escola_Util::log("--- Migrando Transportes de [" . implode(", ", $ids) . "] ===> [{$obj_destino->descricao}]");

        $this->atualizaServico($ids, $id_grupo_destino);

        Escola_Util::log("Atualizando Transportes ... ", false);
        // atualiza todos os transportes para o novo grupo
        $sql = "update transporte set id_transporte_grupo = ? where id_transporte in (" . implode(",", $ids) . ") ";
        $db->query($sql, [$id_grupo_destino]);
        Escola_Util::log("OK");
    }

    private function migrar_transporte_grupo($id_grupo_origem, $id_grupo_destino)
    {

        if (is_array($id_grupo_origem)) {
            return;
        }

        $db = Zend_Registry::get("db");

        $sql = "select descricao from transporte_grupo where id_transporte_grupo = ?";
        $obj_origem = Escola_DbUtil::first($sql, [$id_grupo_origem]);
        $obj_destino = Escola_DbUtil::first($sql, [$id_grupo_destino]);

        if (!$obj_destino) {
            return;
        }

        Escola_Util::log("--- Migrando de [{$obj_origem->descricao}] ===> [{$obj_destino->descricao}]");

        $this->atualizaServico($id_grupo_origem, $id_grupo_destino);
        $this->atualizaMotorista($id_grupo_origem, $id_grupo_destino);

        Escola_Util::log("Atualizando Transportes ... ", false);
        // atualiza todos os transportes para o novo grupo
        Escola_DbUtil::query("
            update transporte 
            set id_transporte_grupo = ? 
            where id_transporte_grupo = ? 
        ", [
            $id_grupo_destino,
            $id_grupo_origem
        ]);
        Escola_Util::log("OK");

        $this->excluir_transporte($id_grupo_origem);
    }

    private function migrar($id_grupo_origem, $id_grupo_destino)
    {

        $this->migrar_transportes($id_grupo_origem, $id_grupo_destino);
        $this->migrar_transporte_grupo($id_grupo_origem, $id_grupo_destino);
    }

    public function copiarServico($origem, $id_grupo_destino)
    {

        if (!($origem && isset($origem->id_servico_transporte_grupo) && $origem->id_servico)) {
            return null;
        }

        if (!$id_grupo_destino) {
            return null;
        }

        $db = Zend_Registry::get("db");

        $sql = "select stg.id_servico_transporte_grupo
        from servico_transporte_grupo stg
        where (stg.id_servico = :id_servico)
        and (stg.id_transporte_grupo = :id_grupo_destino)";
        $id_stg_destino = Escola_DbUtil::valor($sql, array(
            ":id_servico" => $origem->id_servico,
            ":id_grupo_destino" => $id_grupo_destino
        ));

        if ($id_stg_destino) {
            return $this->getTransporteGrupoPorId($id_stg_destino);
        }

        // carregar os dados do registro, para copiar.
        $dados_origem = Escola_DbUtil::first("
                        select * from servico_transporte_grupo 
                        where (id_servico_transporte_grupo = ?)
                        ", array($origem->id_servico_transporte_grupo));

        // Ã© necessÃ¡rio gerar outro registro de valor.
        $id_valor_destino = Escola_DbUtil::copiaValor($dados_origem->id_valor);

        Escola_DbUtil::query("insert into servico_transporte_grupo 
                        (id_servico, id_transporte_grupo, id_valor, validade_dias, obrigatorio, juros_dia, id_periodicidade, mes_referencia, vencimento_dias, emite_documento)
                        values 
                        (:id_servico, :id_transporte_grupo, :id_valor, :validade_dias, :obrigatorio, :juros_dia, :id_periodicidade, :mes_referencia, :vencimento_dias, :emite_documento)
                        ", [
            ":id_servico" => $dados_origem->id_servico,
            ":id_transporte_grupo" => $id_grupo_destino,
            ":id_valor" => $id_valor_destino,
            ":validade_dias" => $dados_origem->validade_dias,
            ":obrigatorio" => $dados_origem->obrigatorio,
            ":juros_dia" => $dados_origem->juros_dia,
            ":id_periodicidade" => $dados_origem->id_periodicidade,
            ":mes_referencia" => $dados_origem->mes_referencia,
            ":vencimento_dias" => $dados_origem->vencimento_dias,
            ":emite_documento" => $dados_origem->emite_documento
        ]);

        $id = Escola_DbUtil::lastInsertId();
        if (!$id) {
            throw new Escola_Exception("Falha ao Gerar Serviço!");
        }

        return $this->getTransporteGrupoPorId($id);
    }

    public function getTransporteGrupoPorId($id)
    {
        if (!$id) {
            return null;
        }

        return Escola_DbUtil::first("
            select s.descricao as servico, tg.descricao as transporte_grupo,
                stg.*
            from servico_transporte_grupo stg
                inner join servico s on stg.id_servico = s.id_servico
                inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
            where (stg.id_servico_transporte_grupo = ?)
        ", [$id]);
    }

    private function atualizaServico($id_grupo_origem, $id_grupo_destino)
    {
        $db = Zend_Registry::get("db");

        $where = $params = [];

        if (!$id_grupo_origem) {
            return;
        }

        if (is_array($id_grupo_origem)) {
            $where[] = "ss.id_transporte in (" . implode(",", $id_grupo_origem) . ") or (lower(ss.tipo) = 'tr' and ss.chave in (" . implode(",", $id_grupo_origem) . ")) ";
        } elseif ($id_grupo_origem) {
            $where[] = "stg.id_transporte_grupo = :id_grupo_origem";
            $params[":id_grupo_origem"] = $id_grupo_origem;
        }

        Escola_Util::log("Atualizando Serviços do Transporte de Origem ... ");
        // prepara atualizaÃ§Ã£o dos serviÃ§os
        $sql = "select stg.id_servico_transporte_grupo, s.id_servico, s.descricao as servico, count(ss.id_servico_solicitacao) as quantidade
        from servico_solicitacao ss
        inner join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
        inner join servico s on stg.id_servico = s.id_servico
        where ( " . implode(" ) and ( ", $where) . " )
        group by stg.id_servico_transporte_grupo, s.id_servico, s.descricao
        order by s.descricao";

        $servicos_origem = Escola_DbUtil::listar($sql, $params);

        if (!Escola_Util::isResultado($servicos_origem)) {
            // se nÃ£o existe nenhum registro vinculado ao transporte, sai do mÃ©todo.
            Escola_Util::log();
            Escola_Util::log("Nenhum ServiÃ§o DisponÃ­vel ... Finalizando!");
            return;
        }

        // para cada serviÃ§o verificar se existe equivalente no destino
        foreach ($servicos_origem as $origem) {

            $stg_destino = $this->copiarServico($origem, $id_grupo_destino);

            if (!$stg_destino) {
                throw new Exception("Falha! Novo ServiÃ§o nÃ£o foi criado!");
            }

            // atualiza todos os serviÃ§os apontando pro novo serviÃ§o_transporte_grupo
            $where = $params = [];
            if (is_array($id_grupo_origem)) {
                $where[] = "id_transporte in (" . implode(",", $id_grupo_origem) . ")  or (lower(tipo) = 'tr' and chave in (" . implode(",", $id_grupo_origem) . "))";
            } elseif ($id_grupo_origem) {
                $where[] = "id_transporte in (
                    select id_transporte
                    from transporte
                    where (id_transporte_grupo = :id_grupo_origem)
                ) or (lower(tipo) = :tipo and chave in (
                    select id_transporte
                    from transporte
                    where (id_transporte_grupo = :id_grupo_origem)                    
                ))";
                $params[":id_grupo_origem"] = $id_grupo_origem;
                $params[":tipo"] = "tr";
            }
            $where[] = "(id_servico_transporte_grupo = :id_stg_origem)";
            $params[":id_stg_origem"] = $origem->id_servico_transporte_grupo;

            $db->query("
                update servico_solicitacao 
                set id_servico_transporte_grupo = :id_stg_destino
                where (" . implode(") and (", $where) . ")
            ", array_merge([
                ":id_stg_destino" => $stg_destino->id_servico_transporte_grupo
            ], $params));

            if (!is_array($id_grupo_origem)) {
                Escola_DbUtil::query("
                    update servico_solicitacao 
                    set id_servico_transporte_grupo = :id_stg_destino
                    where (lower(tipo) = :tipo)
                    and (chave in (
                        select id_motorista
                        from motorista
                        where (id_transporte_grupo = :id_grupo_origem)
                    ))
                ", [
                    ":id_stg_destino" => $stg_destino->id_servico_transporte_grupo,
                    ":tipo" => "mo",
                    ":id_grupo_origem" => $id_grupo_origem
                ]);
            }
        }
    }

    private function atualizaMotorista($id_grupo_origem, $id_grupo_destino)
    {
        $db = Zend_Registry::get("db");

        Escola_Util::log("Atualizando Motoristas ... ", false);
        // atualiza todos os transportes para o novo grupo
        $sql = "update motorista set id_transporte_grupo = ? where id_transporte_grupo = ? ";
        $db->query($sql, [$id_grupo_destino, $id_grupo_origem]);
        Escola_Util::log("OK");
    }

    public function excluir_transporte($id = null)
    {
        $db = Zend_Registry::get("db");

        if (!$id) {
            $id = Escola_Util::getParametro("-id");
        }

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {
            if (!$id) {
                throw new Exception("Falha ao Excluir Transporte, Nenhum Transporte Especificado!");
            }

            // apaga grupo origem
            Escola_Util::log("Excluindo Grupo Origem ... ", false);
            $transportes = Escola_DbUtil::listar("
                select t.id_transporte, t.codigo
                from transporte t
                where (t.id_transporte_grupo = ?)
            ", [$id]);
            if (Escola_Util::isResultado($transportes)) {
                print_r($transportes);
                throw new Escola_Exception("Impossível Excluir Grupo de Transportes, Existem Transportes!");
            }

            $transportes = Escola_DbUtil::listar("
                select 
                    s.descricao as servico,
                    tg.descricao as grupo,
                    ss.id_servico_solicitacao, 
                    s.id_servico,
                    tg.id_transporte_grupo,
                    ss.id_servico_transporte_grupo,
                    ss.codigo, 
                    ss.ano_referencia, 
                    ss.tipo, 
                    ss.chave, 
                    ss.id_transporte
                from servico_solicitacao ss
                    left outer join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                    left outer join servico s on stg.id_servico = s.id_servico
                    left outer join transporte_grupo tg on tg.id_transporte_grupo = stg.id_transporte_grupo
                where (ss.id_servico_transporte_grupo in (
                    select stg.id_servico_transporte_grupo
                    from servico_transporte_grupo stg
                    where (stg.id_transporte_grupo = ?)
                ))
            ", [$id]);
            if (Escola_Util::isResultado($transportes)) {
                print_r($transportes);
                throw new Escola_Exception("Impossível Excluir Grupo de Transportes, Existem Serviços!");
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

    public function migrar_pj_carga()
    {
        $db = Zend_Registry::get("db");

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {

            $id_carga = Escola_DbUtil::valor("
                select id_transporte_grupo
                from transporte_grupo
                where (lower(chave) = ?)
            ", ['carga']);

            if (!$id_carga) {
                throw new Exception("Falha! Grupo de Carga Não Encontrado!");
            }

            $cnpjs = [
                "05824316000111", // transwood
                "04872156000113", // sabino
                "03557255000148", // trans gold
            ];

            $ids = Escola_DbUtil::listArray("
                select t.id_transporte
                from transporte t
                    inner join transporte_pessoa tp on t.id_transporte = tp.id_transporte
                    inner join pessoa p on tp.id_pessoa = p.id_pessoa
                    inner join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                    inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                where (tp.id_transporte_pessoa_status = 1) -- ativo
                    and (tp.id_transporte_pessoa_tipo = 1) -- permissionário
                    and (tg.id_transporte_grupo <> ?) -- carga
                    and (pj.cnpj in (" . implode(", ", $cnpjs) . "))
                order by tg.descricao, t.codigo
            ", [$id_carga]);

            $this->migrar_transportes($ids, $id_carga);

            if (!$in) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if (!$in) {
                $db->rollBack();
            }
            Escola_Util::trataErro($ex);
        }
    }

    public function carteira()
    {
        $db = Zend_Registry::get("db");

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {

            $chaves_antigas = ['tcc', 'cm', 'cp'];

            Escola_Util::log("Migrar serviços de CARTEIRA para apenas um.");
            Escola_Util::log("Preparando ... ", false);

            //pega serviço destino
            $servico_destino = Escola_DbUtil::first("
                select id_servico, codigo
                from servico
                where lower(codigo) = ?
            ", ["ca"]);

            if (!($servico_destino && isset($servico_destino->id_servico) && $servico_destino->id_servico)) {
                throw new Escola_Exception("Falha! Serviço de Destino não localizado!");
            }

            $objs = Escola_DbUtil::listar("
                select 
                    s.id_servico, s.codigo as servico_codigo, s.descricao as servico, 
                    tg.id_transporte_grupo, tg.descricao as transporte_grupo, 
                    stg.id_servico_transporte_grupo,
                    count(ss.id_servico_solicitacao) as servicos
                from servico_solicitacao ss
                    inner join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                    inner join servico s on stg.id_servico = s.id_servico
                    inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                where (lower(s.codigo) in ('" . implode("', '", $chaves_antigas) . "'))
                group by 
                    s.id_servico, s.codigo, s.descricao, 
                    tg.id_transporte_grupo, tg.descricao, 
                    stg.id_servico_transporte_grupo
                order by s.descricao, tg.descricao
            ");

            if (!Escola_Util::isResultado($objs)) {
                Escola_Util::log();
                throw new Exception("Falha! Nenhum Registro a Migrar!");
            }

            Escola_Util::log("OK!");

            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual, 10),
                    [$obj->servico, 40],
                    [$obj->transporte_grupo, 50]
                ];

                $row[] = "Registros: " . $obj->servicos;

                Escola_Util::log($row);

                $id_stg = Escola_DbUtil::valor("
                    select id_servico_transporte_grupo
                    from servico_transporte_grupo stg
                    where (id_transporte_grupo = ?)
                    and (stg.id_servico = ?)
                    order by id_servico_transporte_grupo desc
                ", [$obj->id_transporte_grupo, $servico_destino->id_servico]);

                if (!$id_stg) {
                    // carrega dados do grupo de transporte
                    $grupo_origem = Escola_DbUtil::first("
                        select *
                        from servico_transporte_grupo
                        where (id_servico_transporte_grupo = ?)
                    ", [$obj->id_servico_transporte_grupo]);

                    $id_valor = Escola_DbUtil::copiaValor($grupo_origem->id_valor);
                    if (!$id_valor) {
                        print_r($obj);
                        throw new Escola_Exception("Falha! Valor não identificado!");
                    }

                    Escola_DbUtil::query("
                        insert into servico_transporte_grupo
                        (id_servico, id_transporte_grupo, id_valor, validade_dias, obrigatorio, juros_dia, id_periodicidade, mes_referencia, vencimento_dias, emite_documento)
                        values
                        (:id_servico, :id_transporte_grupo, :id_valor, :validade_dias, :obrigatorio, :juros_dia, :id_periodicidade, :mes_referencia, :vencimento_dias, :emite_documento)
                    ", [
                        ":id_servico" => $servico_destino->id_servico,
                        ":id_transporte_grupo" => $grupo_origem->id_transporte_grupo,
                        ":id_valor" => $id_valor,
                        ":validade_dias" => $grupo_origem->validade_dias,
                        ":obrigatorio" => $grupo_origem->obrigatorio,
                        ":juros_dia" => $grupo_origem->juros_dia,
                        ":id_periodicidade" => $grupo_origem->id_periodicidade,
                        ":mes_referencia" => $grupo_origem->mes_referencia,
                        ":vencimento_dias" => $grupo_origem->vencimento_dias,
                        ":emite_documento" => $grupo_origem->emite_documento
                    ]);

                    $id_stg = Escola_DbUtil::lastInsertId();
                }

                if (!$id_stg) {
                    print_r($obj);
                    throw new Escola_Exception("Falha! Serviço Transporte Grupo Destino não especificado!");
                }

                Escola_DbUtil::query("
                    update servico_solicitacao
                    set id_servico_transporte_grupo = ?
                    where (id_servico_transporte_grupo = ?)
                ", [
                    $id_stg,
                    $obj->id_servico_transporte_grupo
                ]);

                // excluindo vínculo do serviço
                Escola_DbUtil::query("
                    delete from servico_transporte_grupo where id_servico_transporte_grupo = ?
                ", [$obj->id_servico_transporte_grupo]);
            }

            $id_pendentes = Escola_DbUtil::listArray("
                select 
                    stg.id_servico_transporte_grupo
                from servico_transporte_grupo stg 
                    inner join servico s on stg.id_servico = s.id_servico
                    inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                where (lower(s.codigo) in ('" . implode("', '", $chaves_antigas) . "'))
                order by s.descricao, tg.descricao
            ");

            // apagando serviços
            Escola_Util::log("Apagando Serviços ... ", false);

            if (Escola_Util::isResultado($id_pendentes)) {
                Escola_DbUtil::query("
                    delete 
                    from servico_transporte_grupo 
                    where id_servico_transporte_grupo in (" . implode(",", $id_pendentes) . ")
                ");
            }

            Escola_DbUtil::query("delete from servico where lower(codigo) in ('" . implode("', '", $chaves_antigas) . "')");
            Escola_Util::log("OK");

            // apagando serviços
            Escola_Util::log("Alterando o Nome do Serviço ... ", false);
            Escola_DbUtil::query("
                update servico
                set descricao = ? 
                where id_servico = ?", ['CARTEIRA', $servico_destino->id_servico]);
            Escola_Util::log("OK");

            if (!$in) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if (!$in) {
                $db->rollBack();
            }
            Escola_Util::trataErro($ex);
        }
    }

    public function solicitacoes_inconsistentes()
    {
        include_once "controllers/Desenvolvimento/RemoverTransporteController.php";
        $removerController = new Desenvolvimento_RemoverTransporteController(new Zend_Controller_Request_Simple(), new Zend_Controller_Response_Cli());

        $db = Zend_Registry::get("db");

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {

            Escola_Util::log("Verificando Inconsistência nas Taxas ... ");
            $solicitacoes = Escola_DbUtil::listar("
                select
                        ss.id_servico_solicitacao,
                        tg.id_transporte_grupo as grupo_id,
                        tg.descricao as grupo,
                        tg1.id_transporte_grupo as grupo_chave_id,
                        tg1.descricao as grupo_chave,
                        tg2.id_transporte_grupo as grupo_solicitacao_id,
                        tg2.descricao as grupo_solicitacao,
                        sss.id_servico_solicitacao_status,
                        sss.descricao as status,
                        ss.codigo,
                        ss.ano_referencia,
                        ss.tipo, ss.chave,
                        ss.id_transporte,
                        ss.id_servico_transporte_grupo,
                        stg.id_servico
                from servico_solicitacao ss
                
                    left outer join transporte t on ss.id_transporte = t.id_transporte
                    left outer join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                
                    left outer join transporte t1 on lower(ss.tipo = 'tr') and ss.chave = t1.id_transporte
                    left outer join transporte_grupo tg1 on t1.id_transporte_grupo = tg1.id_transporte_grupo
                
                    left outer join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                    left outer join transporte_grupo tg2 on stg.id_transporte_grupo = tg2.id_transporte_grupo
                
                    left outer join servico_solicitacao_status sss on ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status
                
                where (tg.id_transporte_grupo <> tg2.id_transporte_grupo)
                or (tg1.id_transporte_grupo <> tg2.id_transporte_grupo)
                order by ss.ano_referencia, ss.codigo;
            ");


            $qtd = 0;
            $total = count($solicitacoes);
            foreach ($solicitacoes as $ss) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $original_id = 0;
                $servico_original = "";
                if ($ss->grupo) {
                    $original_id = $ss->grupo_id;
                    $servico_original = $ss->grupo;
                } elseif ($ss->grupo_chave) {
                    $original_id = $ss->grupo_chave_id;
                    $servico_original = $ss->grupo_chave;
                }

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual, 10),
                    [$servico_original, 40],
                    [$ss->grupo_solicitacao, 50],
                    "Código: " . $ss->codigo . " / " . $ss->ano_referencia
                ];

                Escola_Util::log($row, false);

                if ($ss->id_servico_solicitacao_status != 2) {
                    $removerController->excluir_servico_solicitacao([$ss->id_servico_solicitacao]);
                    Escola_Util::log(" - Não paga - EXCLUÍDO!");
                    continue;
                }

                $obj = new stdClass();
                $obj->id_servico_transporte_grupo = $ss->id_servico_transporte_grupo;
                $obj->id_servico = $ss->id_servico;

                $novoServico = $this->copiarServico($obj, $original_id);

                if (!$novoServico) {
                    print_r($ss);
                    throw new Escola_Exception("Falha ao Gerar Serviço!");
                }

                Escola_DbUtil::query("
                    update servico_solicitacao
                    set id_servico_transporte_grupo = ?
                    where (id_servico_solicitacao = ?)
                ", [$novoServico->id_servico_transporte_grupo, $ss->id_servico_solicitacao]);
                Escola_Util::log(" - OK!");
            }

            if (!$in) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if (!$in) {
                $db->rollBack();
            }
            Escola_Util::trataErro($ex);
        }
    }
}

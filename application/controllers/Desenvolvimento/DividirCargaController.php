<?php

class Desenvolvimento_DividirCargaController extends Escola_Controller_Logado
{
    private $grupo_carga = null;

    public function carrega_grupo_carga()
    {
        $grupo_carga = Escola_DbUtil::first("
            select *
            from transporte_grupo 
            where (lower(chave) = ?)
        ", ['carga']);

        if (!$grupo_carga) {
            throw new Exception("Falha! Grupo Carga não disponível!");
        }

        $this->grupo_carga  = $grupo_carga;
    }

    public function dividir_carga()
    {

        include_once "controllers/Desenvolvimento/MigracaoController.php";

        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $this->carrega_grupo_carga();

            // lista todos os transportes
            $transportes = Escola_DbUtil::listar("
                select t.*
                from transporte t
                    inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                where (lower(tg.id_transporte_grupo) = ?)
                order by t.codigo
            ", [$this->grupo_carga->id_transporte_grupo]);

            if (!$transportes) {
                throw new Exception("Falha! Nenhum Transporte Disponível!");
            }

            $qtd = 0;
            $total = count($transportes);
            foreach ($transportes as $transporte) {
                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                // pega permissionÃ¡rio
                $pessoas = Escola_DbUtil::listar("
                    select 
                        pt.chave as pessoa_tipo, 
                        pf.nome as pf_nome, 
                        pj.nome_fantasia as pj_nome,
                        case 
                            when (tp.id_transporte_pessoa_status = 1) then true
                            when (tp.id_transporte_pessoa_status <> 1) then false
                        end as ativo,
                        tp.*
                    from transporte_pessoa tp
                        inner join transporte_pessoa_tipo tpt on tp.id_transporte_pessoa_tipo = tpt.id_transporte_pessoa_tipo
                        left outer join pessoa p on tp.id_pessoa = p.id_pessoa
                        left outer join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo
                        left outer join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
                        left outer join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                    where (lower(tpt.chave) = ?)
                    and (tp.id_transporte = ?)
                    order by pf.nome, pj.nome_fantasia
                ", ['pr', $transporte->id_transporte]);

                $permissionario = null;
                if (Escola_Util::isResultado($pessoas)) {
                    $permissionarios = array_filter($pessoas, function ($pessoa) {
                        return $pessoa->ativo;
                    });

                    if (count($permissionarios) > 1) {
                        print_r($permissionarios);
                        throw new Exception("Falha! Mais de Um Permissionário!");
                    }

                    $permissionario = $permissionarios[0];
                }

                // if (!$permissionario) {
                //     print_r($transporte);
                //     throw new Exception("Falha! Nenhum PermissionÃ¡rio Cadastrado!");
                // }

                Escola_Util::log([$qtd, Escola_Util::progresso($percentual), $transporte->codigo, $transporte->pf_nome, $transporte->pj_nome]);

                $veiculos = Escola_DbUtil::listar("
                    select vt.id_veiculo_tipo, 
                        vt.chave as veiculo_tipo_chave, 
                        vt.descricao as veiculo_tipo, 
                        v.placa, v.modelo,
                        case 
                            when (tv.id_transporte_veiculo_status = 1) then true
                            when (tv.id_transporte_veiculo_status <> 1) then false
                        end as ativo,
                        tv.*
                    from transporte_veiculo tv 
                        inner join veiculo v on tv.id_veiculo = v.id_veiculo
                        inner join veiculo_tipo vt on v.id_veiculo_tipo = vt.id_veiculo_tipo 
                    where (tv.id_transporte = ?)
                    order by v.placa
                ", [
                    $transporte->id_transporte
                ]);

                if (!Escola_Util::isResultado($veiculos)) {
                    $this->excluir($transporte);
                    continue;
                }

                $veiculos_ativos = array_filter($veiculos, function ($veiculo) {
                    return $veiculo->ativo;
                });

                $transporte->pessoas = $pessoas;
                $transporte->permissionario = $permissionario;
                $transporte->veiculos = $veiculos;
                $transporte->veiculos_ativos = $veiculos_ativos;

                $grupo_destino = $this->getTransporteGrupo($transporte);

                if (!$grupo_destino) {
                    print_r($transporte);
                    throw new Exception("Falha! Grupo não Escolhido!");
                }

                Escola_DbUtil::query("
                    update transporte
                    set codigo = ?
                    where (id_transporte = ?)
                ", [$transporte->codigo, $transporte->id_transporte]);

                $migracaoController = new Desenvolvimento_MigracaoController(new Zend_Controller_Request_Simple(), new Zend_Controller_Response_Cli());
                $migracaoController->migrar_transportes([$transporte->id_transporte], $grupo_destino->id_transporte_grupo);
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex, false);
        }
    }

    public function migrar_motoristas()
    {

        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $this->carrega_grupo_carga();

            // pega o id do grupo de carga_pj para migrar os motoristas vinculados a cargas
            $id_carga_pj = Escola_DbUtil::valor("
                select id_transporte_grupo
                from transporte_grupo
                where (lower(chave) = ?)
            ", ['carga_pj']);

            if (!$id_carga_pj) {
                throw new Exception("Grupo [CARGA_PJ] para atualização de Motoristas não disponível!");
            }

            /**
             * pega todos os ids dos motoristas vinculados ao grupo de carga
             */
            $ids = Escola_DbUtil::listArray("
                select id_motorista
                from motorista
                where (id_transporte_grupo = ?)
            ", [$this->grupo_carga->id_transporte_grupo]);

            if (!Escola_Util::isResultado($ids)) {
                throw new Exception("Falha! Nenhum Motorista Vinculado ao Transporte!");
            }

            $this->migrar_servicos($ids, $id_carga_pj);

            Escola_Util::log();
            Escola_Util::log("Atualizando Motoristas!");
            Escola_DbUtil::query("
                update motorista
                set id_transporte_grupo = ?
                where (id_transporte_grupo = ?)
            ", [$id_carga_pj, $this->grupo_carga->id_transporte_grupo]);

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex, false);
        }
    }

    private function migrar_servicos($ids, $id_carga_pj)
    {

        if (!Escola_Util::isResultado($ids) || !$id_carga_pj) {
            return;
        }

        /**
         * primeiro precisamos migrar os vínculos dos motoristas
         * na tabela servico_transporte_grupo
         */
        $objs = Escola_DbUtil::listar("
                    select ss.id_servico_transporte_grupo, stg.id_servico, s.descricao as servico
                    from servico_solicitacao ss
                        inner join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                        inner join servico s on stg.id_servico = s.id_servico
                    where (ss.tipo = 'MO')
                    and (ss.chave in (" . implode(", ", $ids) . "))
                    group by ss.id_servico_transporte_grupo, stg.id_servico, s.descricao
                    order by ss.id_servico_transporte_grupo, stg.id_servico
        ");

        if (!Escola_Util::isResultado($objs)) {
            return;
        }

        $controller = Escola_Util::getController("desenvolvimento", "migracao");
        if (!$controller) {
            throw new Exception("Controller de Migração não localizado!");
        }

        foreach ($objs as $obj) {
            Escola_Util::log([
                "Apagando Serviços ... ",
                $obj->servico
            ]);
            $stg_destino = $controller->copiarServico($obj, $id_carga_pj);

            if (!$stg_destino) {
                print_r($obj);
                throw new Exception("Falha! Serviço não Criado!");
            }

            Escola_DbUtil::query("
                update servico_solicitacao 
                set id_servico_transporte_grupo = ?
                where (id_servico_transporte_grupo = ?)
                ", [$stg_destino->id_servico_transporte_grupo, $obj->id_servico_transporte_grupo]);
        }
    }

    public function excluir($transporte)
    {
        if (!$transporte) {
            return;
        }

        if (!$transporte->id_transporte) {
            return;
        }

        Escola_DbUtil::query("
            delete 
            from transporte_veiculo_baixa 
            where (id_transporte_veiculo in (
                select id_transporte_veiculo 
                from transporte_veiculo
                where (id_transporte = ?)
            ))
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from transporte_veiculo where id_transporte = ?
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from transporte_pessoa where id_transporte = ?
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from servico_solicitacao_pagamento where id_servico_solicitacao in (
                select id_servico_solicitacao
                from servico_solicitacao 
                where (id_transporte = ?)
            )
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from servico_solicitacao_ocorrencia where id_servico_solicitacao in (
                select id_servico_solicitacao
                from servico_solicitacao 
                where (id_transporte = ?)
            )
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from servico_solicitacao where id_transporte = ?
        ", [$transporte->id_transporte]);

        Escola_DbUtil::query("
            delete from transporte where id_transporte = ?
        ", [$transporte->id_transporte]);
    }

    private function getTransporteGrupo($transporte)
    {
        $methods = [
            "pj",
            "sx",
            "sr",
            "outros"
        ];

        //$grupos = [];
        foreach ($methods as $method) {
            $method_name = "getGrupo" . ucfirst($method);
            if (!method_exists($this, $method_name)) {
                continue;
            }
            $grupo = $this->$method_name($transporte);

            if ($grupo) {
                return $grupo;
            }

            // $existe = Escola_Util::array_some($grupos, function ($item) use ($grupo) {
            //     return ($grupo->id_transporte_grupo == $item->id_transporte_grupo);
            // });

            // if ($grupo && !$existe) {
            //     $grupos[] = $grupo;
            // }
        }

        return null;

        // if (!Escola_Util::isResultado($grupos)) {
        //     print_r($transporte);
        //     throw new Exception("Falha! Nenhum Grupo Detectado!");
        // }

        // if (count($grupos) > 1) {
        //     $existe = Escola_Util::array_some($grupos, function ($item) {
        //         return (strtolower($item->chave) == 'carga_pj');
        //     });

        //     if (!$existe) {
        //         print_r([
        //             "transporte" => $transporte,
        //             "grupos" => $grupos,
        //         ]);
        //         throw new Exception("Transporte Retornou Mais de um Grupo!");
        //     }
        // }

        // return $grupos[0];
    }

    private function getGrupoSr($transporte)
    {
        if (!$transporte->veiculos) {
            return null;
        }

        $ids = [6, 10, 18]; // caminhonete, caminhoneta
        $veiculos_dentro = $veiculos_fora = [];
        foreach ($transporte->veiculos as $veiculo) {
            if (in_array($veiculo->id_veiculo_tipo, $ids)) {
                $veiculos_dentro[] = $veiculo;
            } else {
                $veiculos_fora[] = $veiculo;
            }
        }

        if (!count($veiculos_dentro)) {
            return null;
        }

        $this->geraCodigo("SR", $transporte);

        return $this->criaGrupoSr();
    }

    private function getGrupoSx($transporte)
    {
        if (!$transporte->veiculos) {
            return null;
        }

        $ids = [3, 8, 13, 9]; // caminhonete, caminhoneta
        $veiculos_dentro = $veiculos_fora = [];
        foreach ($transporte->veiculos as $veiculo) {
            if (in_array($veiculo->id_veiculo_tipo, $ids)) {
                $veiculos_dentro[] = $veiculo;
            } else {
                $veiculos_fora[] = $veiculo;
            }
        }

        if (!count($veiculos_dentro)) {
            return null;
        }

        $this->geraCodigo("SX", $transporte);

        return $this->criaGrupoSx();
    }

    private function getGrupoPj($transporte)
    {
        if (!$transporte) {
            return null;
        }

        if (!$transporte->permissionario) {
            return null;
        }

        if (strtolower($transporte->permissionario->pessoa_tipo) != "pj") {
            return null;
        }

        $this->geraCodigo("PJ", $transporte);

        return $this->criaGrupoPj();
    }

    private function getGrupoOutros($transporte)
    {
        $this->geraCodigo("SX", $transporte);
        return $this->criaGrupoSx();
    }

    //criaÃ§Ã£o
    private function criaGrupo($chave)
    {
        $grupo = Escola_DbUtil::first("
            select *
            from transporte_grupo
            where (lower(chave) = ?)
        ", [strtolower('carga_' . $chave)]);

        if ($grupo) {
            return $grupo;
        }

        $params = [
            ":chave" => strtoupper("carga_" . $chave),
            ":descricao" => strtoupper("TRANSPORTE DE CARGA - " . $chave),
            ":id_banco_convenio" => $this->grupo_carga->id_banco_convenio,
            ":possui_concessao" => $this->grupo_carga->possui_concessao,
            ":veiculo_unico" => $this->grupo_carga->veiculo_unico
        ];

        $id = Escola_DbUtil::insert("
            insert into transporte_grupo
            (chave, descricao, id_banco_convenio, possui_concessao, veiculo_unico)
            values
            (:chave, :descricao, :id_banco_convenio, :possui_concessao, :veiculo_unico)
        ", $params);

        if (!$id) {
            print_r($params);
            throw new Exception("Falha! Nenhum Grupo Adicionado!");
        }

        return Escola_DbUtil::first("
            select *
            from transporte_grupo
            where (id_transporte_grupo = ?)
        ", [$id]);
    }
    private function criaGrupoSr()
    {
        return $this->criaGrupo("sr");
    }

    private function criaGrupoSx()
    {
        return $this->criaGrupo("sx");
    }

    private function criaGrupoPj()
    {
        return $this->criaGrupo("pj");
    }

    private function geraCodigo($prefixo, $transporte)
    {
        if (!$prefixo && !$transporte->codigo) {
            return null;
        }

        $codigo = filter_var($transporte->codigo, FILTER_SANITIZE_NUMBER_INT);
        if (!$codigo) {
            $codigo = 1;
        }

        while (true) {
            $novo_codigo = $prefixo . str_pad($codigo, 4, "0", STR_PAD_LEFT);

            $quant = Escola_DbUtil::valor("
                select count(id_transporte)
                from transporte
                where (codigo = ?)
            ", [$novo_codigo]);

            if (!$quant) {
                $transporte->codigo = $novo_codigo;
                return;
            }

            $codigo++;
        }
    }
}

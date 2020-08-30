<?php

class Desenvolvimento_RecuperarTransporteController extends Escola_Controller_Logado
{
    public function recuperar_transporte()
    {

        /** 
         * ids dos transportes legados 
         * para importação
         */
        $ids = [
            2220
        ];

        $dblegado = Zend_Registry::get("dblegado");
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            // lista todos os transportes
            $transportes = Escola_DbUtil::listar("
                select t.*
                from transporte t
                    inner join transporte_grupo tg on t.id_transporte_grupo = tg.id_transporte_grupo
                where (t.id_transporte in ( " . implode(", ", $ids) . " ))
                order by t.codigo
            ", [$this->grupo_carga->id_transporte_grupo], $dblegado);

            if (!$transportes) {
                throw new Exception("Falha! Nenhum Transporte Disponível!");
            }

            $qtd = 0;
            $total = count($transportes);
            foreach ($transportes as $transporte_antigo) {

                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                Escola_Util::log([
                    $qtd,
                    Escola_Util::progresso($percentual),
                    $transporte_antigo->codigo
                ]);

                // copiar concessao
                $concessao_nova = Escola_DbUtil::restaurarRegistro(
                    "concessao",
                    $transporte_antigo->id_concessao
                );

                // copiar transporte
                $transporte_novo = Escola_DbUtil::restaurarRegistro(
                    "transporte",
                    $transporte_antigo->id_transporte,
                    [
                        "id_concessao" => $concessao_nova->id_concessao
                    ]
                );

                // copiar pessoas
                $this->restaurarPessoas($transporte_antigo, $transporte_novo);

                // copiar veículos
                $this->restaurarVeiculos($transporte_antigo, $transporte_novo);

                // copiar serviços
                $this->restaurarServicos($transporte_antigo, $transporte_novo);
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex, false);
        }
    }

    private function restaurarServicos($transporte_antigo, $transporte_novo)
    {
        if (!$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar Serviços, Um dos transportes não definido!");
        }

        $dblegado = Zend_Registry::get("dblegado");

        $servicos_antigo = Escola_DbUtil::listar("
            select 
                s.descricao as servico, s.codigo as servico_codigo, 
                sss.descricao as status,
                tg.id_transporte_grupo, tg.descricao as transporte_grupo, tg.chave as transporte_grupo_chave,

                case 
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pf' then true
                    else false
                end as pf,

                case 
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pj' then true
                    else false
                end as pj,

                case 
                    when lower(ss.tipo) = 'tv' then true
                    else false
                end as veiculo,

                case 
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pf' then pf.cpf
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pj' then pj.cnpj
                    when lower(ss.tipo) = 'tv' then v.placa
                end as valor_recuperacao,

                case 
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pf' then pf.nome
                    when lower(ss.tipo) = 'tp' and lower(pt.chave) = 'pj' then pj.nome_fantasia
                    else null
                end as pessoa_nome,

                ss.*
            from servico_solicitacao ss
                inner join servico_transporte_grupo stg on ss.id_servico_transporte_grupo = stg.id_servico_transporte_grupo
                inner join servico s on stg.id_servico = s.id_servico
                inner join servico_solicitacao_status sss on ss.id_servico_solicitacao_status = sss.id_servico_solicitacao_status
                inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo

                left join transporte_pessoa tp on lower(ss.tipo) = 'tp' and ss.chave = tp.id_transporte_pessoa
                left join pessoa p on tp.id_pessoa = p.id_pessoa
                left join pessoa_fisica pf on pf.id_pessoa = p.id_pessoa
                left join pessoa_juridica pj on pj.id_pessoa = p.id_pessoa
                left join pessoa_tipo pt on p.id_pessoa_tipo = pt.id_pessoa_tipo

                left join transporte_veiculo tv on lower(ss.tipo) = 'tv' and ss.chave = tv.id_transporte_veiculo
                left join veiculo v on tv.id_veiculo = v.id_veiculo
                
            where (ss.id_transporte = ?)
            order by ss.ano_referencia, ss.codigo
        ", [$transporte_antigo->id_transporte], $dblegado);

        if (!$servicos_antigo) {
            return;
        }

        Escola_Util::log();
        Escola_Util::log("  Carregando serviços ... ");
        foreach ($servicos_antigo as $servico_antigo) {
            Escola_Util::log([
                [' ', 5],
                [$servico_antigo->codigo . " / " . $servico_antigo->ano_referencia, 15],
                $servico_antigo->servico,
                $servico_antigo->status
            ]);

            $servico_codigo = strtolower($servico_antigo->servico_codigo);
            switch ($servico_codigo) {
                case "cm":
                case "cp":
                    $servico_codigo = "ca";
                    break;
            }

            $tg_novo = Escola_DbUtil::first("
                select stg.*
                from servico_transporte_grupo stg
                    inner join transporte_grupo tg on stg.id_transporte_grupo = tg.id_transporte_grupo
                    inner join servico s on stg.id_servico = s.id_servico 
                where (lower(tg.chave) = lower(?))
                and (lower(s.codigo) = lower(?) )
            ", [$servico_antigo->transporte_grupo_chave, $servico_codigo]);

            if (!$tg_novo) {
                print_r([
                    "servico_antigo" => $servico_antigo
                ]);
                throw new Escola_Exception("Grupo de Transporte não identificado para o serviço!");
            }

            $valor_novo = Escola_DbUtil::restaurarRegistro("valor", $servico_antigo->id_valor);
            if (!$valor_novo) {
                print_r([
                    "servico_antigo" => $servico_antigo
                ]);
                throw new Escola_Exception("Falha ao gerar um novo valor para o serviço!");
            }

            $chave = $this->getServicoChave($servico_antigo, $transporte_antigo, $transporte_novo);

            if (!$chave) {
                throw new Escola_Exception("Falha ao Recuperar Chave");
            }

            $params = [
                ":data_solicitacao" => $servico_antigo->data_solicitacao,
                ":id_valor" => $valor_novo->id_valor,
                ":id_servico_solicitacao_status" => $servico_antigo->id_servico_solicitacao_status,
                ":id_servico_transporte_grupo" => $tg_novo->id_servico_transporte_grupo,
                ":data_validade" => $servico_antigo->data_validade,
                ":id_transporte" => $transporte_novo->id_transporte,
                ":data_inicio" => $servico_antigo->data_inicio,
                ":data_vencimento" => $servico_antigo->data_vencimento,
                ":ano_referencia" => $servico_antigo->ano_referencia,
                ":mes_referencia" => $servico_antigo->mes_referencia,
                ":codigo" => $servico_antigo->codigo,
                ":tipo" => $servico_antigo->tipo,
                ":chave" => $chave
            ];

            Escola_DbUtil::query("
                insert into servico_solicitacao
                (data_solicitacao, id_valor, id_servico_solicitacao_status, id_servico_transporte_grupo, data_validade, id_transporte, data_inicio, data_vencimento, ano_referencia, mes_referencia, codigo, tipo, chave)
                values
                (:data_solicitacao, :id_valor, :id_servico_solicitacao_status, :id_servico_transporte_grupo, :data_validade, :id_transporte, :data_inicio, :data_vencimento, :ano_referencia, :mes_referencia, :codigo, :tipo, :chave)
            ", $params);

            $id = Escola_DbUtil::lastInsertId();
            if (!$id) {
                print_r([
                    "servico_antigo" => $servico_antigo
                ]);
                throw new Escola_Exception("Falha ao salvar serviço!");
            }
        }
    }

    private function getServicoChave($servico_antigo, $transporte_antigo, $transporte_novo)
    {
        if (!$servico_antigo || !$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar chave do serviço, Um dos dados não definido!");
        }

        if (strtolower($servico_antigo->tipo) == "tr") {
            return $transporte_novo->id_transporte;
        }

        $chave = $this->pegaChavePf($servico_antigo, $transporte_antigo, $transporte_novo);
        if ($chave) {
            return $chave;
        }

        $chave = $this->pegaChavePj($servico_antigo, $transporte_antigo, $transporte_novo);
        if ($chave) {
            return $chave;
        }

        $chave = $this->pegaChaveVeiculo($servico_antigo, $transporte_antigo, $transporte_novo);
        if ($chave) {
            return $chave;
        }

        return null;
    }

    private function pegaChaveVeiculo($servico_antigo, $transporte_antigo, $transporte_novo)
    {
        if (!$servico_antigo || !$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar chave do serviço, Um dos dados não definido!");
        }

        if (!$servico_antigo->veiculo) {
            return null;
        }

        $tp = Escola_DbUtil::first("
            select tv.id_transporte_veiculo
            from transporte_veiculo tv
                inner join veiculo v on tv.id_veiculo = v.id_veiculo
            where (lower(v.placa) = lower(?))
            and (tv.id_transporte = ?)
        ", [$servico_antigo->valor_recuperacao, $transporte_novo->id_transporte]);

        if (!$tp) {
            throw new Escola_Exception("Falha ao recuperar chave do veículo!");
        }

        return $tp->id_transporte_veiculo;
    }

    private function pegaChavePf($servico_antigo, $transporte_antigo, $transporte_novo)
    {
        if (!$servico_antigo || !$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar chave do serviço, Um dos dados não definido!");
        }

        if (!$servico_antigo->pf) {
            return null;
        }

        $tp = Escola_DbUtil::first("
            select tp.id_transporte_pessoa
            from transporte_pessoa tp 
                inner join pessoa p on tp.id_pessoa = p.id_pessoa
                inner join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
            where (pf.cpf = ?)
            and (tp.id_transporte = ?)
        ", [$servico_antigo->valor_recuperacao, $transporte_novo->id_transporte]);

        if (!$tp) {
            throw new Escola_Exception("Falha ao recuperar chave da pessoa física!");
        }

        return $tp->id_transporte_pessoa;
    }

    private function pegaChavePj($servico_antigo, $transporte_antigo, $transporte_novo)
    {
        if (!$servico_antigo || !$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar chave do serviço, Um dos dados não definido!");
        }

        if (!$servico_antigo->pj) {
            return null;
        }

        $tp = Escola_DbUtil::first("
            select tp.id_transporte_pessoa
            from transporte_pessoa tp 
                inner join pessoa p on tp.id_pessoa = p.id_pessoa
                inner join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
            where (pj.cnpj = ?)
            and (tp.id_transporte = ?)
        ", [$servico_antigo->valor_recuperacao, $transporte_novo->id_transporte]);

        if (!$tp) {
            throw new Escola_Exception("Falha ao recuperar chave da pessoa jurídica!");
        }

        return $tp->id_transporte_pessoa;
    }

    private function restaurarVeiculos($transporte_antigo, $transporte_novo)
    {
        if (!$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar Veículos, Um dos transportes não definido!");
        }

        $dblegado = Zend_Registry::get("dblegado");

        $tvs_antigo = Escola_DbUtil::listar("
            select
                v.id_veiculo,
                v.placa,
                v.modelo,
                tvs.descricao as status,
                tv.*
            from transporte_veiculo tv 
                inner join veiculo v on tv.id_veiculo = v.id_veiculo
                inner join transporte_veiculo_status tvs on tv.id_transporte_veiculo_status = tvs.id_transporte_veiculo_status
            where (tv.id_transporte = ?)
        ", [$transporte_antigo->id_transporte], $dblegado);

        if (!$tvs_antigo) {
            return;
        }

        Escola_Util::log();
        Escola_Util::log("  Carregando veículos ... ");
        foreach ($tvs_antigo as $tv_antigo) {
            Escola_Util::log([
                [' ', 5],
                [$tv_antigo->placa, 15],
                $tv_antigo->modelo,
                $tv_antigo->status
            ]);

            $id = $this->criaTransporteVeiculo($tv_antigo, $transporte_novo);
            if (!$id) {
                throw new Escola_Exception("Falha ao Importar Transporte Veículo!");
            }
        }
    }

    private function criaTransporteVeiculo($tv_antigo, $transporte_novo)
    {
        if (!$tv_antigo || !$transporte_novo) {
            return null;
        }

        $id_veiculo = $this->criaVeiculo($tv_antigo);
        if (!$id_veiculo) {
            throw new Escola_Exception("Falha ao Importar Veículo!");
        }

        $tv = Escola_DbUtil::first("
            select tv.id_transporte_veiculo as id
            from transporte_veiculo tv
            where (tv.id_transporte = ?)
            and (tv.id_veiculo = ?) 
        ", [$transporte_novo->id_transporte, $id_veiculo]);

        if ($tv) {
            return $tv->id;
        }

        // cria transporte veiculo
        Escola_DbUtil::query("
            insert into transporte_veiculo 
            (id_transporte, id_veiculo, id_transporte_veiculo_status, data_cadastro, processo, processo_data)
            values 
            (:id_transporte, :id_veiculo, :id_transporte_veiculo_status, :data_cadastro, :processo, :processo_data)
        ", [
            ":id_transporte" => $transporte_novo->id_transporte,
            ":id_veiculo" => $id_veiculo,
            ":id_transporte_veiculo_status" => $tv_antigo->id_transporte_veiculo_status,
            ":data_cadastro" => $tv_antigo->data_cadastro,
            ":processo" => $tv_antigo->processo,
            ":processo_data" => $tv_antigo->processo_data
        ]);

        $id = Escola_DbUtil::lastInsertId();
        if (!$id) {
            print_r([
                "tv_antigo" => $tv_antigo
            ]);
            throw new Escola_Exception("Falha ao criar transporte veículo.");
        }

        return $id;
    }

    private function criaVeiculo($tv_antigo)
    {
        if (!$tv_antigo) {
            return null;
        }

        $veiculo = Escola_DbUtil::first("
            select v.id_veiculo
            from veiculo v
            where (lower(v.placa) = ?)
        ", [strtolower($tv_antigo->placa)]);

        if ($veiculo) {
            return $veiculo->id_veiculo;
        }

        //criando veículo
        // espero não utilizar essa funcionalidade agora
        throw new Escola_Exception("Processo de criação de veículo não implementado!");
    }

    private function restaurarPessoas($transporte_antigo, $transporte_novo)
    {
        if (!$transporte_antigo || !$transporte_novo) {
            throw new Escola_Exception("Falha ao recuperar Pessoas, Um dos transportes não definido!");
        }

        $dblegado = Zend_Registry::get("dblegado");

        $tps_antigo = Escola_DbUtil::listar("
            select
                p.id_pessoa, p.email,
                pt.id_pessoa_tipo, pt.chave as pt_chave,
                upper(tps.descricao) as status,
                upper(tpt.descricao) as tipo,

                case
                    when (lower(pt.chave) = 'pf') then true
                    when (lower(pt.chave) = 'pj') then false
                    else null
                end as pf,

                case
                    when (lower(pt.chave) = 'pf') then false
                    when (lower(pt.chave) = 'pj') then true
                    else null
                end as pj,

                case
                    when (lower(pt.chave) = 'pf') then pf.nome
                    when (lower(pt.chave) = 'pj') then pj.nome_fantasia
                    else null
                end as pessoa_nome,

                case
                    when (lower(pt.chave) = 'pf') then pf.cpf
                    when (lower(pt.chave) = 'pj') then pj.cnpj
                    else null
                end as pessoa_doc,

                tp.*

            from transporte_pessoa tp
                inner join transporte_pessoa_tipo tpt on tp.id_transporte_pessoa_tipo = tpt.id_transporte_pessoa_tipo
                inner join pessoa p on tp.id_pessoa = p.id_pessoa
                inner join pessoa_tipo pt on pt.id_pessoa_tipo = p.id_pessoa_tipo
                left join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
                left join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                left join transporte_pessoa_status tps on tp.id_transporte_pessoa_status = tps.id_transporte_pessoa_status
            
            where (tp.id_transporte = ?)
        ", [$transporte_antigo->id_transporte], $dblegado);

        if (!$tps_antigo) {
            return;
        }

        Escola_Util::log();
        Escola_Util::log("  Carregando pessoas ... ");
        foreach ($tps_antigo as $tp_antigo) {

            Escola_Util::log([
                [' ', 5],
                [$tp_antigo->tipo, 15],
                $tp_antigo->pessoa_doc,
                [$tp_antigo->pessoa_nome, 35],
                $tp_antigo->status
            ]);

            $tp_id = $this->criaTransportePessoa($tp_antigo, $transporte_novo);
            if (!$tp_id) {
                throw new Escola_Exception("Falha ao Importar Transporte Pessoa!");
            }
        }
    }

    private function criaTransportePessoa($tp_antigo, $transporte_novo)
    {
        if (!$tp_antigo) {
            return null;
        }

        $id_pessoa = $this->criaPessoa($tp_antigo);
        if (!$id_pessoa) {
            throw new Escola_Exception("Falha ao Importar Pessoa!");
        }

        $tp = Escola_DbUtil::first("
            select tp.id_transporte_pessoa
            from transporte_pessoa tp
            where (tp.id_transporte = ?)
            and (tp.id_pessoa = ?) 
        ", [$transporte_novo->id_transporte, $id_pessoa]);

        if ($tp) {
            return $tp->id_transporte_pessoa;
        }

        // cria transporte pessoa
        $tp = Escola_DbUtil::query("
            insert into transporte_pessoa 
            (id_pessoa, id_transporte_pessoa_tipo, id_transporte, id_transporte_pessoa_status)
            values 
            (:id_pessoa, :id_transporte_pessoa_tipo, :id_transporte, :id_transporte_pessoa_status)
        ", [
            ":id_pessoa" => $id_pessoa,
            ":id_transporte_pessoa_tipo" => $tp_antigo->id_transporte_pessoa_tipo,
            ":id_transporte" => $transporte_novo->id_transporte,
            ":id_transporte_pessoa_status" => $tp_antigo->id_transporte_pessoa_status
        ]);

        $id = Escola_DbUtil::lastInsertId();
        if (!$id) {
            print_r([
                "tp_antigo" => $tp_antigo
            ]);
            throw new Escola_Exception("Falha ao criar transporte pessoa.");
        }

        return $id;
    }

    private function criaPessoa($tp_antigo)
    {
        if (!$tp_antigo) {
            return null;
        }

        $sql = "";
        if ($tp_antigo->pf) {
            $sql = "
                select p.id_pessoa
                from pessoa p 
                    inner join pessoa_fisica pf on p.id_pessoa = pf.id_pessoa
                where (pf.cpf = ?)
            ";
        } elseif ($tp_antigo->pj) {
            $sql = "
                select p.id_pessoa
                from pessoa p 
                    inner join pessoa_juridica pj on p.id_pessoa = pj.id_pessoa
                where (pj.cnpj = ?)
            ";
        }

        if (!$sql) {
            print_r([
                "tp_antigo" => $tp_antigo
            ]);
            throw new Escola_Exception("Falha ao Buscar Pessoa!");
        }

        $pessoa = Escola_DbUtil::first($sql, [$tp_antigo->pessoa_doc]);

        if ($pessoa) {
            return $pessoa->id_pessoa;
        }

        //criando pessoa
        // espero não utilizar essa funcionalidade agora
        throw new Escola_Exception("Processo de criação de pessoa não implementado!");
    }
}

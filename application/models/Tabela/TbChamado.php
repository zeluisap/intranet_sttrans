<?php

class TbChamado extends Escola_Tabela {

    protected $_name = "chamado";
    protected $_rowClass = "Chamado";
    protected $_dependentTables = array("TbChamadoOcorrencia");
    protected $_referenceMap = array("ChamadoStatus" => array("columns" => array("id_chamado_status"),
            "refTableClass" => "TbChamadoStatus",
            "refColumns" => array("id_chamado_status")),
        "ChamadoTipo" => array("columns" => array("id_chamado_tipo"),
            "refTableClass" => "TbChamadoTipo",
            "refColumns" => array("id_chamado_tipo")),
        "Setor" => array("columns" => array("id_setor"),
            "refTableClass" => "TbSetor",
            "refColumns" => array("id_setor")),
        "Funcionario" => array("columns" => array("id_funcionario"),
            "refTableClass" => "TbFuncionario",
            "refColumns" => array("id_funcionario")));

    public function montaSQL($dados) {
        $db = Zend_Registry::get("db");
        $sql = $this->select();
        $sql->from(array("c" => "chamado"));
        if (isset($dados["filtro_tipo"]) && $dados["filtro_tipo"]) {
            $tb = new TbFuncionario();
            $funcionario = $tb->pegaLogado();
            if (!$funcionario) {
                throw new Exception("Falha ao Executar OperaÃ§Ã£o, Nenhum FuncionÃ¡rio Encontrado!");
            }
            $lotacao = $funcionario->pegaLotacaoAtual();
            if ($lotacao) {
                switch ($dados["filtro_tipo"]) {
                    case "cx":
                        $sql->join(array("ct" => "chamado_tipo"), "c.id_chamado_tipo = ct.id_chamado_tipo", array());
                        $sql->join(array("cts" => "chamado_tipo_setor"), "ct.id_chamado_tipo = cts.id_chamado_tipo", array());
                        $sql->join(array("s" => "setor"), "cts.id_setor = s.id_setor", array());
                        $sql->where("s.id_setor = {$lotacao->id_setor}");
                        break;
                    case "cx_p":
                        $sql->join(array("ct" => "chamado_tipo"), "c.id_chamado_tipo = ct.id_chamado_tipo", array());
                        $sql->join(array("cts" => "chamado_tipo_setor"), "ct.id_chamado_tipo = cts.id_chamado_tipo", array());
                        $sql->join(array("cs" => "chamado_status"), "c.id_chamado_status = cs.id_chamado_status", array());
                        $sql->join(array("s" => "setor"), "cts.id_setor = s.id_setor", array());
                        $sql->where("s.id_setor = {$lotacao->id_setor}");
                        $sql->where("cs.chave in ('P', 'E')");
                        break;
                    case "meus":
                        $sql->where("c.id_funcionario = " . $funcionario->getId());
                        break;
                    case "setor":
                        $sql->where("c.id_setor = " . $lotacao->id_setor);
                        break;
                }
            }
        }
        if (isset($dados["filtro_id_chamado_tipo"]) && $dados["filtro_id_chamado_tipo"]) {
            $sql->where("c.id_chamado_tipo = {$dados["filtro_id_chamado_tipo"]}");
        }
        if (isset($dados["filtro_id_chamado_status"]) && $dados["filtro_id_chamado_status"]) {
            $sql->where("c.id_chamado_status = {$dados["filtro_id_chamado_status"]}");
        }
        if (isset($dados["filtro_descricao_problema"]) && $dados["filtro_descricao_problema"]) {
            $sql->where("c.descricao_problema like '%{$dados["filtro_descricao_problema"]}%'");
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $sql_pf = $db->select();
            $sql_pf->from(array("f" => "funcionario"), array("id_funcionario"));
            $sql_pf->join(array("pf" => "pessoa_fisica"), "f.id_pessoa_fisica = pf.id_pessoa_fisica", array());
            $sql_pf->where("pf.nome like '%{$dados["filtro_nome"]}%'");
            $sql->where("c.id_funcionario in ({$sql_pf})");
        }
        if (isset($dados["filtro_setor"]) && $dados["filtro_setor"]) {
            $sql_setor = $db->select();
            $sql_setor->from(array("s" => "setor"), array("id_setor"));
            $sql_setor->where("s.sigla like '%{$dados["filtro_setor"]}%'");
            $sql_setor->orWhere("s.descricao like '%{$dados["filtro_setor"]}%'");
            $sql->where("c.id_setor in ({$sql_setor})");
        }
        $sql->order("c.data_criacao desc");
        $sql->order("c.hora_criacao desc");
        return $sql;
    }

    public function listarPorPagina($dados = array()) {
        $sql = $this->montaSQL($dados);
        $qtd_por_pagina = 50;
        if (isset($dados["qtd_por_pagina"]) && $dados["qtd_por_pagina"]) {
            $qtd_por_pagina = $dados["qtd_por_pagina"];
        }
        $adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
        //$adapter = new Zend_Paginator_Adapter_DbSelect($sql);
        $paginator = new Zend_Paginator($adapter);
        if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
            $paginator->setCurrentPageNumber($dados["pagina_atual"]);
        }
        $paginator->setItemCountPerPage($qtd_por_pagina);
        return $paginator;
    }

    public function listar($dados = array()) {
        $sql = $this->montaSQL($dados);
        $registros = $this->fetchAll($sql);
        if ($registros && count($registros)) {
            return $registros;
        }
        return false;
    }

    public static function pegaPendentes($funcionario) {
        $tb = new TbChamado();
        $registros = $tb->listar(array("filtro_tipo" => "cx_p"));
        return $registros;
    }

}
<?php

class TbLog extends Escola_Tabela_Log {

    protected $_name = "log";
    protected $_rowClass = "Log";
    protected $_dependentTables = array("TbLogCampo");
    protected $_referenceMap = array("LogOperacao" => array("columns" => array("id_log_operacao"),
            "refTableClass" => "TbLogOperacao",
            "refColumns" => array("id_log_operacao")));

    public function registraDelete(Zend_Db_Table_Row_Abstract $obj) {
        $this->registra("EXC", $obj);
    }

    public function registraInsert(Zend_Db_Table_Row_Abstract $obj, Zend_Db_Table_Row_Abstract $antes) {
        $this->registra("INS", $obj, $antes);
    }

    public function registraUpdate(Zend_Db_Table_Row_Abstract $obj, Zend_Db_Table_Row_Abstract $antes) {
        $this->registra("ALT", $obj, $antes);
    }

    public function registraLogin() {
        $row = $this->createRow();
        $dados = array();
        $tb_lo = new TbLogOperacao();
        $lo = $tb_lo->getPorChave("LOG");
        if ($lo) {
            $dados["id_log_operacao"] = $lo->id_log_operacao;
        }
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $dados["id"] = $identity->id_usuario;
        }
        $row->setFromArray($dados);
        $row->save();
    }

    public function registra($chave, Zend_Db_Table_Row_Abstract $obj, Zend_Db_Table_Row_Abstract $antes = null) {
        $row = $this->createRow();
        $dados = array();
        $dados["tabela"] = $obj->getTable()->info("name");
        $tb_lo = new TbLogOperacao();
        $lo = $tb_lo->getPorChave($chave);
        if ($lo) {
            $dados["id_log_operacao"] = $lo->id_log_operacao;
        }
        $dados["id"] = $obj->getId();
        $row->setFromArray($dados);
        $row->save();
        if ($antes) {
            $fields = array();
            $array_antes = $antes->getCleanData();
            $array_depois = $obj->toArray();
            foreach ($array_depois as $k => $v) {
                if (!$antes->getId()) {
                    $fields[$k] = array("valor_anterior" => "", "valor_depois" => $v);
                } elseif ($array_antes[$k] != $v) {
                    $fields[$k] = array("valor_anterior" => $array_antes[$k], "valor_depois" => $v);
                }
            }
            if (count($fields)) {
                $tb_campos = new TbLogCampo();
                foreach ($fields as $k => $v) {
                    $dados_campos = array("nome_campo" => $k,
                        "valor_anterior" => $v["valor_anterior"],
                        "valor_depois" => $v["valor_depois"],
                        "id_log" => $row->id_log);
                    $row_campos = $tb_campos->createRow();
                    $row_campos->setFromArray($dados_campos);
                    $row_campos->save();
                }
            }
        }
    }

    public function createRow() {
        $row = parent::createRow();
        $front = Zend_Controller_Front::getInstance();
        if ($front) {
            $request = $front->getRequest();
            if ($request) {
                $dados = array("ip" => $request->getServer("REMOTE_ADDR"));
            }
        }
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $tb_usuarios = new TbUsuario();
            $rows = $tb_usuarios->find($identity->id_usuario);
            if (count($rows)) {
                $usuario = $rows->current();
                $pf = $usuario->getPessoaFisica();
                $dados["cpf"] = $pf->cpf;
                $dados["nome"] = $pf->nome;
            }
        }
        $dt = new Zend_Date();
        $dados["data"] = $dt->toString("Y-M-d H:m:s");
        $row->setFromArray($dados);
        return $row;
    }

    public function listarLogin($usuario) {
        $tb = new TbLogOperacao();
        $row = $tb->getPorChave("LOG");
        if ($row) {
            $select = $this->select();
            $select->where("id_log_operacao = " . $row->id_log_operacao);
            $select->where("id = " . $usuario->id_usuario);
            $select->order("id_log desc");
            $rows = $this->fetchAll($select);
            if (count($rows)) {
                return $rows;
            }
        }
        return false;
    }

    public function listarPagina($dados) {
        $db = $this->getAdapter();
        $sql = $db->select();
        $sql->from(array("l" => "log"), array("id_log"));
        $sql->join(array("lo" => "log_operacao"), "l.id_log_operacao = lo.id_log_operacao", array());
        //$sql->joinLeft(array("pf" => "pessoa_fisica"), "l.cpf = pf.cpf", array());
        if (isset($dados["filtro_cpf"]) && $dados["filtro_cpf"]) {
            $filter = new Zend_Filter_Digits();
            $dados["filtro_cpf"] = $filter->filter($dados["filtro_cpf"]);
            if ($dados["filtro_cpf"]) {
                $sql->where("l.cpf = '" . $dados["filtro_cpf"] . "'");
            }
        }
        if (isset($dados["filtro_nome"]) && $dados["filtro_nome"]) {
            $sql->where("l.nome like '%" . $dados["filtro_nome"] . "%'");
        }
        if (isset($dados["filtro_id"]) && $dados["filtro_id"]) {
            $sql->where("l.id = {$dados["filtro_id"]}");
        }
        if (isset($dados["filtro_operacao"]) && $dados["filtro_operacao"]) {
            $sql->where("lo.descricao like '%" . $dados["filtro_operacao"] . "%'");
        }
        if (isset($dados["filtro_tabela"]) && $dados["filtro_tabela"]) {
            $sql->where("l.tabela like '" . $dados["filtro_tabela"] . "'");
        }
        if (isset($dados["filtro_data_inicio"]) && $dados["filtro_data_inicio"]) {
            $dados["filtro_data_inicio"] = Escola_Util::montaData($dados["filtro_data_inicio"]);
            $sql->where("l.data >= '" . $dados["filtro_data_inicio"] . "'");
        }
        if (isset($dados["filtro_data_final"]) && $dados["filtro_data_final"]) {
            $dados["filtro_data_final"] = Escola_Util::montaData($dados["filtro_data_final"]);
            $data = new Zend_Date($dados["filtro_data_final"]);
            $data->addDay(1);
            $sql->where("l.data <= '" . $data->toString("YYYY-MM-dd") . "'");
        }
        $sql->order("l.data desc");
        // $adapter = new Zend_Paginator_Adapter_DbTableSelect($sql);
        $adapter = new Zend_Paginator_Adapter_DbSelect($sql);
        $paginator = new Zend_Paginator($adapter);
        if (isset($dados["pagina_atual"]) && $dados["pagina_atual"]) {
            $paginator->setCurrentPageNumber($dados["pagina_atual"]);
        }
        $paginator->setItemCountPerPage(50);
        return $paginator;
    }

}
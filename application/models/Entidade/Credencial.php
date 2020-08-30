<?php

class Credencial extends Escola_Entidade {

    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->credencial_data = date("Y-m-d");
            $this->credencial_hora = date("H:i:s");
            $this->ano = date("Y");

            $tb = new TbCredencialStatus();
            $cs = $tb->getPorChave("P");
            if ($cs) {
                $this->id_credencial_status = $cs->getId();
            }
        }
    }

    public function getErrors() {
        $msgs = array();
        if (empty($this->id_credencial_tipo)) {
            $msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
        }
        if (empty($this->ano)) {
            $msgs[] = "CAMPO ANO OBRIGATÓRIO!";
        }
        if (empty($this->id_pessoa_fisica)) {
            $msgs[] = "CAMPO BENEFICIÁRIO OBRIGATÓRIO!";
        }
        if (empty($this->id_credencial_status)) {
            $msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
        }

        if (!count($msgs)) {
            $menor = false;
            $pf = $this->pegaBeneficiario();
            if ($pf) {
                if (Escola_Util::validaData($pf->data_nascimento)) {
                    $agora = new Zend_Date();

                    $nasc = new Zend_Date($pf->data_nascimento);
                    $nasc->add(18, Zend_Date::YEAR);

                    if ($agora->isEarlier($nasc)) {
                        $menor = true;
                    }
                }
            }
            if ($menor && empty($this->id_pessoa_fisica_responsavel)) {
                $msgs[] = "BENEFICIÁRIO MENOR DE 18 ANOS, CAMPO RESPONSÁVEL OBRIGATÓRIO!";
            }
        }

        $tb = $this->getTable();
        $id = "0";
        if ($this->getId()) {
            $id = $this->getId();
        }
        if (!count($msgs)) {

            $sql = $tb->select();
            $sql->from(array("c" => "credencial"));
            $sql->join(array("cs" => "credencial_status"), "c.id_credencial_status = cs.id_credencial_status", array());
            $sql->where("c.id_credencial_tipo = {$this->id_credencial_tipo}");
            $sql->where("c.id_pessoa_fisica = {$this->id_pessoa_fisica}");
            $sql->where("cs.chave in ('P')");
            $sql->where("c.id_credencial <> {$id}");
            $objs = $tb->fetchAll($sql);
            if ($objs && count($objs)) {
                $msg_errors = array();
                $obj = $objs->current();
                $ct = $obj->findParentRow("TbCredencialTipo");
                if ($ct) {
                    $msg_errors[] = "Tipo: " . $ct->toString();
                }
                if ($this->numero) {
                    $msg_errors[] = "Número: " . $obj->mostrarNumero();
                } else {
                    $msg_errors[] = "Ano: " . $obj->ano;
                }
                $pf = $obj->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $msg_errors[] = "Beneficiário: " . $pf->nome;
                }
                $cs = $obj->findParentRow("TbCredencialStatus");
                if ($cs) {
                    $msg_errors[] = "Status: " . $cs->toString();
                }
                $msgs[] = "Usuário Já vinculado a uma Credencial: ";
                $msgs[] = "<ul><li>" . implode("</li><li>", $msg_errors) . "</li></ul>";
            }
        }
        if (!count($msgs)) {
            $sql = $tb->select();
            $sql->from(array("c" => "credencial"));
            $sql->join(array("cs" => "credencial_status"), "c.id_credencial_status = cs.id_credencial_status", array());
            $sql->where("c.id_credencial_tipo = {$this->id_credencial_tipo}");
            $sql->where("c.id_pessoa_fisica = {$this->id_pessoa_fisica}");
            $sql->where("cs.chave in ('D')");
            $sql->where("c.data_validade >= CURRENT_DATE");
            $sql->where("c.id_credencial <> {$id}");
            $objs = $tb->fetchAll($sql);
            if ($objs && count($objs)) {
                $msg_errors = array();
                $obj = $objs->current();
                $ct = $obj->findParentRow("TbCredencialTipo");
                if ($ct) {
                    $msg_errors[] = "Tipo: " . $ct->toString();
                }
                if ($this->numero) {
                    $msg_errors[] = "Número: " . $obj->mostrarNumero();
                } else {
                    $msg_errors[] = "Ano: " . $obj->ano;
                }
                $pf = $obj->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $msg_errors[] = "Beneficiário: " . $pf->nome;
                }
                $cs = $obj->findParentRow("TbCredencialStatus");
                if ($cs) {
                    $msg_errors[] = "Status: " . $cs->toString();
                }
                $msgs[] = "Usuário Já vinculado a uma Credencial Ativa: ";
                $msgs[] = "<ul><li>" . implode("</li><li>", $msg_errors) . "</li></ul>";
            }
        }

        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function save($flag = false) {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }

        try {
            $id = $this->getId();

            if (empty($this->id_pessoa_fisica_responsavel)) {
                $this->id_pessoa_fisica_responsavel = null;
            }

            if (!$this->numero) {
                $ct = $this->findParentRow("TbCredencialTipo");
                if (!$ct) {
                    throw new Exception("Falha ao Executar Operação, Número da Credencial não Gerado!");
                }
                $this->numero = TbCredencial::geraNumero($ct->getId(), $this->ano);
            }

            $return = parent::save($flag);

            //novo registro - gerar ocorrência de criação.
            if (!$id) {
                $tb = new TbCredencialOcorrenciaTipo();
                $cot = $tb->getPorChave("C");
                if (!$cot) {
                    $tb->recuperar();
                }
                $cot = $tb->getPorChave("C");
                if (!$cot) {
                    throw new Exception("Falha ao Executar Operação, Tipo de Ocorrência de Criação de Credencial Não Cadastrado!");
                }

                $tb = new TbUsuario();
                $usuario = $tb->pegaLogado();
                if (!$usuario) {
                    throw new Exception("Falha ao Executar Operação, Nenhum Usuário Logado!");
                }

                $dados = array();
                $dados["id_credencial_ocorrencia_tipo"] = $cot->getId();
                $dados["id_credencial"] = $this->getId();
                $dados["id_usuario"] = $usuario->getId();

                $tb = new TbCredencialOcorrencia();
                $co = $tb->createRow();
                $co->setFromArray($dados);
                $errors = $co->getErrors();
                if ($errors) {
                    throw new Exception("Erros ao Salvar Ocorrência de Credencial: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
                }
                $co->save();
            }

            if ($in_trans) {
                $db->commit();
            }

            return $return;
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

    public function pegaBeneficiario() {
        if (!$this->id_pessoa_fisica) {
            return false;
        }

        $pf = TbPessoaFisica::pegaPorId($this->id_pessoa_fisica);

        if (!$pf) {
            return false;
        }

        return $pf;
    }

    public function pegaResponsavel() {
        if (!$this->id_pessoa_fisica_responsavel) {
            return false;
        }

        $pf = TbPessoaFisica::pegaPorId($this->id_pessoa_fisica_responsavel);

        if (!$pf) {
            return false;
        }

        return $pf;
    }

    public function mostrarNumero() {
        $txt = array();
        if (!$this->numero) {
            return "";
        }

        return str_pad($this->numero, 4, "0", STR_PAD_LEFT) . "/" . $this->ano;
    }

    public function pegaOcorrencias() {
        if (!$this->getId()) {
            return false;
        }

        $tb = new TbCredencialOcorrencia();
        $objs = $tb->listar(array("filtro_id_credencial" => $this->getId()));
        if (!$objs) {
            return false;
        }

        if (!count($objs)) {
            return false;
        }

        return $objs;
    }

    public function getDeleteErrors() {
        $errors = array();

        if ($this->getId()) {
            try {
                $ct = $this->findParentRow("TbCredencialStatus");
                if (!$ct) {
                    throw new Exception("Tipo da Credencial Não Informado!");
                }

                if (!$ct->pendente()) {
                    throw new Exception("Credencial Não Está com Status PENDENTE!");
                }

                $usuario = TbUsuario::pegaLogado();
                if (!$usuario) {
                    throw new Exception("Nenhum Usuário Logado!");
                }
                if (!$usuario->administrador()) {
                    throw new Exception("Solicitação de Credencial só Poderá ser Excluída Pelo Usuário que Efetuou o Cadastro ou por um Administrador!");
                }
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        if (!count($errors)) {
            return false;
        }

        return $errors;
    }

    public function delete() {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }

        try {

            $objs = $this->pegaOcorrencias();
            if ($objs) {
                foreach ($objs as $obj) {
                    $obj->delete();
                }
            }

            $return = parent::delete();

            if ($in_trans) {
                $db->commit();
            }

            return $return;
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

    public function deferir($data_validade = false) {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }
        try {

            if (!$this->getId()) {
                throw new Exception("Falha ao Deferir Credencial, Credencial Inválida!");
            }

            $cs = $this->findParentRow("TbCredencialStatus");
            if (!$cs) {
                throw new Exception("Falha ao Deferir Credencial, Status da Credencial Nao Encontrado!");
            }

            if (!$cs->pendente()) {
                throw new Exception("Falha ao Deferir Credencial, Credencial Não Está Pendente!");
            }
            $tb = new TbCredencial();
            $sql = $tb->select();
            $sql->from(array("c" => "credencial"));
            $sql->join(array("cs" => "credencial_status"), "c.id_credencial_status = cs.id_credencial_status", array());
            $sql->where("c.id_credencial_tipo = {$this->id_credencial_tipo}");
            $sql->where("c.id_pessoa_fisica = {$this->id_pessoa_fisica}");
            $sql->where("cs.chave in ('D')");
            $sql->where("c.data_validade >= CURRENT_DATE");
            $sql->where("c.id_credencial <> {$this->getId()}");
            $objs = $tb->fetchAll($sql);
            if ($objs && count($objs)) {
                $msg_errors = array();
                $obj = $objs->current();
                $ct = $obj->findParentRow("TbCredencialTipo");
                if ($ct) {
                    $msg_errors[] = "Tipo: " . $ct->toString();
                }
                if ($this->numero) {
                    $msg_errors[] = "Número: " . $obj->mostrarNumero();
                } else {
                    $msg_errors[] = "Ano: " . $obj->ano;
                }
                $pf = $obj->findParentRow("TbPessoaFisica");
                if ($pf) {
                    $msg_errors[] = "Beneficiário: " . $pf->nome;
                }
                $cs = $obj->findParentRow("TbCredencialStatus");
                if ($cs) {
                    $msg_errors[] = "Status: " . $cs->toString();
                }
                $msgs[] = "Usuário Já vinculado a uma Credencial Ativa: ";
                $msgs[] = "<ul><li>" . implode("</li><li>", $msg_errors) . "</li></ul>";
            }

            if (empty($data_validade)) {
                $data = new Zend_Date();
                $data->add(1, Zend_Date::YEAR);
                $data_validade = $data->toString("yyyy-MM-dd");
            } else {
                $data_validade = Escola_Util::montaData($data_validade);
            }

            if (!Escola_Util::validaData($data_validade)) {
                throw new Exception("Falha ao Deferir Credencial, Data de Validade Inválida {$data_validade}!");
            }

            $tb = new TbCredencialStatus();
            $cs = $tb->getPorChave("D");
            if (!$cs) {
                throw new Exception("Falha ao Deferir Credencial, Status de Deferimento Não Disponível!");
            }

            $this->id_credencial_status = $cs->getId();
            $errors = $this->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Deferir Credencial, <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }

            $this->data_validade = $data_validade;
            $this->save();

            //ocorrência de deferimento
            $tb = new TbCredencialOcorrenciaTipo();
            $cot = $tb->getPorChave("D");
            if (!$cot) {
                throw new Exception("Falha ao Deferir Credencial, Tipo de Ocorrência de Deferimento de Credencial Não Cadastrado!");
            }

            $tb = new TbUsuario();
            $usuario = $tb->pegaLogado();
            if (!$usuario) {
                throw new Exception("Falha ao Deferir Credencial, Nenhum Usuário Logado!");
            }

            $dados = array();
            $dados["id_credencial_ocorrencia_tipo"] = $cot->getId();
            $dados["id_credencial"] = $this->getId();
            $dados["id_usuario"] = $usuario->getId();

            $tb = new TbCredencialOcorrencia();
            $co = $tb->createRow();
            $co->setFromArray($dados);
            $errors = $co->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Deferir Credencial. Erros ao Salvar Ocorrência de Credencial: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $co->save();

            if ($in_trans) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }

            throw $ex;
        }
    }

    public function indeferir($justificativa) {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }
        try {

            $cs = $this->findParentRow("TbCredencialStatus");
            if (!$cs) {
                throw new Exception("Falha ao Indeferir Credencial, Status da Credencial Nao Encontrado!");
            }

            if (!$cs->pendente()) {
                throw new Exception("Falha ao Indeferir Credencial, Credencial Não Está Pendente!");
            }

            if (empty($justificativa)) {
                throw new Exception("Falha ao Indeferir Credencial, Justificativa Obrigatória!");
            }

            $tb = new TbCredencialStatus();
            $cs = $tb->getPorChave("I");
            if (!$cs) {
                throw new Exception("Falha ao Indeferir Credencial, Status de Indeferimento Não Disponível!");
            }

            $this->id_credencial_status = $cs->getId();
            $errors = $this->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Indeferir Credencial, <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }

            $this->save();

            // ocorrência de indeferimentos
            $tb = new TbCredencialOcorrenciaTipo();
            $cot = $tb->getPorChave("I");
            if (!$cot) {
                throw new Exception("Falha ao Indeferir Credencial, Tipo de Ocorrência de Indeferimento de Credencial Não Cadastrado!");
            }

            $tb = new TbUsuario();
            $usuario = $tb->pegaLogado();
            if (!$usuario) {
                throw new Exception("Falha ao Indeferir Credencial, Nenhum Usuário Logado!");
            }

            $dados = array();
            $dados["id_credencial_ocorrencia_tipo"] = $cot->getId();
            $dados["id_credencial"] = $this->getId();
            $dados["id_usuario"] = $usuario->getId();
            $dados["obs"] = "Justificativa do Indeferimento: {$justificativa}.";

            $tb = new TbCredencialOcorrencia();
            $co = $tb->createRow();
            $co->setFromArray($dados);
            $errors = $co->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Indeferir Credencial. Erros ao Salvar Ocorrência de Credencial: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $co->save();

            if ($in_trans) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }

            throw $ex;
        }
    }

    public function cancelar_resposta($justificativa) {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }
        try {
            $cs = $this->findParentRow("TbCredencialStatus");
            if (!$cs) {
                throw new Exception("Falha ao Executar OPeração, Status da Credencial Não Encontrado!");
            }

            if ($cs->pendente()) {
                throw new Exception("Falha ao Executar OPeração, Credencial Não Respondida!");
            }

            $tb = new TbCredencialStatus();
            $cs = $tb->getPorChave("P");
            if (!$cs) {
                throw new Exception("Falha ao Executar OPeração, Status da Credencial Não Encontrado!");
            }

            if (!$justificativa) {
                throw new Exception("Falha ao Executar OPeração, Justificativa Obrigatória no Cancelamento da Credencial!");
            }

            $this->id_credencial_status = $cs->getId();
            $this->data_validade = null;
            $erros = $this->getErrors();
            if ($erros) {
                throw new Exception("Falha ao Cancelar Resposta de Credencial, <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $this->save();

            //ocorrência de deferimento
            $tb = new TbCredencialOcorrenciaTipo();
            $cot = $tb->getPorChave("CA");
            if (!$cot) {
                throw new Exception("Falha ao Cancelar Resposta de Credencial, Tipo de Ocorrência de Cancelamento de Credencial Não Cadastrado!");
            }

            $tb = new TbUsuario();
            $usuario = $tb->pegaLogado();
            if (!$usuario) {
                throw new Exception("Falha ao Cancelar Resposta de Credencial, Nenhum Usuário Logado!");
            }

            $dados = array();
            $dados["id_credencial_ocorrencia_tipo"] = $cot->getId();
            $dados["id_credencial"] = $this->getId();
            $dados["id_usuario"] = $usuario->getId();
            $dados["obs"] = "Motivo do Cancelamento: {$justificativa}";

            $tb = new TbCredencialOcorrencia();
            $co = $tb->createRow();
            $co->setFromArray($dados);
            $errors = $co->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Cancelar Resposta de  Credencial. Erros ao Salvar Ocorrência de Credencial: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $co->save();

            if ($in_trans) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

    public function deferido() {
        $cs = $this->findParentRow("TbCredencialStatus");
        if (!$cs) {
            return false;
        }

        return $cs->deferido();
    }

    public function imprimir() {
        if (!$this->getId()) {
            throw new Exception("Falha ao Executar Operação, Registro de Credencial Não Existe!");
        }

        if (!$this->deferido()) {
            throw new Exception("Falha ao Executar Operação, Credencial Não Deferida!");
        }

        if ($this->vencida()) {
            throw new Exception("Falha ao Executar Operação, Credencial Vencida!");
        }


        $ct = $this->findParentRow("TbCredencialTipo");
        if (!$ct) {
            throw new Exception("Falha ao Executar Operação, Tipo da Credencial Não Encontrado!");
        }
        $obj = false;
        if ($ct->idoso()) {
            $obj = new Escola_Relatorio_Credencial_Idoso();
        } elseif ($ct->deficiente()) {
            $obj = new Escola_Relatorio_Credencial_Deficiente();
        }

        if (!$obj) {
            throw new Exception("Falha ao Executar Operação, Dados Inválidos!");
        }

        $obj->setCredencial($this);
        $obj->toPDF();
    }

    public function vencida() {
        $numero = Escola_Util::limpaNumero($this->data_validade);
        if (empty($numero)) {
            return null;
        }
        $hoje = date("Ymd");
        $validade = date("Ymd", strtotime($this->data_validade));
        return ($hoje > $validade);
    }

    public function getStatus() {
        $status = $this->findParentRow("TbCredencialStatus");
        if (!$status) {
            return null;
        }
        $txt = array();
        $txt[] = $status->toString();

        if ($status->deferido() && $this->vencida()) {
            $txt[] = "VENCIDA";
        }

        return implode(" - ", $txt);
    }

    public function renovar($anos) {
        $in_trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $in_trans = false;
        }
        try {
            if (!$this->deferido()) {
                throw new Exception("Falha ao Renovar Credencial, Não Deferido!"); 
            }
            if (!$this->vencida()) {
                throw new Exception("Falha ao Renovar Credencial, Não Vencida!"); 
            }
            if (!trim($anos)) {
                throw new Exception("Falha ao Renovar Credencial, Nenhum Ano Informado!"); 
            }
            if (!is_numeric($anos)) {
                throw new Exception("Falha ao Renovar Credencial, Valor da Validade Inválido!"); 
            }
            
            $validade_antiga = $this->data_validade;
                        
            $validade = new DateTime();
            $validade->add(new DateInterval("P{$anos}Y"));
            $mes_atual = $validade->format("m");
            do {
                $validade->add(new DateInterval("P1D"));    
            } while ($mes_atual == $validade->format("m"));
            $validade->sub(new DateInterval("P1D"));
            
            $validade_nova = $validade->format("Y-m-d");
                        
            $this->data_validade = $validade_nova;
            $erros = $this->getErrors();
            if ($erros) {
                throw new Exception("Falha ao Cancelar Credencial, <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $this->save();

            //ocorrência de deferimento
            $tb = new TbCredencialOcorrenciaTipo();
            $cot = $tb->getPorChave("R");
            if (!$cot) {
                throw new Exception("Falha ao Renovar Credencial, Tipo de Ocorrência de Renovação de Credencial Não Cadastrado!");
            }

            $tb = new TbUsuario();
            $usuario = $tb->pegaLogado();
            if (!$usuario) {
                throw new Exception("Falha ao Renovar Credencial, Nenhum Usuário Logado!");
            }
            
            $dt_antiga = (new DateTime($validade_antiga))->format("d/m/Y");
            $dt_nova = (new DateTime($validade_nova))->format("d/m/Y");

            $dados = array();
            $dados["id_credencial_ocorrencia_tipo"] = $cot->getId();
            $dados["id_credencial"] = $this->getId();
            $dados["id_usuario"] = $usuario->getId();
            $dados["obs"] = "Credencial Renovada. Validade Antiga: {$dt_antiga}, Validade Nova: {$dt_nova}.";

            $tb = new TbCredencialOcorrencia();
            $co = $tb->createRow();
            $co->setFromArray($dados);
            $errors = $co->getErrors();
            if ($errors) {
                throw new Exception("Falha ao Renovar Credencial. Erros ao Salvar Ocorrência de Credencial: <ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
            }
            $co->save();
            
            if ($in_trans) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if ($in_trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }

}
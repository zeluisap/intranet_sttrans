<?php
class RequerimentoJari extends Escola_Entidade {
    
    public function pegaRequerimentoJariStatus() {
        $obj = $this->findParentRow("TbRequerimentoJariStatus");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
    
    public function pegaAutoInfracaoNotificacao() {
        $obj = $this->findParentRow("TbAutoInfracaoNotificacao");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
    
    public function pegaDocumento() {
        $obj = $this->findParentRow("TbDocumento");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_jari = date("Y-m-d");
            $this->hora_jari = date("H:i:s");
            $tb = new TbRequerimentoJariStatus();
            $vrs = $tb->getPorChave("AR");
            if ($vrs) {
                $this->id_requerimento_jari_status = $vrs->getId();
            }
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!$this->id_auto_infracao_notificacao) {
            $msgs[] = "CAMPO NOTIFICAÇÃO DE AUTO DE INFRAÇÃO OBRIGATÓRIO!";
        }                
        if (!$this->id_documento) {
            $msgs[] = "CAMPO DOCUMENTO OBRIGATÓRIO!";
        }                
        if (!Escola_Util::limpaNumero($this->data_jari)) {
            $msgs[] = "CAMPO DATA DO REQUERIMENTO JARI OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_jari)) {
            $msgs[] = "CAMPO DATA DO REQUERIMENTO JARI INVÁLIDO!";
        }
        if (!$this->id_requerimento_jari_status) {
            $msgs[] = "CAMPO STATUS DE REQUERIMETO JARI OBRIGATÓRIO!";
        }
        if ($this->aguardando_resposta()) {
            $tb = new TbRequerimentoJari();
            $sql = $tb->select();
            $sql->where("id_auto_infracao_notificacao = {$this->id_auto_infracao_notificacao}");
            $sql->where("id_requerimento_jari_status = {$this->id_requerimento_jari_status}");
//            $sql->where("id_documento <> {$this->id_documento}");
            $sql->where("id_requerimento_jari <> {$this->getId()}");
            $rs = $tb->fetchAll($sql);
            if ($rs && count($rs)) {
                $rj = $rs->current();
                $msgs[] = "JÁ EXISTE UM REQUERIMENTO JARI AGUARDANDO RESPOSTA PARA ESTA NOTIFICAÇÃO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function getDeleteErrors() {
        $erros = array();
        if (!$this->aguardando_resposta()) {
            $erros[] = "REQUERIMENTO JARI JÁ RESPONDIDO, CANCELE A RESPOSTA ANTES DE EXCLUIR!";
        }
        if (count($erros)) {
            return $erros;
        }
        return false;
    }
    
    public function delete() {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $e) {
            $trans = false;
        }
        try {
            $doc = $this->findParentRow("TbDocumento");
            $return = parent::delete();
            if ($doc && $doc->getId()) {
                $errors = $doc->getDeleteErrors();
                if (!$errors) {
                    $doc->delete();
                } else {
                    throw new Exception(implode("<br>", $errors));
                }
            }
            if ($trans) {
                $db->commit();
            }
            return $return;
        } catch (Exception $e) {
            if ($trans) {
                $db->rollBack();
            }
            throw $e;
        }
    }
    
    public function aguardando_resposta() {
        $rjs = $this->findParentRow("TbRequerimentoJariStatus");
        if ($rjs) {
            return $rjs->aguardando_resposta();
        }
        return false;
    }
    
    public function respondido() {
        return (!$this->aguardando_resposta());
    }
    
    public function habilitaResponder() {
        if ($this->aguardando_resposta()) {
            return true;
        }
        return false;
    }
    
    public function deferimento_total() {
        $rjs = $this->findParentRow("TbRequerimentoJariStatus");
        if ($rjs) {
            return $rjs->deferimento_total();
        }
        return false;
    }
    
    public function deferimento_parcial() {
        $rjs = $this->findParentRow("TbRequerimentoJariStatus");
        if ($rjs) {
            return $rjs->deferimento_parcial();
        }
        return false;
    }
    
    public function pegaResposta() {
        $tb = new TbRequerimentoJariResposta();
        $rjrs = $tb->listar(array("id_requerimento_jari" => $this->getId()));
        if ($rjrs && count($rjrs)) {
            return $rjrs;
        }
        return false;
    }
    
    public function responder($dados) {
        if (!$this->getId()) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
        }
        $dados["id_requerimento_jari"] = $this->getId();
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $rjr = $this->pegaResposta();
            if ($rjr) {
                throw new Exception("REQUERIMENTO JARI JÁ RESPONDIDO");
            }
            if (isset($dados["id_requerimento_jari_status"])) {
                $this->id_requerimento_jari_status = $dados["id_requerimento_jari_status"];
                $this->save();
            }
            $funcionario = false;
            if (!isset($dados["id_funcionario"]) && $dados["id_funcionario"]) {
                $funcionario = TbFuncionario::pegaPorId($dados["id_funcionario"]);
            }
            if (!$funcionario) {
                $tb = new TbFuncionario();
                $funcionario = $tb->pegaLogado();
            }
            if ($funcionario && $funcionario->getId()) {
                $dados["id_funcionario"] = $funcionario->getId();
            }
            $tb = new TbRequerimentoJariResposta();
            $rjr = $tb->createRow();
            $rjr->setFromArray($dados);
            $errors = $rjr->getErrors();
            if ($errors) {
                throw new Exception(implode("<br>", $errors));
            }
            $rjr->save();
            $doc = $this->pegaDocumento();
            if ($doc) {
                $dados = array();
                if ($funcionario) {
                    $dados["id_funcionario"] = $funcionario->getId();
                }
                $setor = $doc->pegaSetorAtual();
                if ($setor) {
                    $dados["id_setor"] = $setor->getId();
                }
                $rjs = $this->pegaRequerimentoJariStatus();
                if ($rjs) {
                    $dados["despacho"] = "Requerimento JARI Respondido com Situação: {$rjs->toString()}.";
                }
                $doc->arquivar($dados);
            }
            //deferimento total - cancelamento da notificação
            if ($this->deferimento_total()) {
                $ain = $this->findParentRow("TbAutoInfracaoNotificacao");
                if ($ain) {
                    $ain->cancelar();
                }
            }
            if ($trans) {
                $db->commit();
            }
            return true;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
    
    public function cancelar_resposta() {
        $trans = true;
        $db = Zend_Registry::get("db");
        if (!$this->getId()) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
        }
        if (!$this->respondido()) {
            throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, REQUERIMENTO JARI NÃO RESPONDIDO!");
        }
        $ss = false;
        $ain = $this->findParentRow("TbAutoInfracaoNotificacao");
        if ($ain) {
            $ss = $ain->pegaServicoSolicitacao();
            if ($ss->pago()) {
                throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, NOTIFICAÇÃO COM PAGAMENTO CONFIRMADO!");
            }
        } else {
            throw new Exception("FALHA AO EXECUTAR OPERAÇÃO, DADOS INVÁLIDOS!");
        }
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $tb = new TbRequerimentoJariStatus();
            $rjs = $tb->getPorChave("AR");
            if ($rjs) {
                $this->id_requerimento_jari_status = $rjs->getId();
                $this->save();
            }
            if ($ss) {
                $tb = new TbServicoSolicitacaoStatus();
                $sss = $tb->getPorChave("AG");
                if ($sss) {
                    $ss->id_servico_solicitacao_status = $sss->getId();
                    $ss->save();
                    $valor_total = $ss->pega_valor();
                    if ($valor_total) {
                        $valor_total->valor = $ain->pegaValorTotal();
                        $valor_total->save();
                    }
                }
            }
            $rjrs = $this->pegaResposta();
            if ($rjrs) {
                foreach ($rjrs as $rjr) {
                    $rjr->delete();
                }
            }
            $doc = $this->pegaDocumento();
            if ($doc) {
                if ($doc->arquivado()) {
                    $dados = array();
                    $dados["despacho"] = "Arquivamento Cancelado pois a Resposta do Requerimento foi Excluída.";
                    $doc->cancelar_arquivar($dados);;
                }
            }
            if ($trans) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
}
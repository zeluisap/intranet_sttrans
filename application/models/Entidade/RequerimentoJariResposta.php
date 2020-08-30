<?php
class RequerimentoJariResposta extends Escola_Entidade {
    
    protected $_infracoes = array();
    
    public function pegaFuncionario() {
        $funcionario = $this->findParentRow("TbFuncionario");
        if ($funcionario && $funcionario->getId()) {
            return $funcionario;
        }
        return false;
    }
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data_resposta = date("Y-m-d");
        }
        $this->_infracoes = array();
        $infracoes = $this->listarInfracoes();
        if ($infracoes) {
            $this->_infracoes = $infracoes;
        }
    }
    
    public function pegaRequerimentoJari() {
        $rj = $this->findParentRow("TbRequerimentoJari");
        if ($rj && $rj->getId()) {
            return $rj;
        }
        return false;
    }
    
    public function addInfracao($infracao) {
        if (count($this->_infracoes)) {
            foreach ($this->_infracoes as $inf) {
                if ($infracao->getId() == $inf->getId()) {
                    return false;
                }
            }
        }
        $this->_infracoes[] = $infracao;
        return true;
    }
    
    public function listarInfracoes() {
        if ($this->getId()) {
            $tb = new TbInfracao();
            $sql = $tb->select();
            $sql->from(array("i" => "infracao"));
            $sql->join(array("rjri" => "requerimento_jari_resposta_infracao"), "i.id_infracao = rjri.id_infracao", array());
            $sql->where("rjri.id_requerimento_jari_resposta = {$this->getId()}");
            $sql->order("i.codigo");
            $infracaos = $tb->fetchAll($sql);
            if ($infracaos && count($infracaos)) {
                return $infracaos;
            }
        }
        return false;
    }
    
    public function setFromArray(array $dados) {
        parent::setFromArray($dados);
        if (isset($dados["id_infracao"]) && is_array($dados["id_infracao"]) && count($dados["id_infracao"])) {
            foreach ($dados["id_infracao"] as $id_infracao) {
                $infracao = TbInfracao::pegaPorId($id_infracao);
                if ($infracao && $infracao->getId()) {
                    $this->addInfracao($infracao);
                }
            }
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!$this->id_requerimento_jari) {
            $msgs[] = "CAMPO REQUERIMENTO JARI OBRIGATÓRIO!";
        }                
        if (!$this->id_funcionario) {
            $msgs[] = "CAMPO FUNCIONÁRIO OBRIGATÓRIO!";
        }                
        if (!Escola_Util::limpaNumero($this->data_resposta)) {
            $msgs[] = "CAMPO DATA DE RESPOSTA DO REQUERIMENTO JARI OBRIGATÓRIO!";
        } elseif (!Escola_Util::validaData($this->data_resposta)) {
            $msgs[] = "CAMPO DATA DE RESPOSTA DO REQUERIMENTO JARI INVÁLIDO!";
        }
        if (!$this->getId()) {
            $rj = $this->findParentRow("TbRequerimentoJari");
            if ($rj) {
                $ain = $rj->findParentRow("TbAutoInfracaoNotificacao");
                if ($ain) {
                    if ($ain->pendente()) {
                        $rjs = $rj->findParentRow("TbRequerimentoJariStatus");
                        if ($rjs) {
                            if ($rjs->indeferido() || $rjs->deferimento_parcial()) {
                                if (!$this->observacao) {
                                    $msgs[] = "CAMPO JUSTIFICATIVA OBRIGATÓRIO PARA O TIPO: {$rjs->toString()}!";
                                }
                                if ($rjs->deferimento_parcial() && !count($this->_infracoes)) {
                                    $msgs[] = "CAMPO INFRAÇÃO SELECIONADA PARA O TIPO: {$rjs->toString()}!";
                                }
                            }
                        } else {
                            $msgs[] = "STATUS DO REQUERIMENTO NÃO DEFINIDO!";
                        }
                    } else {
                        $msgs[] = "NOTIFICAÇÃO JÁ PAGA!";
                    }
                }
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function saveInfracao() {
        $db = Zend_Registry::get("db");
        if ($this->getId()) {
            foreach ($this->_infracoes as $inf) {
                $sql = $db->select();
                $sql->from(array("requerimento_jari_resposta_infracao"));
                $sql->where("id_requerimento_jari_resposta = {$this->getId()}");
                $sql->where("id_infracao = {$inf->getId()}");
                $stmt = $db->query($sql);
                if (!$stmt || !$stmt->rowCount()) {
                    $sql = "insert into requerimento_jari_resposta_infracao
                            (id_requerimento_jari_resposta, id_infracao)
                            values
                            ({$this->getId()}, {$inf->getId()})";
                    $db->query($sql);
                }
            }
        }
    }
    
    public function save() {
        $trans = true;
        $db = Zend_Registry::get("db");
        try {
            $db->beginTransaction();
        } catch (Exception $ex) {
            $trans = false;
        }
        try {
            $return_id = parent::save();
            $this->saveInfracao();
            $rj = $this->pegaRequerimentoJari();
            if ($rj) {
                $ain = $rj->pegaAutoInfracaoNotificacao();
                if ($ain) {
                    $valor_pagar = $ain->pegaValorPagar();
                    $ss = $ain->pegaServicoSolicitacao();
                    if ($ss) {
                        $valor_ss = $ss->pega_valor();
                        if ($valor_pagar != $valor_ss->valor) {
                            $valor_ss->valor = $valor_pagar;
                            $valor_ss->save();
                        }
                    }
                }
            }
            if ($trans) {
                $db->commit();
            }
            return $return_id;
        } catch (Exception $ex) {
            if ($trans) {
                $db->rollBack();
            }
            throw $ex;
        }
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
            $sql = "delete 
                    from requerimento_jari_resposta_infracao 
                    where (id_requerimento_jari_resposta = {$this->getId()})";
            $db->query($sql);
            $return = parent::delete();
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
    
    public function pegaValorTotal() {
        $infracaos = $this->listarInfracoes();
        if ($infracaos) {
            $valor_total = 0;
            foreach ($infracaos as $infracao) {
                $valor = $infracao->pega_valor();
                if ($valor) {
                    $valor_total += $valor->valor;
                }
            }
            return $valor_total;
        }
        return 0;
    }
}
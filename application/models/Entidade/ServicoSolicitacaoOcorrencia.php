<?php
class ServicoSolicitacaoOcorrencia extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $hoje = new Zend_Date();
            $this->ocorrencia_data = date("Y-m-d");
            $this->ocorrencia_hora = date("H:i:s");
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_servico_solicitacao)) {
            $msgs[] = "CAMPO SERVIÇO DE SOLICITAÇÃO OBRIGATÓRIO!";
        }
        if (!trim($this->id_servico_solicitacao_ocorrencia_tipo)) {
            $msgs[] = "CAMPO TIPO DE OCORRÊNCIA DA SOLICITAÇÃO DE SERVIÇO OBRIGATÓRIO!";
        }
        if (!trim($this->id_usuario)) {
            $msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function getDeleteErrors() {
        $msgs = array();
        
        $msgs[] = "NÃO É PERMITIDO EXCLUIR UMA OCORRÊNCIA DE SOLICITAÇÃO DE SERVIÇO!";
        
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function pegaServicoSolicitacaoOcorrenciaTipo() {
        $obj = $this->findParentRow("TbServicoSolicitacaoOcorrenciaTipo");
        if ($obj) {
            return $obj;
        }
        return false;
    }
    
    public function pegaUsuario() {
        $obj = $this->findParentRow("TbUsuario");
        if ($obj) {
            return $obj;
        }
        return false;
    }
}
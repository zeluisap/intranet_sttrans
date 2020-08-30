<?php
class AutoInfracaoOcorrencia extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $data = new Zend_Date();
            $this->data_ocorrencia = $data->toString("YYYY-MM-dd");
            $this->hora_ocorrencia = $data->get("HH:mm:ss");
        }
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_funcionario)) {
            $msgs[] = "NENHUM FUNCIONÁRIO VINCULADO!";
        }
        if (!trim($this->id_auto_infracao)) {
            $msgs[] = "NENHUM AUTO DE INFRAÇÃO VINCULADO!";
        }
        if (!trim($this->id_auto_infracao_ocorrencia_tipo)) {
            $msgs[] = "NENHUM TIPO DE AUTO DE INFRAÇÃO VINCULADO!";
        }
        if (!Escola_Util::validaData($this->data_ocorrencia)) {
            $msgs[] = "CAMPO DATA DA OCORRÊNCIA INVÁLIDO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }
    
    public function toString() {
        $txt = array();
        $aiot = $this->findParentRow("TbAutoInfracaoOcorrenciaTipo");
        if ($aiot) {
            $txt[] = $aiot->descricao;
        }
        $txt[] = Escola_Util::formatData($this->data_ocorrencia) . " " . $this->hora_ocorrencia;
        if (trim($this->observacoes)) {
            $txt[] = $this->observacoes;
        }
        if (count($txt)) {
            return implode(" - ", $txt);
        }
        return "";
    }
}
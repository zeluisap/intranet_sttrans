<?php
class Aditivo extends Escola_Entidade {
        
    protected $_valor = false;
    
    public function pega_valor() {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
        }
        return $valor;
    }
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->data = date("Y-m-d");
        }
        $this->_valor = $this->pega_valor();        
    }
    
    public function setFromArray(array $dados) {
        $this->_valor->setFromArray($dados);
        if (isset($dados["data"])) { $dados["data"] = Escola_Util::montaData($dados["data"]); }
        if (isset($dados["data_aditivo"])) { $dados["data_aditivo"] = Escola_Util::montaData($dados["data_aditivo"]); }
        parent::setFromArray($dados);
    }
     
    public function save() {
        $at = $this->findParentRow("TbAditivoTipo");
        if ($at && $at->getId()) {
            $this->id_valor = $this->_valor->save();
        }
        return parent::save();
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_vinculo)) {
            $msgs[] = "CAMPO VÍNCULO OBRIGATÓRIO!";
        }
        if (!trim($this->id_aditivo_tipo)) {
            $msgs[] = "CAMPO TIPO DE ADITIVO OBRIGATÓRIO!";
        }
        if (!trim($this->data) || !Escola_Util::validaData($this->data)) {
            $msgs[] = "CAMPO DATA OBRIGATÓRIO!";
        }
        if (!trim($this->numero)) {
            $msgs[] = "CAMPO NÚMERO OBRIGATÓRIO!";
        }
        if (!trim($this->ano)) {
            $msgs[] = "CAMPO ANO OBRIGATÓRIO!";
        }
        $at = $this->findParentRow("TbAditivoTipo");
        if ($at) {
            if ($at->valor()) {
                if (!$this->_valor->existe_valor()) {
                    $msgs[] = "CAMPO VALOR ADICIONADO OBRIGATÓRIO!";
                }
            } elseif ($at->data()) {
                if (!trim($this->data_aditivo) || !Escola_Util::validaData($this->data_aditivo)) {
                    $msgs[] = "CAMPO NOVA DATA FINAL OBRIGATÓRIO!";
                } else {
                    $vinculo = $this->findParentRow("TbVinculo");
                    if ($vinculo) {
                        $obj_df = $vinculo->pega_data_final();
                        $date_final = new Zend_Date($obj_df->data_final);
                        $date_aditivo = new Zend_Date($this->data_aditivo);
                        if ($date_final >= $date_aditivo) {
                            $msgs[] = "CAMPO NOVA DATA FINAL DEVE SER POSTERIOR A DATA FINAL ATUAL DO PROJETO!";
                        }
                    }
                }
            }
        }
        if (!count($msgs)) {
            $rg = $this->getTable()->fetchAll(" id_vinculo = {$this->id_vinculo} and numero = '{$this->numero}' and ano = {$this->ano} and id_aditivo <> '{$this->getId()}' ");
            if ($rg && count($rg)) {
                $msgs[] = "TERMO ADITIVO JÁ CADASTRADO!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function delete() {
        $valor = $this->_valor;
        $id = parent::delete();
        $valor->delete();
        return $id;
    }   
    
    public function mostrar_numero() {
        $items = array();
        if ($this->numero) {
            $items[] = $this->numero;
        }
        if ($this->ano) {
            $items[] = $this->ano;
        }
        return implode("/", $items);
    }
    
    public function toString() {
        return $this->mostrar_numero();
    }
}
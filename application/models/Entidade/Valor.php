<?php
class Valor extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbMoeda();
            $moeda = $tb->pega_padrao();
            if ($moeda) {
                $this->id_moeda = $moeda->getId();
            }
        }
    }
    
    public function toString() {
        $valor = Escola_Util::number_format($this->valor);
        $moeda = $this->findParentRow("TbMoeda");
        if ($moeda) {
            $valor = $moeda->simbolo . " " . $valor;
        }
        return $valor;
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["valor"])) {
            $dados["valor"] = Escola_Util::montaNumero($dados["valor"]);
        }
        parent::setFromArray($dados);
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->id_moeda)) {
            $msgs[] = "CAMPO MOEDA OBRIGATÓRIO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;        
    }

    public function render(Zend_View_Interface $view, $dados = array()) {
        $ctrl = new Escola_Form_Element_Valor("valor");
        $ctrl->setLabel("Valor:");
        if (isset($dados["label"]) && $dados["label"]) {
            $ctrl->setLabel($dados["label"] . ":");
        }
        $ctrl->setValue($this->valor);
        $ctrl->set_moeda($this->findParentRow("TbMoeda"));
        if (isset($dados["class"]) && $dados["class"]) {
            $ctrl->setAttrib("class", $dados["class"]);
        }
        return $ctrl->render($view);
    }
    
    public function converter() {
        $moeda = $this->findParentRow("TbMoeda");
        if ($moeda) {
            if ($moeda->valor_ref) {
                return ($this->valor * $moeda->valor_ref);
            }
            return $this->valor;
        }
        return 0;
    }
    
    public function existe_valor() {
        if ((int)$this->valor) {
            return true;
        }
        return false;
    }
}
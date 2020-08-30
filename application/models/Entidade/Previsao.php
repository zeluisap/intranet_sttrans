<?php
class Previsao extends Escola_Entidade {
        
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
        $this->_valor = $this->pega_valor();        
    }
    
    public function setFromArray(array $dados) {
        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
    }
     
    public function save() {
        $this->id_valor = $this->_valor->save();
        return parent::save();
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->id_vinculo)) {
			$msgs[] = "CAMPO VÍNCULO OBRIGATÓRIO!";
		}
		if (!trim($this->id_previsao_tipo)) {
			$msgs[] = "CAMPO TIPO DE PREVISÃO OBRIGATÓRIO!";
		}
        if (!trim($this->ano)) {
			$msgs[] = "CAMPO ANO OBRIGATÓRIO!";
		}
        if (!trim($this->mes)) {
			$msgs[] = "CAMPO MÊS OBRIGATÓRIO!";
		}
        if (!$this->_valor->valor) {
			$msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function get_valor() {
        $valor = $this->pega_valor()->valor;
        if ($this->findParentRow("TbPrevisaoTipo")->bolsista()) {
            $bt = $this->findParentRow("TbBolsaTipo");
            if ($bt) {
                return ($bt->pega_valor()->valor * $valor);
            }
        }
        return $this->pega_valor()->valor;
    }
    
    public function mostrar_valor() {
        $txt = Escola_Util::number_format($this->get_valor());
        $valor = $this->pega_valor();
        $moeda = $valor->findParentRow("TbMoeda");
        if ($moeda) {
            $txt = $moeda->simbolo . " " . $txt;
        }
        return $txt;
    }
}
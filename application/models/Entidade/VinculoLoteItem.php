<?php
class VinculoLoteItem extends Escola_Entidade {
        
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
        if (!$this->getId()) {
            $tb = new TbVinculoLoteItemStatus();
            $status = $tb->getPorChave("PP");
            if ($status) {
                $this->id_vinculo_lote_item_status = $status->getId();
            }
            if ($this->bolsista() && !$this->id_bolsa_tipo) {
                $bolsista = $this->pega_referencia();
                if ($bolsista) {
                    $this->id_bolsa_tipo = $bolsista->id_bolsa_tipo;
                    $this->save();
                }
            }
        }
    }
    
    public function setFromArray(array $dados) {
        if (isset($dados["tipo"])) {
            $dados["tipo"] = Escola_Util::maiuscula($dados["tipo"]);
        }
        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
    }
     
    public function save() {
        $this->id_valor = $this->_valor->save();
        return parent::save();
    }
    
    public function getErrors() {
        $msgs = array();
        if (!trim($this->tipo)) {
            $msgs[] = "CAMPO TIPO OBRIGATÓRIO!";
        }
        if (!trim($this->id_bolsa_tipo)) {
            $msgs[] = "CAMPO TIPO DE DESPESA OBRIGATÓRIO!";
        }
        if (!trim($this->id_vinculo_lote_item_status)) {
            $msgs[] = "CAMPO STATUS OBRIGATÓRIO!";
        }
        if (!trim($this->id_vinculo_lote)) {
            $msgs[] = "CAMPO LOTE OBRIGATÓRIO!";
        }
        if (!trim($this->chave)) {
            $msgs[] = "CAMPO REFERÊNCIA OBRIGATÓRIO!";
        }
        if (!$this->_valor->valor) {
            $msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        $rg = $this->getTable()->fetchAll(" id_vinculo_lote = {$this->id_vinculo_lote} and tipo = '{$this->tipo}' and chave = '{$this->chave}' and id_vinculo_lote_item <> '{$this->getId()}' ");
        if ($rg && count($rg)) {
            $msgs[] = "ÍTEM DO LOTE JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }
    
    public function getDeleteErrors() {
        $msgs = array();
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
    
    public function des_tipo() {
        $tipos = $this->getTable()->listar_tipo();
        if (array_key_exists($this->tipo, $tipos)) {
            return $tipos[$this->tipo];
        }
        return "";
    }
    
    public function pega_referencia() {
        $tb = false;
        switch ($this->tipo) {
            case "BO": $tb = new TbBolsista(); break;
            case "PF": $tb = new TbPessoaFisica(); break;
            case "PJ": $tb = new TbPessoaJuridica(); break;
            default: $tb = new TbPessoa(); break;
        }
        if ($tb) {
            return $tb->pegaPorId($this->chave);
        }
        return false;
    }
    
    public function mostrar_referencia() {
        $registro = $this->getReferencia();
        if (!$registro) {
            $registro = $this;
        }
        $obj = $registro->pega_referencia();
        if ($obj) {
            return $obj->toString();
        }
        return "";
    }
    
    public function pagar() {
        if ($this->pagamento_pendente()) {
            $tb = new TbVinculoLoteItemStatus();
            $status = $tb->getPorChave("PG");
            if ($status) {
                $this->id_vinculo_lote_item_status = $status->getId();
                $this->save();
            }                        
        }
    }
    
    public function cancelar_pagamento() {
        if ($this->pagamento_confirmado()) {
            $tb = new TbVinculoLoteItemStatus();
            $status = $tb->getPorChave("PP");
            if ($status) {
                $this->id_vinculo_lote_item_status = $status->getId();
                $this->save();
            }
        }            
    }
    
    public function pega_previsao_tipo() {
        if ($this->tipo) {
            $tb = new TbPrevisaoTipo();
            $pt = $tb->getPorChave($this->tipo);
            if ($pt) {
                return $pt;
            }
        }
        return false;
    }
    
    public function registrar_problema($dados) {
        $pt = $this->pega_previsao_tipo();
        if ($pt && $pt->bolsista()) {
            $usuario = false;
            if (isset($dados["usuario"]) && $dados["usuario"]) {
                $usuario = $dados["usuario"];
            } else {
                $usuario = TbUsuario::pegaLogado();
            }
            if (isset($dados["problema"]) && trim($dados["problema"])) {
                $tb = new TbVinculoLoteItemStatus();
                $status = $tb->getPorChave("FL");
                if ($status) {
                    $this->id_vinculo_lote_item_status = $status->getId();
                    $this->save();
                    if ($this->findParentRow("TbVinculoLoteItemStatus")->problema()) {
                        $problema = trim($dados["problema"]);
                        $tb = new TbBolsistaOcorrencia();
                        $bo = $tb->createRow();
                        $bolsista = $this->pega_referencia();
                        if ($bolsista) {
                            $bo->setFromArray(array("id_bolsista" => $bolsista->getId(),
                                                    "id_usuario" => $usuario->getId(),
                                                    "id_vinculo_lote_item" => $this->getId(),
                                                    "descricao" => $dados["problema"]));
                            $errors = $bo->getErrors();
                            if (!$errors) {
                                $bo->save();
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function toString() {
        $txts = array();
        $lote = $this->findParentRow("TbVinculoLote");
        if ($lote) {
            $txts[] = $lote->toString();
        }
        $pt = $this->pega_previsao_tipo();
        if ($pt) {
            $txts[] = $pt->toString();
        }
        $txts[] = $this->pega_valor()->toString();
        return implode(" - ", $txts);
    }
    
    public function pega_ocorrencia() {
        $tb = new TbBolsistaOcorrencia();
        $registros = $tb->listar(array("id_vinculo_lote_item" => $this->getId()));
        if ($registros && count($registros)) {
            return $registros;
        }
        return false;
    }
    
    public function registrar_inapto($dados) {
        $pt = $this->pega_previsao_tipo();
        if ($pt && $pt->bolsista()) {
            $usuario = false;
            if (isset($dados["usuario"]) && $dados["usuario"]) {
                $usuario = $dados["usuario"];
            } else {
                $usuario = TbUsuario::pegaLogado();
            }
            if (isset($dados["problema"]) && trim($dados["problema"])) {
                $tb = new TbVinculoLoteItemStatus();
                $status = $tb->getPorChave("IN");
                if ($status) {
                    $this->id_vinculo_lote_item_status = $status->getId();
                    $this->save();
                    if ($this->findParentRow("TbVinculoLoteItemStatus")->inapto()) {
                        $problema = trim($dados["problema"]);
                        $tb = new TbBolsistaOcorrencia();
                        $bo = $tb->createRow();
                        $bolsista = $this->pega_referencia();
                        if ($bolsista) {
                            $bo->setFromArray(array("id_bolsista" => $bolsista->getId(),
                                                    "id_usuario" => $usuario->getId(),
                                                    "id_vinculo_lote_item" => $this->getId(),
                                                    "descricao" => "INAPTO: " . $dados["problema"]));
                            $errors = $bo->getErrors();
                            if (!$errors) {
                                $bo->save();
                            }
                        }
                    }
                }
            }
        }
        return false;
    }    
    
    public function cancelar_inapto($dados) {
        $pt = $this->pega_previsao_tipo();
        if ($pt && $pt->bolsista()) {
            $usuario = false;
            if (isset($dados["usuario"]) && $dados["usuario"]) {
                $usuario = $dados["usuario"];
            } else {
                $usuario = TbUsuario::pegaLogado();
            }
            $tb = new TbVinculoLoteItemStatus();
            $status = $tb->getPorChave("PP");
            if ($status) {
                $this->id_vinculo_lote_item_status = $status->getId();
                $this->save();
                if ($this->findParentRow("TbVinculoLoteItemStatus")->pendente()) {
                    $tb = new TbBolsistaOcorrencia();
                    $bo = $tb->createRow();
                    $bolsista = $this->pega_referencia();
                    if ($bolsista) {
                        $bo->setFromArray(array("id_bolsista" => $bolsista->getId(),
                                                "id_usuario" => $usuario->getId(),
                                                "id_vinculo_lote_item" => $this->getId(),
                                                "descricao" => "Registro de Inapto Cancelado!"));
                        $errors = $bo->getErrors();
                        if (!$errors) {
                            $bo->save();
                        }
                    }
                }
            }
        }
        return false;
    }    
    
    public function getReferencia() {
        try {
            if ($this->tipo) {
                $class_name = "VinculoLoteItem_" . $this->tipo;
                if (class_exists($class_name)) {
                    $dados = $this->toArray();
                    $stored = false;
                    if ($this->getId()) {
                        $stored = true;
                    }
                    $obj = new $class_name(array("table" => $this->getTable(), "data" => $this->toArray(), "stored" => $stored));
                    return $obj;
                }
                return $this;
            }
        } catch (Exception $e) {
            die($e->getMessage());
            return false;
        }
        return false;
    }
    
    public function toForm(Zend_View_Abstract $view) {
        $instancia = $this->getReferencia();
        $class_name = get_class($instancia);
        if ($class_name == __CLASS__) {
            ob_start();
?>
<script type="text/javascript">
$(document).ready(function() {
    $(".class_moeda").css("text-align", "right").priceFormat({
        prefix: '',
        centsSeparator: ',', 
        thousandsSeparator: '.',
        limit: false,
        centsLimit: 2
    });
});
</script>
<?php 
            if ($this->getId()) {
?>
            <dl class="dl-horizontal">
                <dt>Beneficiário: </dt>
                <dd><?php echo $this->mostrar_referencia(); ?></dd>
            </dl>
<?php
            } else {
                $id_pessoa_fisica = 0;
                $referencia = $this->pega_referencia();
                if ($referencia) {
                    $id_pessoa_fisica = $referencia->id_pessoa_fisica;
                }
                $ctrl = new Escola_Form_Element_Select_Table_Pessoa("chave");
                $ctrl->setLabel("Beneficiário:");
                $ctrl->setValue($id_pessoa_fisica);
                echo $ctrl->render($view);
            }
?>
<?php
$valor = $this->pega_valor();
$tb = new TbMoeda();
$moeda = $tb->pega_padrao();
?>
            <div class="control-group">
                <label for="valor" class="control-label">Valor: </label>
                <div class="controls">
                    <div class="input-prepend">
                        <div class="add-on"><?php echo $moeda->simbolo; ?></div>
                        <input type="text" name="valor" id="valor" class="class_moeda input-medium" value="<?php echo Escola_Util::number_format($valor->valor); ?>" />
                    </div>
                </div>
            </div>
<?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
        return $instancia->toForm($view);
    }
    
    public function toHTML(Zend_View_Abstract $view) {
        $referencia = $this->pega_referencia();
        $valor = $this->pega_valor();
        $situacao = $this->findParentRow("TbVinculoLoteItemStatus");
        ob_start();
?>
<dl class="dl-horizontal">
    <dt>Tipo:</dt>
    <dd><?php echo $this->des_tipo(); ?></dd>
</dl>
<?php if ($referencia) { ?>
<dl class="dl-horizontal">
    <dt>Referencia:</dt>
    <dd><?php echo $referencia->toString(); ?></dd>
</dl>
<?php } ?>
<?php if ($valor) { ?>
<dl class="dl-horizontal">
    <dt>Valor:</dt>
    <dd><?php echo $valor->toString(); ?></dd>
</dl>
<?php } ?>
<?php if ($situacao) { ?>
<dl class="dl-horizontal">
    <dt>Situacao:</dt>
    <dd><?php echo $situacao->toString(); ?></dd>
</dl>
<?php } ?>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    public function bolsista() {
        $tb = new TbPrevisaoTipo();
        $pt = $tb->getPorChave($this->tipo);
        if ($pt) {
            return $pt->bolsista();
        }
        return false;
    }
    
    public function pagamento_pendente() {
        $status = $this->findParentRow("TbVinculoLoteItemStatus");
        if ($status) {
            return $status->pagamento_pendente();
        }
        return false;
    }
    
    public function pagamento_confirmado() {
        $status = $this->findParentRow("TbVinculoLoteItemStatus");
        if ($status) {
            return $status->pagamento_confirmado();
        }
        return false;
    }
}
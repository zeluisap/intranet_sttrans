<?php
class VinculoMovimento_DE extends VinculoMovimento {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbVinculoMovimentoTipo();
            $vmt = $tb->getPorChave("DE");
            if ($vmt && $vmt->getId()) {
                $this->id_vinculo_movimento_tipo = $vmt->getId();
            }
        }
        $this->calculaValorPosterior();
    }
    
    public function getErrors() {
        $msgs = parent::getErrors();
        if (!$msgs) {
            $msgs = array();
        }
        if (!trim($this->id_despesa_tipo)) {
            $msgs[] = "CAMPO TIPO DE DESPESA OBRIGATÓRIO!";
        }
        if (!trim($this->id_forma_pagamento)) {
            $msgs[] = "CAMPO FORMA DE PAGAMENTO OBRIGATÓRIO!";
        } else {
            $fp = $this->findParentRow("TbFormaPagamento");
            if ($fp && $fp->cheque() && !$this->numero_documento) {
                $msgs[] = "CAMPO NÚMERO DO CHEQUE OBRIGATÓRIO!";
            }
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function calculaValorPosterior() {
        if ($this->_valor && $this->_valor_anterior) {
            $this->_valor_posterior = $this->_valor_anterior->valor - $this->_valor->valor;
        }
    }
    
    public function render(Zend_View_Abstract $view) {
        $tb = new TbFormaPagamento();
        $fp = $tb->getPorChave("CH"); //CHEQUE
        ob_start();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#id_despesa_tipo").focus().select();
<?php if ($fp) { ?>
        $("#id_forma_pagamento").change(function() {
            $(".linha_numero_cheque").hide();
            if ($(this).val() == "<?php echo $fp->getId(); ?>") {
                $(".linha_numero_cheque").show();
            }
        }).change();
<?php } ?>
    });
</script>
<?php 
$ctrl = new Escola_Form_Element_Select_Table("id_despesa_tipo");
$ctrl->setPkName("id_despesa_tipo");
$ctrl->setModel("TbDespesaTipo");
$ctrl->setValue($this->id_despesa_tipo);
$ctrl->setLabel("Tipo de Despesa:");
echo $ctrl->render($view);

echo $this->_valor->render($view); 

$ctrl = new Escola_Form_Element_Select_Table("id_forma_pagamento");
$ctrl->setPkName("id_forma_pagamento");
$ctrl->setModel("TbFormaPagamento");
$ctrl->setValue($this->id_forma_pagamento);
$ctrl->setLabel("Forma de Pagamento:");
echo $ctrl->render($view);
?>
<div class="control-group linha_numero_cheque hide">
    <label for="numero_documento" class="control-label">Número do Cheque:</label>
    <div class="controls">
        <input type="text" name="numero_documento" id="numero_documento" class="span2" value="<?php echo $this->numero_documento; ?>" />
    </div>
</div>
<div class="control-group">
    <label for="data_movimento" class="control-label">Data da Saída:</label>
    <div class="controls">
        <input type="text" name="data_movimento" id="data_movimento" class="data span2" value="<?php echo Escola_Util::formatData($this->data_movimento); ?>" />
    </div>
</div>
<div class="control-group">
    <label for="descricao" class="control-label">Observações:</label>
    <div class="controls">
        <textarea name="descricao" id="descricao" class="span5" rows="6"><?php echo $this->descricao; ?></textarea>
    </div>
</div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
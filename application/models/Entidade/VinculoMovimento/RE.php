<?php
class VinculoMovimento_RE extends VinculoMovimento {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $tb = new TbVinculoMovimentoTipo();
            $vmt = $tb->getPorChave("RE");
            if ($vmt && $vmt->getId()) {
                $this->id_vinculo_movimento_tipo = $vmt->getId();
            }
            $tb = new TbDespesaTipo();
            $dt = $tb->getPorChave("NO");
            if ($dt && $dt->getId()) {
                $this->id_despesa_tipo = $dt->getId();
            }
        }
        $this->calculaValorPosterior();
    }
    
    public function getErrors() {
        $msgs = parent::getErrors();
        if (!$msgs) {
            $msgs = array();
        }
        if (!trim($this->numero_documento)) {
            $msgs[] = "CAMPO NOTA FISCAL OBRIGATÓRIO!";
        }
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function calculaValorPosterior() {
        if ($this->_valor && $this->_valor_anterior) {
            $this->_valor_posterior = $this->_valor->valor + $this->_valor_anterior->valor;
        }
    }
    
    public function render(Zend_View_Abstract $view) {
        ob_start();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#valor").focus().select();
    });
</script>
<?php echo $this->_valor->render($view); ?>
<div class="control-group">
    <label for="numero_documento" class="control-label">Número da Nota Fiscal:</label>
    <div class="controls">
        <input type="text" name="numero_documento" id="numero_documento" class="span2" value="<?php echo $this->numero_documento; ?>" />
    </div>
</div>
<div class="control-group">
    <label for="data_movimento" class="control-label">Data da Receita:</label>
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
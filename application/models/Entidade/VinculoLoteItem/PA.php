<?php
class VinculoLoteItem_PA extends VinculoLoteItem {
    
    protected $passagem;

    public function setFromArray(array $dados) {
        $passagem = $this->pega_referencia();
        $passagem->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
    public function pega_referencia() {
        if ($this->passagem) {
            return $this->passagem;
        }
        $passagem = false;
        $tb = new TbPassagem();
        if ($this->chave) {
            $passagem = $tb->pegaPorId($this->chave);
        }
        if (!$passagem) {
            $passagem = $tb->createRow();
        }
        $this->passagem = $passagem;
        return $this->passagem;
    }
    
    public function save() {
        $passagem = $this->pega_referencia();
        $passagem->save();
        if ($passagem->getId()) {
            $this->chave = $passagem->getId();
        }
        if ($this->chave) {
            parent::save();
        }
    }
    
    public function getErrors() {
        $passagem = $this->pega_referencia();
        if ($passagem) {
            $errors = $passagem->getErrors();
        } else {
            $errors = array();
        }
        if (!(float)$this->pega_valor()->valor) {
            $errors[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        if (count($errors)) {
            return $errors;
        }
        return false;
    }

    public function toForm(Zend_View_Abstract $view) {
        try {
            ob_start();
            $passagem = $this->pega_referencia();
?>
<script type="text/javascript">
    $(document).ready(function() {
        $(".data").mask("99/99/9999");
		$(".data").datepicker();
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
                $ctrl = new Escola_Form_Element_Select_Table_PessoaJuridica("id_pessoa_juridica");
                $ctrl->setLabel("Beneficiário:");
                $ctrl->setValue($passagem->id_pessoa_juridica);
                echo $ctrl->render($view);
            }
?>
            <div class="control-group">
                <label for="trecho" class="control-label">Trecho: </label>
                <div class="controls">
                    <textarea name="trecho" id="trecho" rows="6" class="span5 field_lote_item"><?php echo $passagem->trecho; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label for="data_ida" class="control-label">Data Ida: </label>
                <div class="controls">
                    <input type="text" name="data_ida" id="data_ida" class="data span2 field_lote_item" value="<?php echo Escola_Util::formatData($passagem->data_ida) ?>" />
                </div>
            </div>
            <div class="control-group">
                <label for="data_volta" class="control-label">Data Volta: </label>
                <div class="controls">
                    <input type="text" name="data_volta" id="data_volta" class="data span2 field_lote_item" value="<?php echo Escola_Util::formatData($passagem->data_volta) ?>" />
                </div>
            </div>
<?php
$tb = new TbMoeda();
$moeda = $tb->pega_padrao();
$valor = $this->pega_valor();
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
        } catch (Exception $e) {
            die($e->getMessage()); 
        }
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
<?php 
if ($referencia) {
    echo $referencia->toHTML($view);
} 
?>
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
}
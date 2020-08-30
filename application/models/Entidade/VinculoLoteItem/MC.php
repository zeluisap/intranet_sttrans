<?php
class VinculoLoteItem_MC extends VinculoLoteItem {
    
    protected $referencia;

    public function setFromArray(array $dados) {
        $referencia = $this->pega_referencia();
        $referencia->setFromArray($dados);
        parent::setFromArray($dados);
    }
    
    public function pega_referencia() {
        if ($this->referencia) {
            return $this->referencia;
        }
        $referencia = false;
        $tb = new TbMaterial();
        if ($this->chave) {
            $referencia = $tb->pegaPorId($this->chave);
        }
        if (!$referencia) {
            $referencia = $tb->createRow();
        }
        $this->referencia = $referencia;
        return $this->referencia;
    }
    
    public function save() {
        $referencia = $this->pega_referencia();
        $referencia->save();
        if ($referencia->getId()) {
            $this->chave = $referencia->getId();
        }
        if ($this->chave) {
            parent::save();
        }
    }
    
    public function getErrors() {
        $referencia = $this->pega_referencia();
        if ($referencia) {
            $errors = $referencia->getErrors();
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
?>
<script type="text/javascript">
$(document).ready(function() {
    $("#quantidade, #valor_unitario").on("keyup", function() {
        var quantidade = $("#quantidade").val().replace(".", "");
        quantidade = parseFloat(quantidade.replace(",", "."));
        var valor_unitario = $("#valor_unitario").val().replace(".", "");
        valor_unitario = parseFloat(valor_unitario.replace(",", "."));
        if (quantidade && valor_unitario) {
            var valor = quantidade * valor_unitario;
        } else {
            var valor = 0;
        }
        $("#valor").val($.formatNumber(valor, {format:"#,###.00", locale:"us"}));
        $("#valor").keyup();
    });
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
            $referencia = $this->pega_referencia();
            $ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_MaterialUnidadeTipo("id_material_unidade_tipo");
            $ctrl->setLabel("Tipo de Unidade: ");
            $ctrl->setValue($referencia->id_material_unidade_tipo);
            echo $ctrl->render($view);
            $tb = new TbMaterialTipo();
            $mt = $tb->getPorChave("MC");
            $ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_MaterialTipoItem("id_material_tipo_item");
            $ctrl->setLabel("Tipo de Material: ");
            $ctrl->setValue($referencia->id_material_tipo_item);
            if ($mt) {
                $ctrl->setMaterialTipo($mt);
            }
            echo $ctrl->render($view);
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
                $ctrl->setValue($referencia->id_pessoa_juridica);
                echo $ctrl->render($view);
            }
?>
            <div class="control-group">
                <label for="descricao" class="control-label">Descrição: </label>
                <div class="controls">
                    <textarea name="descricao" id="descricao" rows="6" class="span5 field_lote_item"><?php echo $referencia->descricao; ?></textarea>
                </div>
            </div>
            <div class="control-group">
                <label for="quantidade" class="control-label">Quantidade: </label>
                <div class="controls">
                    <input type="text" name="quantidade" id="quantidade" value="<?php echo Escola_Util::number_format($referencia->quantidade); ?>" class="class_moeda" />
                </div>
            </div>
<?php
$valor = $referencia->pega_valor();
$tb = new TbMoeda();
$moeda = $tb->pega_padrao();
?>
            <div class="control-group">
                <label for="valor_unitario" class="control-label">Valor Unitário: </label>
                <div class="controls">
                    <div class="input-prepend">
                        <div class="add-on"><?php echo $moeda->simbolo; ?></div>
                        <input type="text" name="valor_unitario" id="valor_unitario" class="class_moeda input-medium" value="<?php echo Escola_Util::number_format($valor->valor); ?>" />
                    </div>
                </div>
            </div>
<?php
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
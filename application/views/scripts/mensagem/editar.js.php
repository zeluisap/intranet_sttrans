<?php
$tb = new TbMensagemTipo();
?>
$(document).ready(function() {
    $("#id_mensagem_tipo").change(
        function() {
            $(".linha_chave_destino").hide();
<?php
$mt = $tb->getPorChave("S");
if ($mt && $mt->getId()) {
?>
            if ($(this).val() == <?php echo $mt->getId(); ?>) {
                $("#chave_destino").children().remove();
                if (chefes.length) {
                    $("<option>").attr( { "value": "" <?php if (!$this->view->registro->chave_destino) { ?>, "selected": true<?php } ?> } ).text("==> SELECIONE <==").appendTo($("#chave_destino"));
                    for (var i = 0; i < chefes.length; i++) {
                        var op = $("<option>").attr("value", chefes[i].id).text(chefes[i].descricao).appendTo($("#chave_destino"));
                        if (chefes[i].id == '<?php echo $this->view->registro->chave_destino; ?>') {
                            op.attr("selected", true);
                        }
                    }
                    $(".linha_chave_destino").show();
                }
            }
<?php
}
$mt = $tb->getPorChave("A");
if ($mt && $mt->getId()) {
?>
            if ($(this).val() == <?php echo $mt->getId(); ?>) {
                $("#chave_destino").children().remove();
                if (setores.length) {
                    $("<option>").attr({ "value": "" <?php if (!$this->view->registro->chave_destino) { ?>, "selected": true<?php } ?> } ).text("==> SELECIONE <==").appendTo($("#chave_destino"));
                    for (var i = 0; i < setores.length; i++) {
                        var op = $("<option>").attr("value", setores[i].id).text(setores[i].descricao).appendTo($("#chave_destino"));
                        if (setores[i].id == '<?php echo $this->view->registro->chave_destino; ?>') {
                            op.attr("selected", true);
                        }
                    }
                    $(".linha_chave_destino").show();
                }
            }
<?php } ?>
<?php
$mt = $tb->getPorChave("P");
if ($mt && $mt->getId()) {
?>
            $("#linha_id_pessoa_fisica").hide();
            if ($(this).val() == <?php echo $mt->getId(); ?>) {
                $("#linha_id_pessoa_fisica").show();
            }
<?php } ?>
<?php
$mt = $tb->getPorChave("E");
if ($mt && $mt->getId()) {
?>
            $("#linha_id_setor").hide();
            if ($(this).val() == <?php echo $mt->getId(); ?>) {
                $("#linha_id_setor").show();
            }
<?php } ?>
        }
    ).change();
    $("#id_mensagem_tipo").focus();
    var editor = criaEditor("mensagem");
});
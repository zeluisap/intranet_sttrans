<?php
$tb = new TbPrevisaoTipo();
$pt = $tb->getPorChave("BO");
?>
$(document).ready(
    function() {
<?php if ($pt) { ?>
        $("#id_previsao_tipo").change(function() {
            var ctrl = $("#linha_valor").find("label");
            ctrl.text("Valor:");
            if ($(this).val() == "<?php echo $pt->getId(); ?>") {
                ctrl.text("Valor da Bolsa:");
            }
        });
<?php } ?>
        $("#id_previsao_tipo").focus().select();
    }
);
<?php
$tb = new TbServicoTipo();
$st = $tb->getPorChave("TR");
?>
$(document).ready(
	function() {
<?php if ($st) { ?>
        $("#id_servico_tipo").change(function() {
            $(".linha_id_servico_referencia").hide();
            if ($(this).val() == "<?php echo $st->getId(); ?>") { 
                $(".linha_id_servico_referencia").show();
            }
        }).change();
<?php } ?>
		$("#id_servico_tipo").focus();
	}
);
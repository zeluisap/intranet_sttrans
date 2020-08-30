<?php
$tb = new TbPeriodicidade();
$per = $tb->getPorChave("A");
?>
$(document).ready(
	function() {
        $("#id_periodicidade").change(function() {
            if ($(this).val().length) {
                $("#validade_dias").val(periodicidades[$(this).val()] * 30);
            }
<?php if ($per) { ?>
            $("#linha_mes_referencia").hide();
            if ($(this).val() == "<?php echo $per->getId(); ?>") {
                $("#linha_mes_referencia").show();
            }
<?php } ?>
        }).change();
<?php if ($this->view->servico->transporte()) { ?>
		$("#id_transporte_grupo").focus();
<?php } else { ?>
        $("#valor").focus();
<?php } ?>
	}
);
<?php
$tb = new TbTransportePessoaTipo();
$tpt = $tb->getPorChave("MO");
?>
$(document).ready(
	function() {
        $("#linha_id_motorista").hide();
<?php if ($tpt) { ?>
        $("#id_transporte_pessoa_tipo").change(function() {
            $("#linha_id_pessoa, #linha_id_motorista").hide();
            if ($("#id_transporte_pessoa_tipo").val() == "<?php echo $tpt->getId(); ?>") {
                $("#linha_id_motorista").show();
            } else {
                $("#linha_id_pessoa").show();
            }
        }).change();
<?php } ?>
		$("#id_transporte_pessoa_tipo").focus();
	}
);
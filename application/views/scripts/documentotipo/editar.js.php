<?php
?>
$(document).ready(
	function() {
<?php 
$tb = new TbDocumentoTipoTarget();
$dtt = $tb->pegaDocumentoAdministrativo();
if ($dtt) {
?>
		$("#id_documento_tipo_target").change(
			function() {
				$(".linha_possiu_numero").hide();
				if ($(this).val() == '<?php echo $dtt->getId(); ?>') {
					$(".linha_possiu_numero").show();
				}
			}
		).change();
<?php } ?>
		$("#id_documento_tipo_target").focus();
	}
);
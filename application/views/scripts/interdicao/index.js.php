$(document).ready(function() {
    $("#filtro_id_pessoa_tipo").change(function() {
        $(".linha_cpf, .linha_cnpj").hide();
<?php
$tb = new TbPessoaTipo();
$pf = $tb->getPorChave("PF");
if ($pf) {
?>
        if ($(this).val() == "<?php echo $pf->getId(); ?>") {
            $(".linha_cpf").show();
        }
<?php } ?>
<?php
$pj = $tb->getPorChave("PJ");
if ($pj) {
?>
        if ($(this).val() == "<?php echo $pj->getId(); ?>") {
            $(".linha_cnpj").show();
        }
<?php } ?>
    });
});
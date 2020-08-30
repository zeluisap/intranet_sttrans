<?php
$tb = new TbAutoInfracaoDevolucaoStatus();
$aids = $tb->getPorChave("O");
?>
$(document).ready(function() {
    $("#id_auto_infracao_devolucao_status").change(function() {
        $(".linha_observacoes, .linha_notificacao").hide();
        if ($(this).val().length) { 
            if ($(this).val() != "<?php echo $aids->getId(); ?>") {
                $(".linha_observacoes").show();
            } else {
                $(".linha_notificacao").show();
            }
        }
    }).change();
});
<?php 
$tb = new TbRequerimentoJariStatus();
$rjs_dt = $tb->getPorChave("DT");
$rjs_dp = $tb->getPorChave("DP");
?>
$(document).ready(
    function() {
        $("#id_requerimento_jari_status").focus().select();
        $("#id_requerimento_jari_status").change(function() {
            $.limpaAlerta();
            $(".linha_infracao, .linha_observacao").hide();
<?php if ($rjs_dt && $rjs_dt->getId()) { ?>
            if ($(this).val().length && ($(this).val() != "<?php echo $rjs_dt->getId(); ?>")) {
                $(".linha_observacao").show();
                $("#observacao").val("").focus();
<?php if ($rjs_dp && $rjs_dp->getId()) { ?>
                if ($(this).val() == "<?php echo $rjs_dp->getId(); ?>") {
                    $(".id_infracao").attr("checked", false);
                    $(".linha_infracao").show();
                }
<?php } ?>
            }
<?php } ?>
        });
        $(".link_infracao").click(function(ev) {
            ev.preventDefault();
            var controle = $(this).parents("tr").find("input:checkbox");
            if (controle.length) {
                var checked = controle.attr("checked");
                if (checked) {
                    controle.attr("checked", false);
                } else {
                    controle.attr("checked", true);
                }
            }
        });
    }
);
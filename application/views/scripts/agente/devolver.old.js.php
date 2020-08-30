<?php
$tb = new TbAutoInfracaoDevolucaoStatus();
$aids = $tb->getPorChave("O");
?>
$(document).ready(function() {
<?php if ($aids) { ?>
    $(".result_aids").change(function() {
        var id = $(this).attr("rel");
        $("#result_" + id + "_obs").hide().val("");
        if (($(this).val() != "") && ($(this).val() != "<?php echo $aids->getId(); ?>")) {
            $("#result_" + id + "_obs").show().focus().select();
        }
    });
<?php } ?>
    $("#formulario").submit(function(event) {
        var flag = true;
        $(".result_aids, .result_obs").removeClass("red");
        $(".result_aids").each(function(idx, obj) {
            if ($(obj).val() == "") {
                $(obj).addClass("red");
                flag = false;
            } else if ($(obj).val() != "<?php echo $aids->getId(); ?>") {
                var id = $(obj).attr("rel");
                var item = $("#result_" + id + "_obs");
                if (item.val() == "") {
                    $(item).addClass("red");
                }
            }
        });
        if (!flag) {
            event.preventDefault();
        }
    });
});
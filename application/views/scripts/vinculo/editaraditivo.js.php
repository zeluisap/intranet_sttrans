<?php
$tb = new TbAditivoTipo();
?>
$(document).ready(
    function() {
        $("#id_aditivo_tipo").change(function() {
            $(".linha_aditivo_data, .linha_aditivo_valor").hide();
<?php $at = $tb->getPorChave("D"); if ($at) { ?>
            if ($(this).val() == "<?php echo $at->getId(); ?>") { 
                $(".linha_aditivo_data").show();
            }
<?php } ?>
<?php $at = $tb->getPorChave("V"); if ($at) { ?>
            if ($(this).val() == "<?php echo $at->getId(); ?>") { 
                $(".linha_aditivo_valor").show();
            }
<?php } ?>
        }).change();
        $("#id_aditivo_tipo").focus();
    }
);
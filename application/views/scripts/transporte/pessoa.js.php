$(document).ready(function() {
    
});

function carteira() {
    $("#formulario").attr("action", "<?php echo $this->view->url(array("action" => "carteira")); ?>");
    $("#formulario").submit();
}
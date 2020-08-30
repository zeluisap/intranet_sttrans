$(document).ready(function() {
    $("#id_info_bancaria").change(function() {
        window.location = "<?php echo $this->view->url(array("controller" => "vinculo", "action" => "movimento"), null, true); ?>/id_info_bancaria/" + $(this).val();
    });
});
function importar() {
    if ($(".importar").css("display") == "none") {
        $(".importar").show();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "importar")); ?>");
    } else {
        $(".importar").hide();
        $("#formulario").attr("action", "<?php echo $this->view->url(array("controller" => $this->_request->getControllerName(), "action" => "index")); ?>");    
    }
}
<?php 
class Escola_Form_Element_Icone extends Zend_Form_Element_Text {
    public function render(Zend_View_Interface $view = null) {
        ob_start();
?>
<style type="text/css">
.btn-icone {
	width: 40px;
	margin: 3px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
	$(".icone-texto").keyup(function() {
		var ctrl_icone = $("#icone-texto-button-icon");
		ctrl_icone.removeClass();
		ctrl_icone.addClass(".icone-texto-button-icon");
		if ($(this).val().length) {
			ctrl_icone.addClass($(this).val());
		}
	});
	$(".btn-icone").click(function(evt) {
		evt.preventDefault();
		$(".icone-texto").val($(this).attr("id"));
		$("#icone-texto-button-icon").removeClass().addClass(".icone-texto-button-icon").addClass($(this).attr("id"));
		$("#myModal<?php echo $this->getId(); ?>").modal("hide");
	});
	$("#myModal<?php echo $this->getId(); ?>").css({ "width": "700px", "margin-left": "-350px" });
});
</script>
	<div id="myModal<?php echo $this->getId(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
			<h3 id="myModalLabel">Selecionar Ícone</h3>
		</div>
		<div class="modal-body">
<?php
$tb = new TbIcone();
$icones =  $tb->listar();
foreach ($icones as $icone) {
?>
	<button class="btn btn-icone" id="<?php echo $icone->descricao; ?>"><i class="<?php echo $icone->descricao; ?> icon-large"></i></button>
<?php } ?>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
		</div>
	</div>
	<div class="control-group">
		<label for="icon" class="control-label">Ícone:</label>
		<div class="controls">
			<div class="input-append">
				<input type="text" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getId(); ?>" maxlength="60" value="<?php echo $this->getValue(); ?>" class="icone-texto" />
				<button class="btn" type="button"  data-toggle="modal" title="Localizar Ícone" data-target="#myModal<?php echo $this->getId(); ?>"><i class="<?php echo $this->getValue(); ?> icone-texto-button-icon" id="icone-texto-button-icon">&nbsp;</i></button>
			</div>
		</div>
	</div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
var grupos = new Array();
var usuario_grupos = new Array();
$(document).ready(
	function() {
		$("#seta_direita").click(
			function(evt) {
				evt.preventDefault();
				var lista = $(".lista_usuarios:checked");
				if (lista.length) {
					lista.each(
						function() {
							var id = $(this).val();
							$(grupos).each(
								function(idc, val) {
									if (val.id == id) {
										usuario_grupos[usuario_grupos.length] = val;
										grupos.splice(idc, 1);
									}
								}
							);
						}
					);
				}
				atualizaListas();
			}
		);
		$("#seta_esquerda").click(
			function(evt) {
				evt.preventDefault();
				var lista = $(".lista_usuarios_grupo:checked");
				if (lista.length) {
					lista.each(
						function() {
							var id = $(this).val();
							$(usuario_grupos).each(
								function(idc, val) {
									if (val.id == id) {
										grupos[grupos.length] = val;
										usuario_grupos.splice(idc, 1);
									}
								}
							);
						}
					);
				}
				atualizaListas();
			}
		);
<?php 
	$i = 0;
	$tb = new TbGrupo();
	foreach ($this->view->grupos as $grupo) {
?>
		grupos[<?php echo $i; ?>] = { "id" : <?php echo $grupo->getId(); ?>, "nome" : '<?php echo $grupo->descricao; ?>' };
<?php $i++; } ?>
<?php 
	$i = 0;
	if ($this->view->usuario_grupos) {
	foreach ($this->view->usuario_grupos as $grupo) { 
?>
		usuario_grupos[<?php echo $i; ?>] = { "id" : <?php echo $grupo->getId(); ?>, "nome" : '<?php echo $grupo->descricao; ?>' };
<?php $i++; }} ?>
		
		atualizaListas();
	}
);

function atualizaListas() {
	atualiza("esquerda", grupos, "lista_usuarios");
	atualiza("direita", usuario_grupos, "lista_usuarios_grupo");
}

function atualiza(lado, users, nome) {
	var lista = $("table." + lado + " tbody");
	lista.children().remove();
	if (users.length) {
		for (var i = 0; i < users.length; i++) {
			$("<tr><td><input type='checkbox' name='" + nome + "[]' id='lista_" + users[i].id + "' value='" + users[i].id + "'  class='" + nome + "' /></td><td><label for='lista_" + users[i].id + "'>" + users[i].nome + "</label></td></tr>").appendTo(lista);
		}
	}
}

function salvar() {
	$(".lista_usuarios_grupo").attr("checked", true);
	salvarFormulario("formulario");
}
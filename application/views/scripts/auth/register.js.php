$(document).ready(
	function() {
		var ajax_uf = false;
		var default_id_uf = '<?php echo $this->getRequest()->getPost("id_uf"); ?>';
		$("#id_uf").change(
			function() {
				$("#linha_municipio").hide();				
				var ctrl = $("#id_municipio");
				ctrl.children().remove();
				if ($(this).val()) {
					$("#linha_municipio").fadeIn();
					$("#municipio").focus();
				}
			}
		);
		$("#id_pais").change(
			function() {
				if (ajax_uf) {
					ajax_uf.abort();
					ajax_uf = false;
				}
				$("#linha_uf, #linha_dinamic_uf, #linha_municipio").hide();
				if ($("#id_pais option:selected").val().length && $("#id_pais option:selected").text().toUpperCase() != "BRASIL") {
					$("#linha_dinamic_uf").show();
					$("#dinamic_uf").focus();
				} else {
					var ctrl = $("#id_uf");
					ctrl.children().remove();
					if ($(this).val()) {
						ajax_uf = $.ajax({
							"url" : "<?php echo $this->view->baseUrl(); ?>/uf/listar/format/json/",
							"type" : "POST",
							"data" : { "id_pais" : $(this).val() },
							"success" : function(obj_view) {
								if (obj_view.result && obj_view.result.length) {
									$("<option value=''>==> SELECIONE <==</option>").appendTo(ctrl);
									for (var x = 0; x < obj_view.result.length; x++) {
										var obj = obj_view.result[x];
										var selected = "";
										if (obj.id == default_id_uf) {
											selected = " selected ";
										}
										$("<option value='" + obj.id + "' " + selected + ">" + obj.descricao + "</option>").appendTo(ctrl);
									}
									$("#linha_uf").fadeIn();
									$("#id_uf").change();
								}
							}
						});
					}
				}
			}
		).change();
		$("#dinamic_uf").blur(
			function() {
				if ($(this).val().length) {
					$("#linha_municipio").fadeIn();
					$("#municipio").focus();
				} else {
					$("#linha_municipio").hide();
				}
			}
		);
		$("#nome").focus().select();
	}
);
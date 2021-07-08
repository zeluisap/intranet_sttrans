var formatarReal = n => {
	return new Intl.NumberFormat('pt-BR', {
		style: 'currency',
		currency: 'BRL'
	}).format(n);
}

var montaNumero = text => {
	if (!text) {
		return 0;
	}

	text = text.replace(".", "").replace(",", ".");

	if (isNaN(text)) {
		return 0;
	}

	return parseFloat(text);
	
}

var somenteNumeros = text => {
	if (!text) {
		return '';
	}
	return text.replace(/[^\d]+/g,'');
}

$(document).ready(
    function() {
        $(".moeda").priceFormat({
			prefix: '',
			centsSeparator: ',', 
			thousandsSeparator: '.',
			limit: false,
			centsLimit: 2
		});
        $('.fancybox').fancybox({ "type": "image" });
		$.datepicker.setDefaults( $.datepicker.regional[ "pt-BR" ] );
		$(".cpf").mask("999.999.999-99");
        $(".cnpj").mask("99.999.999/9999-99");
		$(".data").mask("99/99/9999");
		$(".data").datepicker();
		$(".cep").mask("99.999-999");
		$(".telefone").mask("( 99 ) 9999-9999");
		$(".celular").mask("( 99 ) 99999-9999");
        $(".placa").mask("***9999");
        $(".auto_infracao").mask("* 99999999");
        $(".hora").mask("99:99:99");
		$(".ck-marca-todos").change(
			function() {
				$checked = false;
				if ($(this).attr("checked")) {
					$checked = true;
				}
				var lista = $("." + $(this).attr("rel"));
				lista.attr("checked", $checked);
			}
		);
		$("#idPesquisa").hide();
		var filtros = $("#idPesquisa .filtro");
		if (filtros.length) {
			filtros.each(function() {
				if ($(this).val() != "") {
					$("#idPesquisa").show();
					return true;
				}
			});
		}
		$("#idLimparPesquisa").click(
			function() {
				$("#idPesquisa").find(".filtro").each(
					function() {
						$(this).val("");
					}
				);
                        var form = $("#idPesquisa").parents("form");
				form.submit();
			}
		);
		
		$(".janela_funcionario").click(function(event) {
			event.preventDefault();
			var func = new Funcionario($(this).attr("rel"));
			func.show();
            $('.fancybox').fancybox();
		});
		$(".link_excluir").click(function(event) {
			event.preventDefault();
			var obj = $(this);
			$.modalConfirmacao({ 
				"conteudo" : "Confirmar Exclus�o?",
				"titulo": "Aten��o",
				"id": "myModal" + obj.attr("id"),
				"confirma": function(value) {
					if (value) {
						window.location = obj.attr("href");
					}
				}
			});
		});
		$(".link_confirma").click(function(event) {
			event.preventDefault();
			var obj = $(this);
			$.modalConfirmacao({ 
				"conteudo" : "Confirmar Opera��o?",
				"titulo": "Aten��o",
				"id": "myModal" + obj.attr("id"),
				"confirma": function(value) {
					if (value) {
						window.location = obj.attr("href");
					}
				}
			});
		});
		
		$("a.show_imagem").click(showImagem);
		
		$.ajaxSetup( {
			"beforeSend": function() {
				$("<div>").addClass("ajax_aguarde")
				.css( { "position": "fixed",
					    "top": "5px",
						"right": "5px",
						"left": "auto",
						"width": "130px",
						"height": "40px", 
						"border": "2px solid #ccc",
						"background-color": "#fff",
						"z-index": "99999",
						"padding": "5px",
						"line-height": "40px",
						"font-weight": "bold",
						"text-align": "center" } )
				.html('<img src="<?php echo Escola_Util::getBaseUrl(); ?>/img/ajax-loader1.gif" alt="Carregando" height="20px" /> Carregando ...')
				.appendTo("body");
			},
			"complete": function() {
                            $("div.ajax_aguarde").remove();
			}
		} );

		//janela de confirma��o
		jQuery.modalConfirmacao = function(options){
			if (typeof options.titulo == "undefined") {
				options.titulo = "T�tulo Padr�o";
			}
			if (typeof options.conteudo == "undefined") {
				options.conteudo = "Mensagem Padr�o";
			}
			if (typeof options.id == "undefined") {
				options.id = "myModalConfirm";
			}
			var html = '<div id="' + options.id + '" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">';
			html += '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button><h3 id="myModalLabel">' + options.titulo + '</h3></div>';
			html += '<div class="modal-body"><p>' + options.conteudo + '</p></div>';
			html += '<div class="modal-footer">';
			html += '<button id="btnCancela" class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>';
			html += '<button id="btnConfirma" class="btn btn-primary">Confirmar</button>';
			html += '</div></div>';
			jQuery(html).appendTo("body");
			$("#" + options.id).on("confirma", function(event, data) {
				if (typeof options.confirma != "undefined") {
					options.confirma(data);
				}
				$(this).modal("hide");
			});
			$("#btnConfirma").bind("click", function() {
				$("#" + options.id).trigger("confirma", true );
			});
			$("#btnCancela").click(function() {
				$("#" + options.id).trigger("confirma", false );
			});
			$("#" + options.id).modal();
		};
		
		//mensagem
		jQuery.mensagemAlerta = function(options){
			mensagemAlerta(options)
		};
                
		//mensagem
		jQuery.limpaAlerta = function(options){
			limpaAlerta(options);
		};
                
		/*
		 * controlhe dos widgets
		 * */
		/*
		$('div.grid').Sortable(
				{
					accept 		:'widget',
					helperclass :'dragAjuda',
					activeclass :'dragAtivo',
					hoverclass 	:'dragHover',
					handle 		:'h2',
					opacity 	:1,					
					revert 		:true,
					floats 		:true,
					tolerance 	:'pointer',
					onChange : function(ser)
					{
					},
					onStart : function()
					{
						$.iAutoscroller.start(this, document.getElementsByTagName('body'));
					},
					onStop : function()
					{
						$.iAutoscroller.stop();
					}
				}
			);
		*/
               $(".sidebar-nav").hide();
               $("#menuicon").toggle(function(){
                   $("#corpo").addClass("content");
                   $(".sidebar-nav").show();
               }, function(){
                   $("#corpo").removeClass("content");
                  $(".sidebar-nav").hide(); 
               });
});
/*
 * Fun��es do sistema
 * */
function salvarFormulario(frm_name) {
	$("#" + frm_name).submit();
}

function confirmaExcluir(url) {
	if (confirm("CONFIRMA EXCLUS�O?")) {
		window.location = url;
	}
}

function confirma(url) {
	if (confirm("CONFIRMA OPERA��O?")) {
		window.location = url;
	}
}

function pesquisar() {
	var pesquisa = $("#idPesquisa");
	pesquisa.toggle();
	if (pesquisa.css("display") != "none") {
		$("#idPesquisa .filtro").first().focus();
	}
}

function maskara(zindex) {
	$("<div id='mask" + zindex + "' class='mask'></div>").css({"height": $(document).height(), "z-index" : zindex}).appendTo("body");
}

function validarCPF(wCPF) {
 kCPF = '';
 wRes = false;
 wdig = 0;
 wresto = 0;
// if ((wCPF.length == 14)&&(wCPF != '000.000.000-00')) {
 if (wCPF.length == 14) {	
  kCPF += wCPF.substr(0,3);
  kCPF += wCPF.substr(4,3);
  kCPF += wCPF.substr(8,3);
  kCPF += wCPF.substr(12,2);
  for (x=1; x<=9;x++) 
   wdig += (11 - x) * kCPF.substr(x-1,1);
  wresto = wdig % 11; 
  if ((wresto == 0)||(wresto == 1)) 
   wdig = 0;
  else
   wdig = 11 - wresto;
  if (wdig == kCPF.substr(9,1)) { 
   wdig = 0;
   for (x=1; x<=10;x++) 
    wdig += (12 - x) * kCPF.substr(x-1,1);
   wresto = wdig % 11; 
   if ((wresto == 0)||(wresto == 1)) 
    wdig = 0;
   else
    wdig = 11 - wresto;
   if (wdig == kCPF.substr(10,1)) 
    wRes = true;   
  }
 }
 else 
  wRes = false;
 return wRes;
}

function showImagem(event) {
	event.preventDefault();
	var img = new Image();
	img.src = $(this).attr("href");
	img.onload = function() {
		var max_height = window.innerHeight - 120;
		var max_width = window.innerWidth - 20;
		var ant_height = img.height;
		var ant_width = img.width;
		if (img.height > max_height) {
			img.height = max_height;
			img.width = ant_width * img.height / ant_height;
		}
		if (img.width > max_width) {
			img.width = max_width;
		}
		maskara(99);
		var tabela = $("<table>").addClass("lista_box").css( { "position": "fixed", "z-index": "100", "top": parseInt((max_height - img.height)/2)  + "px", "left": "50%", "width": img.width, "margin-left": "-" + parseInt(img.width/2) + "px" } ).appendTo($("body"));
		$("<tr><th>VISUALIAR IMAGEM</th></tr>").appendTo(tabela);
		var td_img = $("<td>").appendTo($("<tr>").appendTo(tabela));
		$("<input>").attr( { "type": "button", "value": "Fechar" } )
		.click(function() {
			$("#mask99").remove();
			$(this).parents("table.lista_box").remove();
		})
		.appendTo($("<td>").attr("align", "right").appendTo($("<tr>").appendTo(tabela)));
		$(img).appendTo(td_img);
	};
}

function criaEditor(idname) {
	return CKEDITOR.replace(idname, {
		toolbar : [ [ 'Source', '-', 'Font','FontSize', '-', 'Bold', 'Italic', 'Underline', 'Strike','-','Link', '-', 'Image', '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock', '-', 'Table' ] ]
	});
}

var Funcionario = function(id) {
	this.id = id;
}
Funcionario.prototype.id = 0;
Funcionario.prototype.zindex = 10;
Funcionario.prototype.add_mensagem = function(mensagem) {
	$('<div class="messages"><div><div class="ui-branco ui-icon ui-icon-check icon-position"></div>' + mensagem + '</div></div>').appendTo($("#jan_alert_" + this.id));
	$("#jan_alert_" + this.id).show();
}
Funcionario.prototype.add_error = function(erro) {
	$('<div class="errors"><div><div class="ui-amarelo ui-icon ui-icon-alert icon-position"></div>' + erro + '</div></div>').appendTo($("#jan_alert_" + this.id));
	$("#jan_alert_" + this.id).show();
}
Funcionario.prototype.limpar_mensagem = function() {
	$("#jan_alert_" + this.id).children().remove();
}
Funcionario.prototype.show = function() {
	var obj_atual = this;
	if (this.id > 0) {
		ajax_funcionario = $.ajax({
			"url" : "<?php echo Escola_Util::getBaseUrl(); ?>/funcionario/listar/format/json/",
			"type" : "POST",
			"data" : { "id" : this.id },
			"success" : function(result) {
				//maskara(obj_atual.zindex -1);
				var div_janela = $("<div>").attr({ "class": "modal hide fade", "id": "janela_funcionario_" + obj_atual.id}).css({ "width": "800px", "margin-left": "-400px" }).appendTo("body");
				$('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>Visualizar Funcion�rio</h3></div>').appendTo(div_janela);
				var tabela = $("<table>").attr( { "class": "lista_box" } ).appendTo(div_janela);
				var texto_tabela = '<tr><td><div align="center"><div id="jan_alert_' + obj_atual.id + '" style="display:none"></div>' +
								'<div style="position:absolute">' + result.funcionario.foto + '</div>'+
								 '<table width="100%">'+
									'<tr><td width="180px"><div align="right">Nome:</div></td><td><div align="left">' + result.funcionario.nome + '</div></td></tr>'+
									'<tr><td><div align="right">E-Mail:</div></td><td><div align="left">' + result.funcionario.email + '</div></td></tr>'+
									'<tr><td><div align="right">Matr�cula:</div></td><td><div align="left">' + result.funcionario.matricula + '</div></td></tr>'+
									'<tr><td><div align="right">Cargo:</div></td><td><div align="left">' + result.funcionario.cargo + '</div></td></tr>'+
									'<tr><td><div align="right">Lota��o:</div></td><td><div align="left">' + result.funcionario.lotacao + '</div></td></tr>';
				if (result.funcionario.telefone.length) { 									
					texto_tabela += '<tr><td><div align="right">Telefone(s):</div></td><td><div align="left">' + result.funcionario.telefone + '</div></td></tr>';
				}
<?php
$acl = Escola_Acl::getInstance();
$usuario = $acl->getUsuarioLogado();
$id_funcionario = '0';
$id_usuario = '0';
if ($usuario) {
	$id_usuario = $usuario->getId();
	$tb = new TbFuncionario();
	$funcionario = $tb->getPorUsuario($usuario);
	if ($funcionario) {
		$id_funcionario = $funcionario->getId();
?>
				if (obj_atual.id != <?php echo $id_funcionario; ?>) {
					texto_tabela += '<tr class="jan_linha_mensagem"><td><div align="right">Assunto:</div></td><td><div align="left"><input type="text" name="jan_assunto_' + obj_atual.id + '" id="jan_assunto_' + obj_atual.id + '" class="span7" /></div></td></tr>'+
									'<tr class="jan_linha_mensagem"><td><div align="right">Mensagem:</div></td><td><div align="left"><textarea name="jan_mensagem_' + obj_atual.id + '" id="jan_mensagem_' + obj_atual.id + '"></textarea></div></td></tr>'+
									'<tr><td></td></tr>';
				}
<?php }} ?>
				texto_tabela += '<tr><td></td><td><div align="left" id="botoes' + obj_atual.id + '">'+
											'</div></td></tr></table>'+
									'<br /></div></td></tr>';
				$(texto_tabela).appendTo(tabela);
				$(".jan_linha_mensagem").hide();
				if (obj_atual.id != <?php echo $id_funcionario; ?>) {				
					$("<input>").attr({ "type":"button", "value": "Enviar Mensagem", "id": "jan_mensagem_" + obj_atual.id, "class": "btn btn-primary" })
					.css( { "margin-right": "4px" } )
					.click(function() {
						if ($(".jan_linha_mensagem").css("display") == "none") {
							$(".jan_linha_mensagem").show();
							$("#jan_assunto_" + obj_atual.id).focus();
						} else {
							obj_atual.limpar_mensagem();
							if (!$("#jan_assunto_" + obj_atual.id).val().length && !editor.getData().length) {
								obj_atual.add_error("CAMPO ASSUNTO E COMENT�RIO PRECISAM SER PREENCHIDOS!");
							} else {
								var ajax_mensagem = $.ajax({
									"url" : "<?php echo Escola_Util::getBaseUrl(); ?>/mensagem/adicionar/format/json/",
									"type" : "POST",
									"data" : { "chave_destino": obj_atual.id,
											   "id_usuario": <?php echo $id_usuario; ?>,
											   "assunto": $('#jan_assunto_' + obj_atual.id).val(),
											   "mensagem": editor.getData() },
									"success" : function(result) {
										if (result.mensagem) {
											obj_atual.add_error(result.mensagem);
										} else {
											obj_atual.add_mensagem("OPERA��O EFETUADA COM SUCESSO");
											$('#jan_assunto_' + obj_atual.id).val("");
											editor.setData("");
											$(".jan_linha_mensagem").hide();
										}
									}
								});
							}
						}
					}).appendTo($("#botoes" + obj_atual.id));
				}
				$('<button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>').appendTo($("#botoes" + obj_atual.id));
				if (obj_atual.id != <?php echo $id_funcionario; ?>) {
					var editor = criaEditor('jan_mensagem_' + obj_atual.id);
				}
				//div_janela.show();
				div_janela.modal();
				div_janela.on("hidden", function() {
					$(this).remove();
					if (obj_atual.id != <?php echo $id_funcionario; ?>) {
						CKEDITOR.remove(editor);
					}
				});
				$("a.show_imagem").bind("click", showImagem);				
			}
		});
	}
}

var Paginacao = function() {
	
}
Paginacao.prototype.total_pagina = 0;
Paginacao.prototype.pagina_atual = 0;
Paginacao.prototype.primeira = 0;
Paginacao.prototype.ultima = 0;
Paginacao.prototype.nome_funcao = "setPage";
Paginacao.id_formulario = "";

Paginacao.prototype.render = function() {
	if (this.total_pagina > 10) {
		var primeira = this.pagina_atual - 5;
		if (primeira > 0) {
			this.primeira = primeira;
			var ultima = this.pagina_atual + 4;
		} else {
			var ultima = this.primeira + 9;
		}
		if (ultima < this.ultima) {
			this.ultima = ultima;
		} else {
			this.primeira = this.ultima - 9;
		}
	}
	var html = '<div class="pagination pagination-centered"><ul>';
	html += '<!-- Previous page link -->';
	if (this.pagina_atual > this.primeira) {
		html += '<li><a href="javascript:' + this.nome_funcao + '(' + (this.pagina_atual - 1) + ')">&lt; Anterior</a></li>';
	} else {
		html += '<li class="disabled"><a href="#">&lt; Anterior</a></li>';
	}
	html += '<!-- Numbered page links -->';
	for (var i = this.primeira; i <= this.ultima; i++) {
		if (this.pagina_atual != i) {
			html += '<li><a href="javascript:' + this.nome_funcao + '(' + i + ')">' + i + '</a></li>';
		} else {
			html += '<li class="disabled"><a href="#">' + i + '</a></li>';
		}
	}
	html += '<!-- Next page link -->';
	if (this.pagina_atual < this.ultima) {
		html += '<li><a href="javascript:' + this.nome_funcao + '(' + (this.pagina_atual + 1) + ')">Pr�xima &gt;</a></li>';
	} else {
		html += '<li class="disabled"><a href="#">Pr�xima &gt;</span></a></li>';
	}
    html += '</ul></div>';
	return html;
}

function zero(wvar, wtam) {
	if (wvar.length < wtam) {
		ret = "";
		for (var i = wvar.length; i <= wtam; i++) {
			ret += "0";
		}
		return ret + wvar;
	}
	return wvar;
}

function ucFirst(txt) {
	if (!txt) {
		return txt;
	}
	return txt.charAt(0).toUpperCase() + txt.slice(1).toLowerCase();	
}

function mensagemAlerta(options) {

	if (options.tipo === undefined) {
		options.tipo = "erro";
	}

	if (options.mensagem === undefined) {
		options.mensagem = "";
	}

	if (!(options.mensagem && options.mensagem.length)) {
		return;
	}

	if (!_.isArray(options.mensagem)) {
		options.mensagem = [options.mensagem];
	}

	let nome_classe = "alert-info";
	if (options.tipo == "erro") {
		nome_classe = "alert-error";
	}

	$("<button>", {
		type: 'button', 
		class: 'close',
		'data-dismiss': 'alert',
		text: 'x'
	});

	const div_alert = $("<div>", {
		class: 'alert ' + nome_classe,
	});

	$("<button>", {
		type: 'button', 
		class: 'close',
		'data-dismiss': 'alert',
		text: 'x'
	}).appendTo(div_alert);

	const ul = $("<ul>").appendTo(div_alert);

	for (const erro of options.mensagem) {
		$("<li>", {
			text: erro
		}).appendTo(div_alert);
	}

	jQuery(".conteudo_controller").before(div_alert);

	if (options.timeout && !isNaN(options.timeout)) {
		if (options.timeout < 2000) {
			options.timeout = 2000;
		}

		setTimeout(() => {
			limpaAlerta();
		}, options.timeout);
	}

}

function limpaAlerta(options) {
	jQuery("div.alert").fadeOut().remove();
}

function errorMessage(error) {
	if (!error) {
		return 'Falha ao executar operação.';
	}

	if (typeof error === 'string') {
		return error;
	}

	if (error.message) {
		return error.message;
	}

	return 'Falha ao executar operação.';
}

function executaRequest(options) {
	options = $.extend({
		metodo: 'POST',
		dados: []
	}, options);

	if (!options.controller) {
		throw new Error("Controller não informado na requisição.");
	}

	if (!options.action) {
		throw new Error("Action não informado na requisição.");
	}

	return new Promise(function(resolve, reject) {
		let ajax_obj = $.ajax({
			"url": "<?php echo Escola_Util::getBaseUrl(); ?>/" + options.controller + "/" + options.action + "/format/json/",
			"type": options.metodo,
			"data": options.dados,
			"success": function(result) {
				if (result.erro && result.erro.length) {
					return reject(result.erro);
				}

				if (!result.result) {
					return resolve(null);
				}

				return resolve(result.result);
			}
		});

	});
	
}

function facebookLoading() {

	return "<img width='15px' src='<?php echo Escola_Util::getBaseUrl(); ?>/public/img/facebook-loading.gif' />";

}

function formataCPF(cpf) {
	cpf = cpf.replace(/[^\d]/g, "");
	cpf = _.padStart(cpf, 11, '0');
	return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
}
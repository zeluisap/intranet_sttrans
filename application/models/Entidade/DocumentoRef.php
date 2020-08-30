<?php
class DocumentoRef extends Escola_Entidade {
    
	public function getErrors() {
		$msgs = array();
		if (!$this->id_documento) {
			$msgs[] = "CAMPO DOCUMENTO OBRIGATÓRIO!";
		}
		$tb = $this->getTable();
		$sql = $tb->select();
		$sql->where("id_documento = " . $this->id_documento);
		$sql->where("tipo = '{$this->tipo}' ");
		$sql->where("chave = {$this->chave}");
		$sql->where("id_documento_ref <> '" . $this->getId() . "'");
		$rg = $tb->fetchAll($sql);
		if ($rg && count($rg)) {
			$msgs[] = "REFERÊNCIA DE DOCUMENTO JÁ CADASTRADA!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;
	}
    
	public function pegaObjeto() {

        switch (strtoupper($this->tipo)) {
            case "A": 
                return TbArquivo::pegaPorId($this->chave);

            case "F": 
                return TbFuncionario::pegaPorId($this->chave);

            case "D":
            case "O": 
                return TbDocumento::pegaPorId($this->chave);

            case "N": 
                return TbArquivo::pegaPorId($this->chave);

            case "T": 
                return TbTransporte::pegaPorId($this->chave);
        }

        return null;
	}
	
	public function delete() {
		$documento = $this->findParentRow("TbDocumento");
		$flag = parent::delete();
		if ($documento && $documento->pessoal()) {
			$documento->delete();
		}
		return $flag;
	}
	
	public function listarTipo() {
		return array("F" => "FUNCIONÁRIO",
					 "D" => "DOCUMENTO VINCULADO",
					 "A" => "ARQUIVO",
					 "N" => "ANEXO",
					 "O" => "DOCUMENTO ORIGINAL");
	}
	
	public function mostrarTipo() {
		$tipos = $this->listarTipo();
		if (array_key_exists($this->tipo, $tipos)) {
			return $tipos[$this->tipo];
		}
		return "";
	}

    public function toForm($dados = array()) {
        $view = false;
        if (isset($dados["view"]) && $dados["view"]) {
            $view = $dados["view"];
        }
        if ($view) {
            $id_documento_tipo = 0;
            $documento = $view->documento;
            if (!$documento) {
                $documento = $this->findParentRow("TbDocumento");
            }
            if (!$documento) {
                $tb_doc = new TbDocumento();
                $documento = $tb_doc->createRow();
            }
            $id_documento_tipo = $documento->id_documento_tipo;
            ob_start();
?>
<script type="text/javascript">
var ajax_obj = false;
$(document).ready(
    function() {
        var tds = [];
        var tipo_docs = [];
<?php
$tb = new TbDocumentoTipoTarget();
$sql = $tb->select();
$sql->from(array("dtt" => "documento_tipo_target"));
if (isset($dados["documento_tipo_target"]) && is_array($dados["documento_tipo_target"]) && count($dados["documento_tipo_target"])) {
    $sql->where("dtt.chave in ('" . implode("', '", $dados["documento_tipo_target"]) . "')");
}
$tb_dt = new TbDocumentoTipo();
$sql_dt = $tb_dt->select();
$sql_dt->from(array("dt" => "documento_tipo"), array("dt.id_documento_tipo"));
$sql_dt->where("dt.id_documento_tipo_target = dtt.id_documento_tipo_target");
$sql->where("exists ({$sql_dt}) ");
$sql->order("dtt.descricao");
//$dtts = $tb->listar();
$dtts = $tb->fetchAll($sql);
if ($dtts) {
    foreach ($dtts as $dtt) {
            $tb = new TbDocumentoTipo();
            $dts = $tb->listar(array("filtro_id_documento_tipo_target" => $dtt->getId()));
            if ($dts) {
?>
                tds[<?php echo $dtt->getId(); ?>] = [];
<?php 
                $contador = 0;
                foreach ($dts as $dt) {
?>
                    tds[<?php echo $dtt->getId(); ?>][<?php echo $contador; ?>] = { "id": <?php echo $dt->getId(); ?>, "descricao": '<?php echo $dt->toString(); ?>', 'possui_numero': '<?php echo $dt->possui_numero; ?>' };
                    tipo_docs[<?php echo $dt->getId(); ?>] = { "id": <?php echo $dt->getId(); ?>, "descricao": '<?php echo $dt->toString(); ?>', 'possui_numero': '<?php echo $dt->possui_numero; ?>' };
<?php $contador++; }}}} ?>
        $("#id_documento_tipo_target").change(
            function() {
                $(".linha_documento_tipo").hide();
                $("#id_documento_tipo").children().remove();
                if ($(this).val().length) {
                    $('<option value="">==> SELECIONE <== </option>').appendTo($("#id_documento_tipo"));
                    for (var i = 0; i < tds[$(this).val()].length; i++) {
                        var obj = tds[$(this).val()][i];
                        var selected = "";
                        if (obj.id == '<?php echo $id_documento_tipo; ?>') {
                            selected = " selected ";
                        }
                        $('<option value="' + obj.id + '" ' + selected + '>' + obj.descricao + '</option>').appendTo($("#id_documento_tipo"));
                    }
                    $(".linha_documento_tipo").show();
                }
<?php
$tb = new TbDocumentoTipoTarget();
$dtt = $tb->getPorChave("A");
if ($dtt) {
?>
                $(".linha_cadastro, .linha_documento_cadastro, .linha_documento").hide();
                if ($(this).val() == '<?php echo $dtt->getId(); ?>') {
                    $(".linha_cadastro").show();
                }
<?php } ?>
            }
        ).change();
        $("#id_documento_tipo").change(
            function() {
            	var obj = false;
            	if ($(this).val() && $(this).val().length) {
            		var obj = tipo_docs[$(this).val()];
            	}
                    $(".linha_documento, .linha_cadastro, .linha_documento_cadastro").hide();   
                	if (obj) {
                		if  (obj.possui_numero == "S") {
		                    if ($(this).children().length && $(this).val().length ) {
		                        $(".linha_documento").show();
		                    }
	            	    } else {
                                $(".linha_cadastro").show();	                
                            }
                        }
            }
        ).change();
        $("#link_documento").click(
            function() {
                $("#jan_pagina, #id_documento").val("");
                $("#show_documento").text("");
                $("#operacao").val("documento");
                $("#janela_documento").modal("show");
                limparFiltro();
            }
        );
        $(".bt_documento_cancelar").click(
            function() {
                fecharJanela();
            }
        );
        $("#bt_documento_limpar").click(
            function() {
                limparFiltro();
            }
        );
        $("#formulario").submit(
            function() {
                if ($("#operacao").val() == "documento") {
                    procurarDocumento();
                    return false;
                }
                return true;
            }
        );
        $("#bt_documento_novo").click(
            function() {
                fecharJanela();
                $(".linha_documento").hide();
                $(".linha_cadastro, .linha_documento_cadastro").show();
                $(".linha_documento_cadastro input").first().focus();
                $(".field").val("");
            }
        );
        $("#janela_documento").css( { "width": "1000px", "margin-left": "-500px" } ); 
        $("#janela_documento").modal("hide").on("shown", function() {
            $(".filtro").first().focus();
        });
        $("#janela_documento").keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                procurarDocumento();
            }
        });
    }
);

function setPage($page) {
    $("#jan_pagina").val($page);
    $("#formulario").submit();
}

function limparFiltro() {
    $(".filtro").val("");
    procurarDocumento();
}

function fecharJanela() {
    $("#operacao").val("");
    $("#janela_documento").modal("hide");
}

function procurarDocumento() {    
    if (ajax_obj) {
        ajax_obj.abort();
    }
    $(".linha_resultado, .paginacao_arquivo").remove();
    ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/documento/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_numero": $("#filtro_numero").val(),
                   "filtro_ano": $("#filtro_ano").val(),
                   "filtro_resumo": $("#filtro_resumo").val(),
                   "filtro_id_documento_tipo": $("#id_documento_tipo").val(),
                   "pagina_atual": $("#jan_pagina").val(),
                   "qtd_por_pagina": 15 },
        "success" : function(result) {
            if (result.items && result.items.length) {
                infos = [];
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    infos[i] = { "id": item.id,
                                 "data_hora":  item.data_hora,
                                 "tipo": item.tipo,
                                 "numero": item.numero,
                                 "resumo": item.resumo }; 
                    var tr = $('<tr class="linha_resultado"></tr>').appendTo($("#documento_resposta"));
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.data_hora + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.numero+ '</a></td>').appendTo(tr);
                    $('<td><a href="#" class="link_seleciona" id="' + item.id + '">' + item.resumo + '</a></td>').appendTo(tr);
                    tr.appendTo($(".corpo_documento"));
                }
                $(".link_seleciona").bind("click",
                    function(event) {
                        event.preventDefault();
                        $("#id_documento").val($(this).attr("id"));
                        $("#operacao").val("set_documento");
                        $("#formulario").submit();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                var html = paginacao.render();
                $('<div class="paginacao_arquivo">' + html + '</div>').appendTo($("#janela_documento .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='5' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#documento_resposta"));
            }
        }
    });    
}
</script>
<?php
                $tb = new TbDocumentoTipoTarget();
                //$dtts = $tb->listar();
                if ($dtts) {
                    $id_dtt = 0;
                    $dt = $documento->findParentRow("TbDocumentoTipo");
                    if ($dt) {
                        $id_dtt = $dt->id_documento_tipo_target;
                    }
                    if (isset($view->dados["id_documento_tipo_target"]) && $view->dados["id_documento_tipo_target"]) {
                        $id_dtt = $view->dados["id_documento_tipo_target"];
                    }
?>
                        <div class="control-group">
                            <label for="id_documento_tipo_target" class="control-label">Tipo:</label>
                            <div class="controls">
                                <select name="id_documento_tipo_target" id="id_documento_tipo_target" class="span6">
                                    <option value="" <?php echo (!$id_dtt)?"selected":""; ?>>==> SELECIONE <==</option>
<?php foreach ($dtts as $dtt) { ?>
                                    <option value="<?php echo $dtt->getId(); ?>" <?php echo ($dtt->getId() == $id_dtt)?"selected":""; ?>><?php echo $dtt->toString(); ?></option>
<?php } ?>
                                </select>
                            </div>
                        </div>
<?php } ?>
                        <div class="linha_documento_tipo control-group" style="display:none">
                            <label for="id_documento_tipo" class="control-label">Tipo de Documento:</label>
                            <div class="controls">
                                <select name="id_documento_tipo" id="id_documento_tipo" class="span6">
                                </select>
                            </div>
                        </div>
                        
    <div id="janela_documento" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="myModalLabel">Localizar Documento</h4>
        </div>
        <div class="modal-body">
            <div class="well well-small">
                <fieldset>
                    <div class="control-group">
                        <label for="filtro_numero" class="control-label">Número / Ano:</label>
                        <div class="controls">
                            <input type="text" name="filtro_numero" id="filtro_numero" value="" class="filtro span1" /> / <input type="text" name="filtro_ano" id="filtro_ano" value="" class="filtro span1" />
                        </div>
                    </div>
                    <div class="control-group">
                        <label for="filtro_resumo" class="control-label">Resumo:</label>
                        <div class="controls">
                            <textarea name="filtro_resumo" id="filtro_resumo" rows="5" class="filtro span5"></textarea>
                        </div>
                    </div>
                </fieldset>
            </div>
            <table id="documento_resposta" class="table table-striped table-bordered">
                <thead class="head_documento">
                    <th>ID</th>
                    <th>Data / Hora Criação</th>
                    <th>Tipo</th>
                    <th>Número</th>
                    <th>Resumo</th>
                </thead>
                <tbody class="corpo_documento"></tbody>
            </table>            
        </div>
        <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
            <input type="button" value="ADICIONAR" class="btn" id="bt_documento_novo" />
            <input type="button" value="LIMPAR FILTRO" id="bt_documento_limpar" class="btn" />
            <input type="submit" value="PROCURAR" class="btn btn-primary" />
       </div>
    </div>
<?php if ($documento->getId()) { ?>
                       <input type="hidden" name="id_documento" id="id_documento" value="<?php echo $documento->getId(); ?>" />
<?php } ?>
                        <div class="linha_documento control-group" style="display:none">
                            <label for="procedencia" class="control-label">Documento:</label>
                            <div class="controls">
                                <div class="input-append">
                                    <input type="text" name="show_documento" id="show_documento" value="<?php echo ($documento->getId())?$documento->toString():""; ?>" disabled class="input-xxlarge" />
                                    <div class="add-on">
                                        <a href="#" id="link_documento" title="Selecionar Documento">
                                            <i class="icon-search"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="linha_documento_cadastro control-group" style="display:none">
                            <label for="numero" class="control-label">Número:</label>
                            <div class="controls">
                                <input type="text" name="numero" id="numero" class="field span1" /> / <input type="text" name="ano" id="ano" class="field span1" />
                            </div>
                        </div>
                        <div class="linha_cadastro control-group">
                            <label for="resumo" class="control-label">Observações:</label>
                            <div class="controls">
                                <textarea name="resumo" id="resumo" rows="4" class="field span5"><?php echo $documento->resumo; ?></textarea>
                            </div>
                        </div>
                        <div class="linha_cadastro control-group">
                            <label for="localizacao_fisica" class="control-label">Localização Física:</label>
                            <div class="controls">
                                <textarea name="localizacao_fisica" id="localizacao_fisica" rows="4" class="field span5"><?php echo $documento->localizacao_fisica; ?></textarea>
                            </div>
                        </div>
                        <div class="linha_cadastro control-group">
                            <label for="arquivo" class="control-label">Arquivo:</label>
                            <div class="controls">
                                <input type="file" name="arquivo" id="arquivo" size="50" class="field" />
                            </div>
                        </div>
<?php
            $html = ob_get_contents();
            ob_end_clean();
            return $html;
        }
        return false;
    }
}
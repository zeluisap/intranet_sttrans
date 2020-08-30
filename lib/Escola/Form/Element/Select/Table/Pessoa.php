<?php
class Escola_Form_Element_Select_Table_Pessoa extends Escola_Form_Element_Select_Table {
    
    public function init() {
        parent::init();
        $this->setPkName("id_pessoa");
        $this->setModel("TbPessoa");
    }
	
	public function render(Zend_View_Interface $view = null) {
        $tb = new TbPessoaTipo();
        $tps = $tb->listar();
        $pessoa = TbPessoa::pegaPorId($this->getValue());
        $txt = "";
        if ($pessoa) {
            $txt = $pessoa->toString();
        }
        ob_start();
?>
<script type="text/javascript">
var <?php echo $this->getName(); ?>_pessoas = [];
$(document).ready(function() {
    $(".link_<?php echo $this->getName(); ?>").click(function(event) {
        event.preventDefault();
        $("#janela_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_pessoa();
    });
    $("#janela_<?php echo $this->getName(); ?>").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_atualiza_pessoa();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_atualiza_pessoa();
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_limpar_filtro").click(function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_pessoa();
    });
    $("#<?php echo $this->getName(); ?>_id_pessoa_tipo").change(function() {
        $(".linha_PF, .linha_PJ").hide();
<?php 
$tb = new TbPessoaTipo();
$pt = $tb->getPorChave("PF");
if ($pt) {
?>
        if ($(this).val() == "<?php echo $pt->getId(); ?>") {
            $(".linha_PF").show();
        }
<?php } 
$pt = $tb->getPorChave("PJ");
if ($pt) {
?>
        if ($(this).val() == "<?php echo $pt->getId(); ?>") {
            $(".linha_PJ").show();
        }
<?php } ?>
    }).change();
})

function <?php echo $this->getName(); ?>_atualiza_pessoa() {
    $("#<?php echo $this->getName(); ?>_resposta .corpo_destino tr, .<?php echo $this->getName(); ?>_paginacao").remove();
    $("#<?php echo $this->getName(); ?>_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/pessoalistarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_id_pessoa_tipo": $("#<?php echo $this->getName(); ?>_id_pessoa_tipo").val(),
                   "filtro_cpf": $("#<?php echo $this->getName(); ?>_cpf").val(),
                   "filtro_cnpj": $("#<?php echo $this->getName(); ?>_cnpj").val(),
                   "filtro_nome": $("#<?php echo $this->getName(); ?>_nome").val(),
                   "pagina_atual": $("#<?php echo $this->getName(); ?>_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            <?php echo $this->getName(); ?>_pessoas = [];
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    <?php echo $this->getName(); ?>_pessoas[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.tipo + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.documento + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.nome + '</a></td>').appendTo(tr);
                    tr.appendTo($("#<?php echo $this->getName(); ?>_resposta .corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_<?php echo $this->getName(); ?>").val(<?php echo $this->getName(); ?>_pessoas[$(this).attr("id")].documento + " - " + <?php echo $this->getName(); ?>_pessoas[$(this).attr("id")].nome);
                        $("#<?php echo $this->getName(); ?>").val(<?php echo $this->getName(); ?>_pessoas[$(this).attr("id")].id);
                        <?php echo $this->getName(); ?>fechar();
                        $("#<?php echo $this->getName(); ?>").change();
                    }
                );
                var paginacao = new Paginacao();
                paginacao.total_pagina = result.total_pagina;
                paginacao.pagina_atual = result.pagina_atual;
                paginacao.primeira = result.primeira;
                paginacao.ultima = result.ultima;
                paginacao.nome_funcao = "<?php echo $this->getName(); ?>_set_page";
                var html = paginacao.render();
                $('<div class="<?php echo $this->getName(); ?>_paginacao">' + html + '</td></tr>').appendTo($("#janela_<?php echo $this->getName(); ?> .modal-body"));
            } else {
                $("<tr class='linha_resultado'><td colspan='3' align='center'>NEHUM REGISTRO LOCALIZADO!</td></tr>").appendTo($("#<?php echo $this->getName(); ?>_resposta"));
            }
            $("#janela_<?php echo $this->getName(); ?> .filtro").first().focus();
            $("#<?php echo $this->getName(); ?>_resposta").show();
        }
    });
}

function <?php echo $this->getName(); ?>fechar() {
    $("#janela_<?php echo $this->getName(); ?>").modal("hide");
}

function <?php echo $this->getName(); ?>_set_page(pagina) {
    $("#<?php echo $this->getName(); ?>_pagina").val(pagina);
    <?php echo $this->getName(); ?>_atualiza_pf();
}
</script>
<input type="hidden" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" value="<?php echo $this->getValue(); ?>" />
<input type="hidden" name="<?php echo $this->getName(); ?>_pagina" id="<?php echo $this->getName(); ?>_pagina" />
<div id="janela_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Pessoa Física</h3>
    </div>
    <div class="modal-body">
<?php if ($tps) { ?>
        <div class="well well-small">
            <fieldset>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_id_pessoa_tipo" class="control-label">Tipo:</label>
                    <div class="controls">
                        <select name="<?php echo $this->getName(); ?>_id_pessoa_tipo" id="<?php echo $this->getName(); ?>_id_pessoa_tipo" class="filtro">
                            <option value="">==> SELECIONE <==</option>
<?php foreach ($tps as $tp) { ?>
                            <option value="<?php echo $tp->getId(); ?>"><?php echo $tp->toString(); ?></option>
<?php } ?>                            
                        </select>
                    </div>
                </div>
                <div class="control-group linha_PF">
                    <label for="<?php echo $this->getName(); ?>_cpf" class="control-label">C.P.F.:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_cpf" id="<?php echo $this->getName(); ?>_cpf" value="" class="filtro cpf span4" />
                    </div>
                </div>
                <div class="control-group linha_PJ">
                    <label for="<?php echo $this->getName(); ?>_cnpj" class="control-label">C.N.P.J.:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_cnpj" id="<?php echo $this->getName(); ?>_cnpj" value="" size="60" class="filtro span4 cnpj" />
                    </div>
                </div>                
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_nome" class="control-label">Nome:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_nome" id="<?php echo $this->getName(); ?>_nome" value="" size="60" class="filtro span7" />
                    </div>
                </div>
            </fieldset>
        </div>
<?php } ?>
        <table id="<?php echo $this->getName(); ?>_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Documento</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody class="corpo_destino"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Limpar Filtro" id="btn_limpar_filtro" class="btn" />
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>
<div class="control-group" id="linha_<?php echo $this->getName(); ?>">
    <label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
    <div class="controls">
        <div class="input-append">
            <input type="text" name="show_<?php echo $this->getName(); ?>" id="show_<?php echo $this->getName(); ?>" disabled class="input-xxlarge" value="<?php echo $txt; ?>" />
            <div class="add-on">
                <a href="#" class="link_<?php echo $this->getName(); ?>">
                    <i class="icon-search"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
	}
}
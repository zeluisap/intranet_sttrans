<?php
class Escola_Form_Element_Select_Table_PessoaFisica extends Escola_Form_Element_Select_Table {
    
    protected $hab_adicionar = true;
    
    public function get_hab_adicionar() {
        return $this->hab_adicionar;
    }
    
    public function set_hab_adicionar($hab_adicionar) {
        $this->hab_adicionar = $hab_adicionar;
    }
    
    public function init() {
        parent::init();
        $this->setPkName("id_pessoa_fisica");
        $this->setModel("TbPessoaFisica");
    }
	
    public function render(Zend_View_Interface $view = null) {
        $pj = TbPessoaFisica::pegaPorId($this->getValue());
        $txt_pj = "";
        if ($pj) {
            $txt_pj = $pj->toString();
        }
        ob_start();
?>
<script type="text/javascript">
var pjs = [];
$(document).ready(function() {
    $(".link_<?php echo $this->getName(); ?>").click(function(event) {
        event.preventDefault();
        $("#janela_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_pf();
    });
    $("#janela_<?php echo $this->getName(); ?>").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_atualiza_pf();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_atualiza_pf();
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_limpar_filtro").click(function() {
        $("#janela_<?php echo $this->getName(); ?> .filtro, #<?php echo $this->getName(); ?>_pagina").val("");
        <?php echo $this->getName(); ?>_atualiza_pf();
    });
<?php if ($this->hab_adicionar) { ?>
    $("#janela_add_<?php echo $this->getName(); ?>").css({ "width": "900px", "margin-left": "-450px" }).modal("hide");
    $("#janela_add_<?php echo $this->getName(); ?>").on("show", function() {
        $("#janela_add_<?php echo $this->getName(); ?> .alert").hide();
    });
    $("#janela_add_<?php echo $this->getName(); ?>").on("shown", function() {
        $("#<?php echo $this->getName(); ?>_add_nome").focus();
    });
    $("#janela_add_<?php echo $this->getName(); ?> .field").keypress(function(event) {
        if (event.which == 13) {
            event.preventDefault();
            <?php echo $this->getName(); ?>_salvar_pf();
        }
    });
    $("#janela_<?php echo $this->getName(); ?> #btn_add").click(function() {
        $("#janela_<?php echo $this->getName(); ?>").modal("hide");
        $("#janela_cpf_<?php echo $this->getName(); ?>").modal("show");
    });
    $("#janela_add_<?php echo $this->getName(); ?> #btn_salvar").click(function() {
        <?php echo $this->getName(); ?>_salvar_pf();
    });
    $("#janela_cpf_<?php echo $this->getName(); ?>").modal("hide");
    $("#janela_cpf_<?php echo $this->getName(); ?>").on("show", function() {
        $("#<?php echo $this->getName(); ?>_add_cpf").val("");
        $("#janela_cpf_<?php echo $this->getName(); ?> .alert").hide();
    });
    $("#janela_cpf_<?php echo $this->getName(); ?>").on("shown", function() {
        $("#<?php echo $this->getName(); ?>_add_cpf").focus();
    });
    $("#janela_cpf_<?php echo $this->getName(); ?>").keypress(function(event) {
        event.preventDefault();
        <?php echo $this->getName(); ?>_cpf_procurar(0);
    });
    $("#janela_cpf_<?php echo $this->getName(); ?> #btn_procurar").click(function() {
        <?php echo $this->getName(); ?>_cpf_procurar(0);
    });
<?php } ?>
    $(".link_cancelar_<?php echo $this->getName(); ?>").click(function() {
        $("#<?php echo $this->getName(); ?>, #show_<?php echo $this->getName(); ?>").val("");
        <?php echo $this->getName(); ?>_load();
    });
    $("#<?php echo $this->getName(); ?>").change(function() {
        <?php echo $this->getName(); ?>_load();
    });
    $(".link_alterar_<?php echo $this->getName(); ?>").click(function() {
        <?php echo $this->getName(); ?>_cpf_procurar($("#<?php echo $this->getName(); ?>").val());
    });
});

function <?php echo $this->getName(); ?>_load() {
    var valor = $("#<?php echo $this->getName(); ?>").val();
    var controles = $(".<?php echo $this->getName(); ?>_bt_alterar, .<?php echo $this->getName(); ?>_bt_cancelar");
    if (valor.length) {
        controles.show();
    } else {
        controles.hide();
    }
}

function <?php echo $this->getName(); ?>_atualiza_pf() {
    $("#<?php echo $this->getName(); ?>_resposta .corpo_destino tr, .<?php echo $this->getName(); ?>_paginacao").remove();
    $("#<?php echo $this->getName(); ?>_resposta").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/listarporpagina/format/json/",
        "type" : "POST",
        "data" : { "filtro_cpf": $("#<?php echo $this->getName(); ?>_cpf").val(),
                   "filtro_nome": $("#<?php echo $this->getName(); ?>_nome").val(),
                   "pagina_atual": $("#<?php echo $this->getName(); ?>_pagina").val(),
                   "qtd_por_pagina": 20 },
        "success" : function(result) {
            pjs = [];
            if (result.items && result.items.length) {
                for (var i = 0; i < result.items.length; i++) {
                    var item = result.items[i];
                    pjs[i] = item;
                    var tr = $('<tr class="linha_resultado"></tr>');
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.id + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.cpf + '</a></td>').appendTo(tr);
                    $('<td><a href="#" id="' + i + '" class="link_destino">' + item.nome + '</a></td>').appendTo(tr);
                    tr.appendTo($("#<?php echo $this->getName(); ?>_resposta .corpo_destino"));
                }
                $(".link_destino").click(
                    function(event) {
                        event.preventDefault();
                        $("#show_<?php echo $this->getName(); ?>").val(pjs[$(this).attr("id")].nome);
                        $("#<?php echo $this->getName(); ?>").val(pjs[$(this).attr("id")].id);
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

<?php if ($this->hab_adicionar) { ?>
function <?php echo $this->getName(); ?>_salvar_pf() {
    $("#janela_add_<?php echo $this->getName(); ?> .alert").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/salvar/format/json/",
        "type" : "POST",
        "data" : { "flag_errors": true,
                   "id_pessoa_fisica": $("#<?php echo $this->getName(); ?>").val(),
                   "cpf": $("#<?php echo $this->getName(); ?>_add_cpf").val(),
                   "nome": $("#<?php echo $this->getName(); ?>_add_nome").val(),
                   "email": $("#<?php echo $this->getName(); ?>_add_email").val(),
                   "nome_pai": $("#<?php echo $this->getName(); ?>_add_nome_pai").val(),
                   "nome_mae": $("#<?php echo $this->getName(); ?>_add_nome_mae").val(),
                   "id_estado_civil": $("#<?php echo $this->getName(); ?>_add_id_estado_civil").val(),
                   "data_nascimento": $("#<?php echo $this->getName(); ?>_add_data_nascimento").val(),
                   "identidade_numero": $("#<?php echo $this->getName(); ?>_add_identidade_numero").val(),
                   "identidade_orgao_expedidor": $("#<?php echo $this->getName(); ?>_add_identidade_orgao_expedidor").val(),
                   "identidade_id_uf": $("#<?php echo $this->getName(); ?>_add_identidade_id_uf").val(),
                   "telefone_fixo": $("#<?php echo $this->getName(); ?>_add_telefone_fixo").val(), 
                   "telefone_celular": $("#<?php echo $this->getName(); ?>_add_telefone_celular").val(),
                   "logradouro": $("#<?php echo $this->getName(); ?>_add_logradouro").val(),
                   "numero": $("#<?php echo $this->getName(); ?>_add_numero").val(), 
                   "complemento": $("#<?php echo $this->getName(); ?>_add_complemento").val(),
                   "cep": $("#<?php echo $this->getName(); ?>_add_cep").val(),
                   "id_bairro": $("#<?php echo $this->getName(); ?>_add_endereco_id_bairro").val()},
        "success" : function(result) {
            if (result.erro && result.erro.length) {
                $("#janela_add_<?php echo $this->getName(); ?> .mensagem_erro").html(result.erro);
                $("#janela_add_<?php echo $this->getName(); ?> .alert").show();
            }
            if (result.id > 0) {
                $("#show_<?php echo $this->getName(); ?>").val(result.descricao);
                $("#<?php echo $this->getName(); ?>").val(result.id);
                $("#janela_add_<?php echo $this->getName(); ?>").modal("hide");
                $("#<?php echo $this->getName(); ?>").change();
            }
        }
    });
}

function <?php echo $this->getName(); ?>_cpf_procurar(id_pf) {
    $("#janela_cpf_<?php echo $this->getName(); ?> .alert").hide();
    var ajax_obj = $.ajax({
        "url" : "<?php echo Escola_Util::getBaseUrl(); ?>/pessoa/dados/format/json/",
        "type" : "POST",
        "data" : { "cpf": $("#<?php echo $this->getName(); ?>_add_cpf").val(), "id_pessoa_fisica" : id_pf },
        "success" : function(result) {
            if (result.erro && result.erro.length) {
                $("#janela_cpf_<?php echo $this->getName(); ?> .mensagem_erro").html(result.erro);
                $("#janela_cpf_<?php echo $this->getName(); ?> .alert").show();
            }
            if (result.obj) {
                $("#add_id_pessoa_fisica").val(result.obj.id_pessoa_fisica);
                if (!isNaN(result.obj.id_pessoa_fisica) && (parseInt(result.obj.id_pessoa_fisica) > 0)) {
                    $(".<?php echo $this->getName(); ?>_show_cpf").text(result.obj.cpf);
                } else {
                    $(".<?php echo $this->getName(); ?>_show_cpf").text($("#<?php echo $this->getName(); ?>_add_cpf").val());
                }
                $("#<?php echo $this->getName(); ?>_add_nome").val(result.obj.nome);
                $("#<?php echo $this->getName(); ?>_add_email").val(result.obj.email);
                $("#<?php echo $this->getName(); ?>_add_nome_pai").val(result.obj.nome_pai);
                $("#<?php echo $this->getName(); ?>_add_nome_mae").val(result.obj.nome_mae);
                $("#<?php echo $this->getName(); ?>_add_id_estado_civil").val(result.obj.id_estado_civil);
                $("#<?php echo $this->getName(); ?>_add_data_nascimento").val(result.obj.data_nascimento);
                $("#<?php echo $this->getName(); ?>_add_identidade_numero").val(result.obj.identidade_numero);
                $("#<?php echo $this->getName(); ?>_add_identidade_orgao_expedidor").val(result.obj.identidade_orgao_expedidor);
                $("#<?php echo $this->getName(); ?>_add_identidade_id_uf").val(result.obj.identidade_id_uf);
                $("#<?php echo $this->getName(); ?>_add_telefone_fixo").val(result.obj.telefone_fixo);
                $("#<?php echo $this->getName(); ?>_add_telefone_celular").val(result.obj.telefone_celular);
                $("#<?php echo $this->getName(); ?>_add_logradouro").val(result.obj.endereco.logradouro);
                $("#<?php echo $this->getName(); ?>_add_numero").val(result.obj.endereco.numero);
                $("#<?php echo $this->getName(); ?>_add_complemento").val(result.obj.endereco.complemento);
                $("#<?php echo $this->getName(); ?>_add_cep").val(result.obj.endereco.cep);
                <?php echo $this->getName(); ?>_add_endereco_id_municipio_id_default = result.obj.endereco.id_municipio;
                <?php echo $this->getName(); ?>_add_endereco_id_bairro_id_default = result.obj.endereco.id_bairro;
                $("#<?php echo $this->getName(); ?>_add_endereco_id_uf").val(result.obj.endereco.id_uf).change();
                $("#janela_cpf_<?php echo $this->getName(); ?>").modal("hide");
                $("#janela_add_<?php echo $this->getName(); ?>").modal("show");
            }
        }
    });    
}
<?php } ?>
</script>
<input type="hidden" name="<?php echo $this->getName(); ?>" id="<?php echo $this->getName(); ?>" value="<?php echo $this->getValue(); ?>" />
<input type="hidden" name="<?php echo $this->getName(); ?>_pagina" id="<?php echo $this->getName(); ?>_pagina" />
<div id="janela_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Pessoa Física</h3>
    </div>
    <div class="modal-body">
        <div class="well well-small">
            <fieldset>
                <div class="control-group">
                    <label for="<?php echo $this->getName(); ?>_cpf" class="control-label">C.P.F.:</label>
                    <div class="controls">
                        <input type="text" name="<?php echo $this->getName(); ?>_cpf" id="<?php echo $this->getName(); ?>_cpf" value="" class="filtro cpf span4" />
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
        <table id="<?php echo $this->getName(); ?>_resposta" class="table table-striped table-bordered">
            <thead class="head_destino">
                <tr>
                    <th>ID</th>
                    <th>C.P.F.</th>
                    <th>Nome</th>
                </tr>
            </thead>
            <tbody class="corpo_destino"></tbody>
        </table>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
<?php if ($this->hab_adicionar) { ?>
        <input type="button" value="Adicionar" id="btn_add" class="btn btn-danger" />
<?php } ?>
        <input type="button" value="Limpar Filtro" id="btn_limpar_filtro" class="btn" />
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>
<?php if ($this->hab_adicionar) { ?>
<div id="janela_cpf_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Pessoa Física - Informe o C.P.F.</h3>
    </div>
    <div class="modal-body">
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <span class="mensagem_erro"></span>
        </div>
        <fieldset>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_cpf" class="control-label">C.P.F.:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_cpf" id="<?php echo $this->getName(); ?>_add_cpf" class="field span6 cpf" />
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Procurar" id="btn_procurar" class="btn btn-primary" />
    </div>
</div>

<div id="janela_add_<?php echo $this->getName(); ?>" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="myModalLabel">Pessoa Física - Adicionar</h3>
    </div>
    <div class="modal-body">
        <input type="hidden" name="add_id_pessoa_fisica" id="add_id_pessoa_fisica" />
        <div class="alert">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <span class="mensagem_erro"></span>
        </div>
        <fieldset>
            <dl class="dl-horizontal">
                <dt>C.P.F.:</dt>
                <dd class="<?php echo $this->getName(); ?>_show_cpf"></dd>
            </dl>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_nome" class="control-label">Nome:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_nome" id="<?php echo $this->getName(); ?>_add_nome" class="field span7"  />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_email" class="control-label">E-Mail:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_email" id="<?php echo $this->getName(); ?>_add_email" class="field span7"  />
                </div>
            </div>
<?php
$ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_add_id_estado_civil");
$ctrl->setPkName("id_estado_civil");
$ctrl->setModel("TbEstadoCivil");
$ctrl->setLabel("Estado Civil: ");
echo $ctrl->render($view);
?>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_data_nascimento" class="control-label">Data de Nascimento:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_data_nascimento" id="<?php echo $this->getName(); ?>_add_data_nascimento" class="field span2 data"  />
                </div>
            </div>
            
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_nome_pai" class="control-label">Nome do Pai:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_nome_pai" id="<?php echo $this->getName(); ?>_add_nome_pai" class="field span7"  />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_nome_mae" class="control-label">Nome da Mãe:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_nome_mae" id="<?php echo $this->getName(); ?>_add_nome_mae" class="field span7"  />
                </div>
            </div>
            
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_identidade_numero" class="control-label">RG - Número:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_identidade_numero" id="<?php echo $this->getName(); ?>_add_identidade_numero" size="30" maxlength="30" value="" class="field" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_identidade_orgao_expedidor" class="control-label">RG - Órgão Expedidor:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_identidade_orgao_expedidor" id="<?php echo $this->getName(); ?>_add_identidade_orgao_expedidor" size="20" maxlength="20" value="" class="field" />
                </div>
            </div>
<?php
$ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_add_identidade_id_uf");
$ctrl->setPkName("id_uf");
$ctrl->setModel("TbUf");
$ctrl->setLabel("RG - UF: ");
echo $ctrl->render($view);
?>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_telefone_fixo" class="control-label">Telefone Fixo:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_telefone_fixo" id="<?php echo $this->getName(); ?>_add_telefone_fixo" class="span3 telefone field" value="" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_telefone_celular" class="control-label">Telefone Celular:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_telefone_celular" id="<?php echo $this->getName(); ?>_add_telefone_celular" class="span3 telefone field" value="" />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Endereço</legend>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_logradouro" class="control-label">Logradouro:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_logradouro" id="<?php echo $this->getName(); ?>_add_logradouro" class="span7 field" maxlength="60" value="" />
                </div>
            </div>            
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_numero" class="control-label">Número:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_numero" id="<?php echo $this->getName(); ?>_add_numero" class="span2 field" value="" maxlength="10" />
                </div>
            </div>            
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_complemento" class="control-label">Complemento:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_complemento" id="<?php echo $this->getName(); ?>_add_complemento" class="span7 field" maxlength="40" value="" />
                </div>
            </div>
            <div class="control-group">
                <label for="<?php echo $this->getName(); ?>_add_cep" class="control-label">C.E.P.:</label>
                <div class="controls">
                    <input type="text" name="<?php echo $this->getName(); ?>_add_cep" id="<?php echo $this->getName(); ?>_add_cep" class="span2 cep field" value="" />
                </div>
            </div>
<?php
$ctrl = new Escola_Form_Element_Select_Table($this->getName() . "_add_endereco_id_uf");
$ctrl->setPkName("id_uf");
$ctrl->setModel("TbUf");
$ctrl->setLabel("UF: ");
$ctrl->setAttrib("class", "field");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Municipio($this->getName() . "_add_endereco_id_municipio");
$ctrl->setLabel("Município: ");
$ctrl->set_id_uf($this->getName() . "_add_endereco_id_uf");
$ctrl->setAttrib("class", "field");
echo $ctrl->render($view);

$ctrl = new Escola_Form_Element_Select_Table_Crud_Inline_Bairro($this->getName() . "_add_endereco_id_bairro");
$ctrl->setLabel("Bairro:");
$ctrl->set_id_municipio($this->getName() . "_add_endereco_id_municipio");
$ctrl->setAttrib("class", "field");
echo $ctrl->render($view);
?>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Fechar</button>
        <input type="button" value="Salvar" id="btn_salvar" class="btn btn-primary" />
    </div>
</div>
<?php } ?>
<div class="control-group" id="linha_<?php echo $this->getName(); ?>">
    <label for="<?php echo $this->getName(); ?>" class="control-label"><?php echo $this->getLabel(); ?></label>
    <div class="controls">
        <div class="input-append">
            <input type="text" name="show_<?php echo $this->getName(); ?>" id="show_<?php echo $this->getName(); ?>" disabled class="input-xxlarge" value="<?php echo $txt_pj; ?>" />
            <div class="add-on">
                <a href="#" class="link_<?php echo $this->getName(); ?>">
                    <i class="icon-search"></i>
                </a>
            </div>
<?php 
$style = 'style="display:none;"';
if ($this->getValue()) {
    $style = "";
}
if ($this->hab_adicionar) {
?>
            <div class="add-on <?php echo $this->getName(); ?>_bt_alterar" <?php echo $style; ?>>
                <a href="#" class="link_alterar_<?php echo $this->getName(); ?>">
                    <i class="icon-cog"></i>
                </a>
            </div>
<?php } ?>
            <div class="add-on <?php echo $this->getName(); ?>_bt_cancelar" <?php echo $style; ?>>
                <a href="#" class="link_cancelar_<?php echo $this->getName(); ?>">
                    <i class="icon-remove-circle"></i>
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
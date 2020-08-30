var info_bancaria = [];
$(document).ready(
    function() {
        $("#ib_btn_cancelar").click(function() {
            limpa_botao();
        });
        $("#ib_btn_salvar").click(function() {
            var flag = true;
            $(".ib_alerta").fadeOut().remove();
            if (!$("#id_info_bancaria_tipo").val().length) {
                ib_alerta("CAMPO TIPO OBRIGATÓRIO!");
                flag = false;
            }
            if (!$("#id_banco").val().length) {
                ib_alerta("CAMPO BANCO OBRIGATÓRIO!");
                flag = false;
            }
            if (!$("#agencia").val().length) {
                ib_alerta("CAMPO AGÊNCIA OBRIGATÓRIO!");
                flag = false;
            }
            if (!$("#conta").val().length) {
                ib_alerta("CAMPO CONTA OBRIGATÓRIO!");
                flag = false;
            }
            if (!$("#conta_dv").val().length) {
                ib_alerta("CAMPO DÍGITO VERIFICADOR DA CONTA OBRIGATÓRIO!");
                flag = false;
            }
            if (info_bancaria.length) {
                for (i = 0; i < info_bancaria.length; i++) {
                    ib_item = info_bancaria[i];
                    if ((ib_item.id_info_bancaria_tipo == $("#id_info_bancaria_tipo").val()) && 
                        (ib_item.id_banco == $("#id_banco").val()) && 
                        (ib_item.agencia == $("#agencia").val()) && 
                        (ib_item.agencia_dv == $("#agencia_dv").val()) && 
                        (ib_item.agencia == $("#conta").val()) && 
                        (ib_item.conta_dv == $("#conta_dv").val())) {
                        ib_alerta("INFORMAÇÃO BANCÁRIA JÁ CADASTRADA!");
                        flag = false;
                    }
                }
            }
            if (flag) {
                var ib_idc = $("#info_bancaria_indice").val();
                if (ib_idc) {
                    obj_ib = info_bancaria[ib_idc];
                } else {
                    obj_ib = {};
                    obj_ib.id = "";
                }
                obj_ib.id_info_bancaria_tipo = $("#id_info_bancaria_tipo").val();
                obj_ib.info_bancaria_tipo = $("#id_info_bancaria_tipo option:selected").text();
                obj_ib.id_banco = $("#id_banco").val();
                obj_ib.banco = $("#id_banco option:selected").text();;
                obj_ib.agencia = $("#agencia").val();;
                obj_ib.agencia_dv = $("#agencia_dv").val();
                obj_ib.agencia_show = obj_ib.agencia;
                if (obj_ib.agencia_dv.length) {
                    obj_ib.agencia_show += "-" + obj_ib.agencia_dv;
                }
                obj_ib.conta = $("#conta").val();
                obj_ib.conta_dv = $("#conta_dv").val();
                obj_ib.conta_show = obj_ib.conta + "-" + obj_ib.conta_dv;
                if (ib_idc) {
                    info_bancaria[ib_idc] = obj_ib;
                } else {
                    info_bancaria.push(obj_ib);
                }
                limpa_botao();
                atualiza_info_bancaria();
            }
        });
        atualiza_info_bancaria();
        $("#id_vinculo_tipo").focus().select();
    }
);

function atualiza_info_bancaria() {
    console.log(info_bancaria);
    $(".linha_info_bancaria").remove();
    if (info_bancaria.length) {
        for (i = 0; i < info_bancaria.length; i++) {
            ib_item = info_bancaria[i];
            var tr = $("<tr>", {"class" : "linha_info_bancaria" });
            $("<td><div class='text-center'><a href='#' class='link_ib' id='" + i + "'>" + (i + 1) + "</a></div></td>").appendTo(tr);
            $("<td><a href='#' class='link_ib' id='" + i + "'>" + ib_item.info_bancaria_tipo + "</a></td>").appendTo(tr);
            $("<td><a href='#' class='link_ib' id='" + i + "'>" + ib_item.banco + "</a></td>").appendTo(tr);
            $("<td><div class='text-center'><a href='#' class='link_ib' id='" + i + "'>" + ib_item.agencia_show + "</a></div></td>").appendTo(tr);
            $("<td><div class='text-center'><a href='#' class='link_ib' id='" + i + "'>" + ib_item.conta_show + "</a></div></td>").appendTo(tr);
            $("<td><div class='text-center'><a href='#' class='btn btn-danger btn_info_bancaria_excluir' id='" + i + "'><i class='icon-trash'></i></a></div></td>").appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][id_info_bancaria]", "value": ib_item.id }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][id_info_bancaria_tipo]", "value": ib_item.id_info_bancaria_tipo }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][info_bancaria_tipo]", "value": ib_item.info_bancaria_tipo }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][id_banco]", "value": ib_item.id_banco }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][banco]", "value": ib_item.banco }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][agencia]", "value": ib_item.agencia }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][agencia_dv]", "value": ib_item.agencia_dv }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][agencia_show]", "value": ib_item.agencia_show }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][conta]", "value": ib_item.conta}).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][conta_dv]", "value": ib_item.conta_dv }).appendTo(tr);
            $("<input>", { "type": "hidden", "name": "info_bancaria[" + i + "][conta_show]", "value": ib_item.conta_show }).appendTo(tr);
            tr.appendTo(".tabela_info_bancaria tbody");
        }
        $(".link_ib").bind("click", function(event) {
            event.preventDefault();
            ib_alterar($(this).attr("id"));
        });
        $(".btn_info_bancaria_excluir").bind("click", function(event) {
            event.preventDefault();
            ib_excluir($(this).attr("id"));
        });
    } else {
        $('<tr class="linha_info_bancaria"><td colspan="6"><div class="text-center">NENHUMA INFORMAÇÃO BANCÁRIA ADICIONADA!</div></td></tr>').appendTo(".tabela_info_bancaria tbody");
    }
}

function limpa_botao() {
    $(".ib_field").val("");
    $("#ib_btn_cancelar").hide();
    $("#ib_btn_salvar").val("Adicionar");
}

function ib_alterar(idc) {
    ib_item = info_bancaria[idc];
    if (ib_item) {
        $("#info_bancaria_indice").val(idc);
        $("#id_info_bancaria").val(ib_item.id_info_bancaria);
        $("#id_info_bancaria_tipo").val(ib_item.id_info_bancaria_tipo);
        $("#id_banco").val(ib_item.id_banco);
        $("#agencia").val(ib_item.agencia);
        $("#agencia_dv").val(ib_item.agencia_dv);
        $("#conta").val(ib_item.conta);
        $("#conta_dv").val(ib_item.agencia_dv);
        $("#ib_btn_cancelar").show();
        $("#ib_btn_salvar").val("Alterar");
    }
}

function ib_excluir(idc) {
    info_bancaria.splice(idc, 1);
    atualiza_info_bancaria();
}

function ib_alerta(mensagem) {
    var ctrl_alert = $("<div>", { "class": "ib_alerta alert alert-danger fade in", "role": "alert" });
    ctrl_alert.html('<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only"></span></button>' + mensagem);
    $(".tabela_info_bancaria").before(ctrl_alert);
}
$(document).ready(function() {
    $(".legenda").first().focus();
});

function addFieldset() {
    var clone = $(".fieldset").first().clone();
    clone.appendTo($("#formulario"));
    var count = $(".fieldset").length;
    $("#file_count").val(count);
    clone.find(".id_fieldset").text(count);
    clone.find("input").val("");
    clone.find(".legenda").last().attr("name", "legenda" + count);
    clone.find(".arquivo").last().attr("name", "arquivo" + count);
    
    $(".legenda").first().focus();
}
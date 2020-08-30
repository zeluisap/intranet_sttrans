<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com     |
// | esse pacote; se n�o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa				        |
// | 														                                   			  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +--------------------------------------------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>              		             				|
// | Desenvolvimento Boleto Banco do Brasil: Daniel William Schultz / Leandro Maniezo / Rog�rio Dias Pereira|
// +--------------------------------------------------------------------------------------------------------+


// ------------------------- DADOS DIN�MICOS DO SEU CLIENTE PARA A GERA��O DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formul�rio c/ POST, GET ou de BD (MySql,Postgre,etc)	//

// DADOS DO BOLETO PARA O SEU CLIENTE
$dias_de_prazo_para_pagamento = 5;
$taxa_boleto = 0;
//$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
$venc = new Zend_Date($boleto->data_vencimento);
$data_venc = $venc->toString("dd/MM/YYYY"); 
$valor_cobrado = Escola_Util::number_format($boleto->pegaValor()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(".", "",$valor_cobrado);
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $boleto->getId();
$dadosboleto["numero_documento"] = $boleto->getId();	// Num do pedido ou do documento
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emiss�o do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com v�rgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $pessoa->toString();
$end = $pessoa->getEndereco();
$txts = $txts1 = array();
if (trim($end->logradouro)) {
    $txts[] = $end->logradouro;
}
if (trim($end->numero)) {
    $txts[] = "N.: {$end->numero}";
}
if (trim($end->complemento)) {
    $txts[] = $end->complemento;
}
$bairro = $end->findParentRow("TbBairro");
if ($bairro) {
    $txts[] = "Bairro: {$bairro->descricao}.";
    $municipio = $bairro->findParentRow("TbMunicipio");
    if ($municipio) {
        $txts1[] = $municipio->descricao;
        $uf = $municipio->findParentRow("TbUf");
        if ($uf) {
            $txts1[] = $uf->descricao;
        }
    }
}
if (Escola_Util::limpaNumero($end->cep)) {
    $txts1[] = Escola_Util::formatCep($end->cep);
}
$dadosboleto["endereco1"] = implode(" - ", $txts);
$dadosboleto["endereco2"] = implode(" - ", $txts1);;

$dadosboleto["demonstrativo1"] = "";
$dadosboleto["demonstrativo2"] = "";
$dadosboleto["demonstrativo3"] = "";
// INFORMACOES PARA O CLIENTE
$items = $boleto->pegaItems();
if ($items) {
    if (count($items) <= 3) {
        $contador = count($items);
    } else {
        $contador = 2;
    }
    for ($i = 1; $i <= $contador; $i++) {
        $item = $items[$i - 1];
        $ref = $item->pegaReferencia();
        if ($ref) {
            $dadosboleto["demonstrativo{$i}"] = $ref->toString();
        } else {
            $dadosboleto["demonstrativo{$i}"] = "";
        }
    }
    if ($contador < 3) {
        $dadosboleto["demonstrativo3"] = "etc.";
    }
}
// INSTRU��ES PARA O CAIXA
$dadosboleto["instrucoes1"] = "- Sr. Caixa, N�o Receber Ap�s o Vencimento";
$tb = new TbSistema();
$cliente = $tb->pegaSistema();
$email = "";
if ($cliente) {
    $pj = $cliente->findParentRow("TbPessoaJuridica");
    if ($pj) {
        $pessoa = $pj->findParentRow("TbPessoa");
        if ($pessoa) {
            $email = $pessoa->email;
        }
    }
}
if ($email) {
    $dadosboleto["instrucoes2"] = "- Em caso de d�vidas entre em contato conosco: {$email}";
} else {
    $dadosboleto["instrucoes2"] = "";
}
$dadosboleto["instrucoes3"] = "";
$dadosboleto["instrucoes4"] = "";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "10";
$dadosboleto["valor_unitario"] = "10";
$dadosboleto["aceite"] = "N";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DM";


// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - BANCO DO BRASIL
$dadosboleto["agencia"] = $ib->agencia; // Num da agencia, sem digito
$dadosboleto["conta"] = $ib->conta; 	// Num da conta, sem digito

// DADOS PERSONALIZADOS - BANCO DO BRASIL
$dadosboleto["convenio"] = $bc->convenio;  // Num do conv�nio - REGRA: 6 ou 7 ou 8 d�gitos
$dadosboleto["contrato"] = $bc->contrato; // Num do seu contrato
$dadosboleto["carteira"] = "17";
$dadosboleto["variacao_carteira"] = "-019";  // Varia��o da Carteira, com tra�o (opcional)

// TIPO DO BOLETO
$dadosboleto["formatacao_convenio"] = "7"; // REGRA: 8 p/ Conv�nio c/ 8 d�gitos, 7 p/ Conv�nio c/ 7 d�gitos, ou 6 se Conv�nio c/ 6 d�gitos
$dadosboleto["formatacao_nosso_numero"] = "2"; // REGRA: Usado apenas p/ Conv�nio c/ 6 d�gitos: informe 1 se for NossoN�mero de at� 5 d�gitos ou 2 para op��o de at� 17 d�gitos

/*
#################################################
DESENVOLVIDO PARA CARTEIRA 18

- Carteira 18 com Convenio de 8 digitos
  Nosso n�mero: pode ser at� 9 d�gitos

- Carteira 18 com Convenio de 7 digitos
  Nosso n�mero: pode ser at� 10 d�gitos

- Carteira 18 com Convenio de 6 digitos
  Nosso n�mero:
  de 1 a 99999 para op��o de at� 5 d�gitos
  de 1 a 99999999999999999 para op��o de at� 17 d�gitos

#################################################
*/


// SEUS DADOS
$dadosboleto["identificacao"] = "BoletoPhp - C�digo Aberto de Sistema de Boletos";
$dadosboleto["cpf_cnpj"] = Escola_Util::formatCnpj($pj->cnpj);
$dadosboleto["endereco"] = "Coloque o endere�o da sua empresa aqui";
$dadosboleto["cidade_uf"] = "Cidade / Estado";
$dadosboleto["cedente"] = $pj->toString();

// N�O ALTERAR!
include("include/funcoes_bb.php"); 
include("include/layout_bb.php");

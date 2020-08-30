<?php

class Escola_Retorno_CNAB240 extends Escola_Retorno {

    public function processar_arquivo($arquivo) {
        $items = array();
        parent::processar_arquivo($arquivo);
        $linhas = $this->get_linhas();
        if (!($linhas && is_array($linhas) && count($linhas))) {
            return;
        }

        foreach ($linhas as $linha) {
            $tipo_registro = substr($linha, 7, 1);
            if (!($tipo_registro && ($tipo_registro == "3"))) {
                continue;
            }
            $codigo_segmento = substr($linha, 13, 1);
            if (!$codigo_segmento) {
                continue;
            }
            switch ($codigo_segmento) {
                case "T":
                    $obj = new stdClass();
                    $obj->nosso_numero = trim(substr($linha, 37, 20));
                    $obj->convenio = trim(substr($obj->nosso_numero, 0, 7));
                    $obj->id_boleto = trim(substr($obj->nosso_numero, 7));
                    if (is_numeric($obj->id_boleto)) {
                        $obj->id_boleto = (int) $obj->id_boleto;
                    }
                    $obj->banco = trim(substr($linha, 0, 3));
                    $obj->agencia = trim(substr($linha, 17, 5));
                    $obj->agencia_dv = trim(substr($linha, 22, 1));
                    $obj->conta = trim(substr($linha, 23, 12));
                    $obj->conta_dv = trim(substr($linha, 35, 1));
                    $obj->valor_titulo = trim(substr($linha, 81, 15));
                    if (strlen($obj->valor_titulo) == 15) {
                        $inteiro = (int) substr($obj->valor_titulo, 0, 13);
                        $decimal = substr($obj->valor_titulo, 13);
                        $obj->valor_titulo = $inteiro . "," . $decimal;
                    }
                    $items[] = $obj;
                    break;
                case "U":
                    if (count($items)) {
                        $obj = $items[count($items) - 1];
                        $obj->valor_pago = trim(substr($linha, 77, 15));
                        if (strlen($obj->valor_pago) == 15) {
                            $inteiro = (int) substr($obj->valor_pago, 0, 13);
                            $decimal = substr($obj->valor_pago, 13);
                            $obj->valor_pago = $inteiro . "," . $decimal;
                        }
                        $obj->data_pagamento = trim(substr($linha, 137, 8));
                        if (strlen($obj->data_pagamento) == 8) {
                            $dia = substr($obj->data_pagamento, 0, 2);
                            $mes = substr($obj->data_pagamento, 2, 2);
                            $ano = substr($obj->data_pagamento, 4);
                            $obj->data_pagamento = $dia . "/" . $mes . "/" . $ano;
                        }
                        $items[count($items) - 1] = $obj;
                    }
                    break;
            }
        }

        if (count($items)) {
            return $items;
        }
        return false;
    }

}
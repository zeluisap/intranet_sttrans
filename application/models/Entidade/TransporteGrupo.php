<?php

class TransporteGrupo extends Escola_Entidade {

    public function init() {
        if (!$this->getId()) {
            $this->possui_concessao = "S";
        }
    }

    public function toString() {
        return $this->descricao;
    }

    public function setFromArray(array $dados) {
        if (isset($dados["chave"])) {
            $dados["chave"] = Escola_Util::maiuscula($dados["chave"]);
        }

        if (isset($dados["veiculo_unico"])) {
            if (strtolower($dados["veiculo_unico"]) == 's') {
                $dados["veiculo_unico"] = true;
            } else {
                $dados["veiculo_unico"] = false;
            }
        }

        if (isset($dados["id_banco_convenio"]) && !$dados["id_banco_convenio"]) {
            $dados["id_banco_convenio"] = null;
        }

        parent::setFromArray($dados);
    }

    public function getErrors() {
        $msgs = array();
        if (!trim($this->chave)) {
            $msgs[] = "CAMPO CHAVE OBRIGATï¿½RIO!";
        }
        if (!trim($this->descricao)) {
            $msgs[] = "CAMPO DESCRIï¿½ï¿½O OBRIGATï¿½RIO!";
        }
        if (!trim($this->possui_concessao)) {
            $msgs[] = "CAMPO POSSUI CONCESSï¿½O OBRIGATï¿½RIO!";
        }
        $rg = $this->getTable()->fetchAll(" chave = '{$this->chave}' and id_transporte_grupo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "GRUPO DE TRANSPORTE Jï¿½ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors() {
        $msgs = array();
        $registros = $this->findDependentRowset("TbTransporte");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        } else {
            $registros = $this->findDependentRowset("TbServicoTransporteGrupo");
            if ($registros && count($registros)) {
                $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
            }
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function taxi() {
        return ($this->chave == "TAXI");
    }

    public function onibus() {
        return ($this->chave == "OB");
    }

    public function transporte_escolhar() {
        return in_array(strtolower($this->chave), [ "escolar", "es" ]);
    }
    
    public function moto_taxi() {
        return in_array($this->chave, array("MT", "TMTX", "LMT"));
    }

    public function pegaServicosObrigatorios() {
        $tb = new TbServicoTransporteGrupo();
        $objs = $tb->listar(array("id_transporte_grupo" => $this->getid(),
            "obrigatorio" => "S"));
        if ($objs && count($objs)) {
            return $objs;
        }
        return false;
    }

    public function possui_concessao() {
        return ($this->possui_concessao == "S");
    }

    public function mostrar_possui_concessao() {
        if ($this->possui_concessao()) {
            return "SIM";
        }
        return "Nï¿½O";
    }

    public function isVeiculoUnico() {
        return $this->veiculo_unico;
        //return ($this->taxi() || $this->moto_taxi() || $this->transporte_escolhar());
    }

}
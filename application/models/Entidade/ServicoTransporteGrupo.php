<?php

class ServicoTransporteGrupo extends Escola_Entidade
{

    protected $_valor = false;

    public function getServico()
    {
        return $this->findParentRow("TbServico");
    }

    public function pega_valor()
    {
        if ($this->_valor) {
            return $this->_valor;
        }
        $valor = $this->findParentRow("TbValor");
        if (!$valor) {
            $tb = new TbValor();
            $valor = $tb->createRow();
            $this->vencimento_dias = 10;
        }
        return $valor;
    }

    public function init()
    {
        parent::init();
        $this->_valor = $this->pega_valor();
        if (!$this->getId()) {
            $this->obrigatorio = "N";
            $this->validade_dias = 0;
            $this->juros_dia = 0;
        }
    }

    public function setFromArray(array $dados)
    {
        if (isset($dados["juros_dia"])) {
            $dados["juros_dia"] = Escola_Util::montaNumero($dados["juros_dia"]);
        }

        if (isset($dados["emite_documento"])) {
            $dados["emite_documento"] = (strtolower($dados["emite_documento"]) == "s") ? 1 : 0;
        }

        if (isset($dados["id_periodicidade"])) {
            if (!$dados["id_periodicidade"]) {
                $dados["id_periodicidade"] = null;
            }
        }

        $this->_valor->setFromArray($dados);
        parent::setFromArray($dados);
    }

    public function save()
    {
        $this->id_valor = $this->_valor->save();
        $periodicidade = $this->findParentRow("TbPeriodicidade");
        if (!$periodicidade || !$periodicidade->anual()) {
            $this->mes_referencia = null;
        }
        return parent::save();
    }

    public function getErrors()
    {
        $msgs = array();
        if (!trim($this->id_servico)) {
            $msgs[] = "CAMPO SERVIÇO OBRIGATÓRIO!";
        }
        $servico = $this->findParentRow("TbServico");
        if ($servico && $servico->transporte() && !trim($this->id_transporte_grupo)) {
            $msgs[] = "CAMPO GRUPO DE TRANSPORTE OBRIGATÓRIO!";
        }
        if (!$this->_valor->valor) {
            $msgs[] = "CAMPO VALOR OBRIGATÓRIO!";
        }
        if (!trim($this->vencimento_dias)) {
            $msgs[] = "CAMPO DIAS PARA O VENCIMENTO OBRIGATÓRIO!";
        }
        if ($this->emite_documento === null) {
            $msgs[] = "CAMPO EMITE DOCUMENTO OBRIGATÓRIO!";
        }

        if (count($msgs)) {
            return $msgs;
        }

        $periodicidade = $this->findParentRow("TbPeriodicidade");
        if ($periodicidade && $periodicidade->anual() && !$this->mes_referencia) {
            $msgs[] = "CAMPO MÊS REFERÊNCIA OBRIGATÓRIO PARA PERIODICIDADE ANUAL!";
        }
        $rg = $this->getTable()->fetchAll(" id_servico = '{$this->id_servico}' and id_transporte_grupo = '{$this->id_transporte_grupo}' and id_servico_transporte_grupo <> '" . $this->getId() . "' ");
        if ($rg && count($rg)) {
            $msgs[] = "VÍNCULO DE SERVIÇO E GRUPOS DE TRANSPORTE JÁ CADASTRADO!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function getDeleteErrors()
    {
        $msgs = array();
        $registros = $this->findDependentRowset("TbServicoSolicitacao");
        if ($registros && count($registros)) {
            $msgs[] = "Existem Registros Vinculados a este! Apague as referencias antes de excluir!";
        }
        if (count($msgs)) {
            return $msgs;
        }
        return false;
    }

    public function obrigatorio()
    {
        return ($this->obrigatorio == "S");
    }

    public function mostrar_obrigatorio()
    {
        return ($this->obrigatorio()) ? "SIM" : "NÃO";
    }

    public function toString()
    {
        $txt = array();
        $servico = $this->findParentRow("TbServico");
        if ($servico) {
            $txt[] = $servico->toString();
        }
        $txt[] = $this->pega_valor()->toString();
        return implode(" - ", $txt);
    }

    public function pegaRelatorioSolicitacao($ss = null)
    {
        $servico = $this->findParentRow("TbServico");
        $tg      = $this->findParentRow("TbTransporteGrupo");

        if (!$servico) {
            return null;
        }

        if (!$tg) {
            return null;
        }

        $partes = [];
        $partes[] = $servico->codigo;
        $partes[] = $tg->chave;

        $prefixo = "Escola_Relatorio_Servico_";

        $relatorios = [];

        for ($i = 0; $i < count($partes); $i++) {
            $array = [];
            foreach ($partes as $idc => $parte) {
                if ($idc > (count($partes) - ($i + 1))) {
                    continue;
                }
                $array[] = $parte;
            }
            $relatorios[] = $prefixo . implode("_", $array);
        }

        $relatorio = null;
        foreach ($relatorios as $relatorio_nome) {
            if (Zend_Loader_Autoloader::autoload($relatorio_nome)) {
                $relatorio = new $relatorio_nome();
                break;
            }
        }

        if (!$relatorio) {
            return null;
        }

        $relatorio->set_registro($ss);

        return $relatorio->getEnabled();
    }

    public function validarEmitir($ss)
    {

        try {
            if (!$this->emite_documento) {
                throw new Escola_Exception("Documento não Configurado para Emissão!");
            }

            $relatorio = $this->pegaRelatorioSolicitacao($ss);
            if (!$relatorio) {
                throw new Escola_Exception("NENHUM RELATÓRIO DISPONÍVEL!");
            }

            return $relatorio->validarEmitir();
        } catch (Escola_Exception $ex) {
            return [$ex->getMessage()];
        } catch (Exception $ex) {
            echo "<pre>";
            print_r($ex);
            die();

            return ["Ocorreu Uma Falha ao Tentar Emitir o Documento!", "Avise o Administrador"];
        }
    }

    public function toPDF($ss)
    {
        if (!$ss) {
            throw new Exception("Falha ao gerar relatório, Nenhum Serviço Vinculado!");
        }

        $relatorio = $this->pegaRelatorioSolicitacao($ss);
        if (!$relatorio) {
            return false;
        }

        return $relatorio->toPDF();
    }

    public function pegaPeriodicidade()
    {
        $obj = $this->findParentRow("TbPeriodicidade");
        if ($obj && $obj->getId()) {
            return $obj;
        }
        return false;
    }
}

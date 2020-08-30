<?php

namespace Tucujuris\Servico\Ws\Nugep;

use Exception;
use Tucujuris\Excecao;
use Tucujuris\Util;

class EnviarProcessosSobrestados extends EnviarProcessosParadigmas
{

    private static $QUANTIDADE_PROCESSOS_POR_TEMA = 1400; //envia no máximo 100 processos por tema de cada x.

    public function enviar()
    {

        $processos = $this->getProcessosEnvio(self::$QUANTIDADE_PROCESSOS_POR_TEMA);
        if (!($processos && is_array($processos) && count($processos))) {
            throw new Excecao("Nenhum processo vinculado ao tema.");
        }

        $contador = $enviados = $erros = 0;
        $total = count($processos);
        foreach ($processos as $p) {

            $numero_tema = Util::valorOuNulo($p, "numero_tema");
            $tipo = Util::valorOuNulo($p, "tipo");
            $siglaOrgao = Util::valorOuNulo($p, "siglaorgao");

            $array = [
                "numero" => $numero_tema,
                "tipo" => $tipo,
                "siglaOrgao" => $siglaOrgao,
            ];

            $contador++;
            $percentual = 100 * $contador / $total;
            $row = [
                "      ",
                $contador,
                Util::progresso($percentual),
                "ID: " . Util::valorOuNulo($p, "id"),
                "numero_processo: " . Util::valorOuNulo($p, "numero_processo"),
            ];

            Util::log($row, false);

            try {

                $sobrestado = [
                    "numero" => Util::valorOuNulo($p, "numero_processo"),
                    // "dataTransitoJulgado" => "",
                    // "dataJulgamento" => "",
                    "dataDistribuicao" => Util::valorOuNulo($p, "datadistribuicao"),
                    "dataSobrestamento" => Util::valorOuNulo($p, "datasobrestamento"),
                    // "situacaoSobrestamento" => "",
                    // "decisaoMerito" => "",
                    // "aplicacaoPrecedenteObrigatorio" => "",
                    // "tipoDecisaoMerito" => "RECONHECIMENTO_PROCEDENCIA_PEDIDO", // padrão para testar o ambiente
                    // "temaAplicado" => "",
                    // "dataBaixa" => "",
                    // "orgaoJulgador" => "",
                    // "julgadoMerito" => "",
                    // "julgadoAplicadaTese" => "",
                ];

                if ($classe = Util::valorOuNulo($p, "classe")) {
                    $sobrestado["classe"] = $classe;
                }

                $resposta = $this->enviarRecurso(array_merge($array, [
                    "processosSobrestados" => [$sobrestado]
                ]));

                $this->sucessoEnvio($p);
                $enviados++;
                Util::log(" --- ok! ");
            } catch (Exception $ex) {
                $this->erroEnvio($p, $ex);
                $erros++;

                Util::log(" **** ERRO: " . $ex->getMessage());
            }
        }

        return [
            "enviados" => $enviados,
            "erros" => $erros
        ];
    }

    public function getProcessosEnvio($limit = 0)
    {

        $sql = "
            select 
                tp.id, 
                t.numero as numero_tema,
                ti.descricao as tipo,
                o.sigla as siglaOrgao,
                tp.classe,
                tp.numero as numero_processo, 

                date(tp.datadistribuicao) || 'T' || tp.datadistribuicao::time as datadistribuicao,
                date(tp.datasobrestamento) || 'T' || tp.datasobrestamento::time as datasobrestamento

            from cnj.bnpr_tema_processos  tp
                join cnj.bnpr_tema t on tp.fk_tema = t.id
                join cnj.bnpr_orgao o on t.fk_orgao = o.id
                join cnj.bnpr_tipo_incidente ti on t.fk_tipo_incidente = ti.id
                
            where not tp.fk_autos is null 
                and tipo = :tipo
                and tp.status_cnj in (:status_cnj_pendente, :status_cnj_erro)
                and tp.datasobrestamento is not null
                and tp.numero ilike '%.%' ";

        if ($limit) {
            $sql .= " limit {$limit} ";
        }

        $params = [
            "tipo" => 2,
            "status_cnj_pendente" => 1,
            "status_cnj_erro" => 5
        ];

        $objs = self::buscarLista($sql, $params);

        if (!($objs && is_array($objs) && count($objs))) {
            return null;
        }

        return $objs;
    }
}

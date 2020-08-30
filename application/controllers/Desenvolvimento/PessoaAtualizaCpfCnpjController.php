<?php

class Desenvolvimento_PessoaAtualizaCpfCnpjController extends Escola_Controller_Logado
{
    public function pessoa_atualiza_cpf_cnpj()
    {
        $db = Zend_Registry::get("db");
        try {

            $sql = "
                select * 
                from pessoa
                where cpf_cnpj is null
            ";

            $objs = Escola_DbUtil::listar($sql);
            if (!Escola_Util::isResultado($objs)) {
                throw new Exception("Falha! Nenhum Registro com Problemas!");
            }

            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $row = [
                    [$qtd, 4],
                    Escola_Util::progresso($percentual, 15),
                    [$obj->id_pessoa, 7],
                    [$obj->email, 40]
                ];

                echo PHP_EOL;
                Escola_Util::log($row, false);

                $pessoa = TbPessoa::pegaPorId($obj->id_pessoa);

                $filho = $pessoa->pegaPessoaFilho();
                if (!$filho) {
                    Escola_DbUtil::query("delete from boleto_item where id_boleto in (
                        select id_boleto from boleto where id_pessoa = :id_pessoa
                    )", [
                        "id_pessoa" => $pessoa->id_pessoa
                    ]);

                    Escola_DbUtil::query("delete from boleto where id_pessoa = :id_pessoa", [
                        "id_pessoa" => $pessoa->id_pessoa
                    ]);

                    Escola_DbUtil::query("delete from pessoa_ref where id_pessoa = :id_pessoa", [
                        "id_pessoa" => $pessoa->id_pessoa
                    ]);

                    Escola_DbUtil::query("delete from pessoa where id_pessoa = :id_pessoa", [
                        "id_pessoa" => $pessoa->id_pessoa
                    ]);
                    Escola_Util::log(" - excluído.", false);
                    continue;
                }

                $doc = Escola_Util::limpaNumero($filho->mostrar_documento());
                if (!$doc) {
                    throw new Exception("Documento não informado.");
                }

                Escola_DbUtil::query("update pessoa set cpf_cnpj = :cpf_cnpj where id_pessoa = :id_pessoa", [
                    "cpf_cnpj" => $doc,
                    "id_pessoa" => $pessoa->id_pessoa
                ]);


                Escola_Util::log();
            }

        } catch (Exception $ex) {
            Escola_Util::trataErro($ex, false);
        }
    }
}

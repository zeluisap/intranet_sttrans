<?php

class Desenvolvimento_ResolvePessoaFisicaDuplicadoController extends Escola_Controller_Logado
{
    public function resolve_pessoa_fisica_duplicado()
    {
        $db = Zend_Registry::get("db");
        try {

            $sql = "
                select pf.cpf, pf.nome, count(pf.id_pessoa_fisica) as quant
                from pessoa_fisica pf
                group by pf.cpf, pf.nome
                having count(pf.id_pessoa_fisica) > 1;
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
                    [$obj->id_pessoa_fisica, 7],
                    $obj->cpf,
                    [$obj->nome, 40]
                ];

                echo PHP_EOL;
                Escola_Util::log($row, false);

                $sql = "
                    select * from pessoa_fisica where cpf = :cpf order by id_pessoa_fisica;
                ";
                $pfs = Escola_DbUtil::listar($sql, [
                    "cpf" => $obj->cpf
                ]);
                if (!Escola_Util::isResultado($pfs)) {
                    print_r($obj);
                    throw new Exception("Falha ao carregar pessoas físicas!");
                }

                if (!count($pfs) > 1) {
                    print_r([
                        "obj" => $obj,
                        "pfs" => $pfs
                    ]);
                    throw new Exception("Falha, pf possui apenas um registro!");
                }

                $pf_permanece = null;
                $pfs_excluir = [];
                $flag = false;
                foreach ($pfs as $pf) {
                    if (!$flag) {
                        $pf_permanece = $pf;
                        $flag = true;
                        continue;
                    }

                    $pfs_excluir[] = $pf;
                }

                foreach ($pfs_excluir as $pf_excluir) {

                    //atualizando pessoa física
                    Escola_DbUtil::query("update auto_infracao_notificacao set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update bolsista set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update credencial set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update credencial set id_pessoa_fisica_responsavel = :id_pf_permanece where id_pessoa_fisica_responsavel = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    // Escola_DbUtil::query("update diaria set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                    //     "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                    //     "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    // ]);

                    Escola_DbUtil::query("update funcionario set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update pessoa_motorista set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update usuario set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    Escola_DbUtil::query("update vinculo_pessoa set id_pessoa_fisica = :id_pf_permanece where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_permanece" => $pf_permanece->id_pessoa_fisica,
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    //atualizando pessoa
                    Escola_DbUtil::query("update boleto set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    Escola_DbUtil::query("update interdicao set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    Escola_DbUtil::query("update pessoa_ref set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    Escola_DbUtil::query("update requerimento set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    // Escola_DbUtil::query("update servico_terceiro set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                    //     "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                    //     "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    // ]);

                    Escola_DbUtil::query("update transporte_pessoa set id_pessoa = :id_pessoa_permanece where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    Escola_DbUtil::query("update veiculo set proprietario_id_pessoa = :id_pessoa_permanece where proprietario_id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_permanece" => $pf_permanece->id_pessoa,
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    // excluindo pessoa física
                    Escola_DbUtil::query("delete from pessoa_fisica where id_pessoa_fisica = :id_pf_antigo", [
                        "id_pf_antigo" => $pf_excluir->id_pessoa_fisica
                    ]);

                    // excluindo pessoa
                    Escola_DbUtil::query("delete from pessoa where id_pessoa = :id_pessoa_antigo", [
                        "id_pessoa_antigo" => $pf_excluir->id_pessoa
                    ]);

                    Escola_Util::log(" - PRONTO!");
                }

                Escola_Util::log();
            }
        } catch (Exception $ex) {
            Escola_Util::trataErro($ex, false);
        }
    }
}

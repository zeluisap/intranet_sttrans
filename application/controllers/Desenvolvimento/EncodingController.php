<?php

class Desenvolvimento_EncodingController extends Escola_Controller_Logado
{
    public function encoding()
    {
        $db = Zend_Registry::get("db");
        $db->beginTransaction();
        try {

            $has_tabela = Escola_Util::hasParametro("-t");
            $has_file   = Escola_Util::hasParametro("-f");

            if (!$has_tabela && !$has_file) {
                throw new Exception("Nada a Processar, utilize a opção -t (tabelas) ou -f (arquivos).");
            }

            if ($has_tabela) {
                $this->encodingTabelas();
            }
            if ($has_file) {
                $this->encodingFiles();
            }

            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();

            Escola_Util::trataErro($ex, false);
        }
    }

    private function encodingTabelas()
    {
        Escola_Util::log("Carregando tabelas ...");
        $tables = Escola_DbUtil::listArray("show tables");

        if (!Escola_Util::isResultado($tables)) {
            throw new Exception("Falha! Banco nÃ£o possui nenhuma tabela!");
        }

        $qtd_geral = 0;
        $total_geral = count($tables);
        foreach ($tables as $table) {
            $qtd_geral++;
            $percentual_geral = $qtd_geral * 100 / $total_geral;

            Escola_Util::log(" -> {$table}");

            $campos = Escola_DbUtil::listar("
                describe {$table}");

            if (!Escola_Util::isResultado($campos)) {
                continue;
            }

            $tipos = [
                "varchar", "text"
            ];

            $pk = null;
            $modificar = [];
            foreach ($campos as $campo) {
                //pega chave primÃ¡ria
                if (isset($campo->Key) && (strtolower($campo->Key) == "pri")) {
                    $pk = $campo->Field;
                    continue;
                }

                $existe = false;
                foreach ($tipos as $tipo) {
                    if (!((isset($campo->Type) && Escola_Util::iniciaCom(strtolower($campo->Type), strtolower($tipo))))) {
                        continue;
                    }
                    $existe = true;
                    break;
                }

                if (!$existe) {
                    continue;
                }

                $modificar[] = $campo->Field;
            }

            if (!$pk) {
                continue;
            }

            if (!count($modificar)) {
                continue;
            }

            $sql = "select {$pk}, " . implode(",", $modificar) . " from {$table}";
            $objs = Escola_DbUtil::listar($sql);

            if (!Escola_Util::isResultado($objs)) {
                continue;
            }

            $qtd = 0;
            $total = count($objs);
            foreach ($objs as $obj) {
                //total
                $qtd++;
                $percentual = $qtd * 100 / $total;

                $sets = $params = [];
                foreach ($modificar as $mod) {
                    $sets[] = " {$mod} = :{$mod} ";
                    $params[":{$mod}"] = utf8_encode($obj->$mod);
                }

                Escola_Util::log([
                    "   ",
                    "Perc. Geral: " . number_format($percentual_geral, 2) . " %",
                    $qtd,
                    "Perc. Tabela: " . number_format($percentual, 2) . " %",
                    $table,
                    "ID: " . $obj->$pk,
                    "campos: [" . implode(", ", $modificar) . "]"
                ]);

                $sql = "update {$table} set " . implode(",", $sets) . " where {$pk} = :pk ";
                $params[":pk"] = $obj->$pk;

                Escola_DbUtil::query($sql, $params);
            }
        }
    }

    private $extensoes = ["phtml", "php"];

    private function encodingFiles()
    {

        $caminhos = [
            "/application/controllers",
            "/application/forms",
            "/application/models",
            "/application/views",
            "/lib/Escola"
        ];

        $files = [];
        Escola_Util::log("Preparando ...");
        foreach ($caminhos as $caminho) {
            $files = array_merge($files, $this->getFiles("." . DIRECTORY_SEPARATOR . ".." . $caminho));
        }

        $qtd = 0;
        $total = count($files);
        foreach ($files as $file) {
            $qtd++;
            $percentual = $qtd * 100 / $total;

            Escola_Util::log([
                $qtd,
                number_format($percentual, 2) . " %",
                $file
            ]);

            $content = trim(file_get_contents($file));

            if (!$content) {
                continue;
            }

            file_put_contents($file, utf8_encode($content));
        }
    }

    private function getFiles($caminho)
    {

        if (!is_dir($caminho)) {
            $extensao = pathinfo($caminho, PATHINFO_EXTENSION);

            if (!$extensao) {
                return [];
            }

            if (!in_array(strtolower($extensao), $this->extensoes)) {
                return [];
            }

            return [$caminho];
        }

        $filter = glob($caminho . DIRECTORY_SEPARATOR . "*");

        $files = [];

        foreach ($filter as $f) {
            $files = array_merge($files, $this->getFiles($f));
        }

        return $files;
    }
}

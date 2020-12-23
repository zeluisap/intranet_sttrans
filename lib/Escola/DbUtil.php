<?php
class Escola_DbUtil
{
    public static function getStmt($sql, $params = null, $db = null)
    {
        if (!$db) {
            $db = Zend_Registry::get("db");
        }

        if (!$db) {
            throw new Exception("Falha, Nenhuma ConexÃ£o!");
        }

        $stmt = null;
        if ($params) {
            $stmt = $db->query($sql, $params);
        } else {
            $stmt = $db->query($sql);
        }

        if (!$stmt) {
            return null;
        }

        return $stmt;
    }

    public static function listar($sql, $params = null, $db = null)
    {
        $stmt = self::getStmt($sql, $params, $db);
        if (!$stmt) {
            return null;
        }

        $objs = $stmt->fetchAll(Zend_Db::FETCH_OBJ);

        if (!Escola_Util::isResultado($objs)) {
            return null;
        }

        return $objs;
    }

    public static function first($sql, $params = null, $db = null)
    {
        $stmt = self::getStmt($sql, $params, $db);
        if (!$stmt) {
            return null;
        }

        return $stmt->fetch(Zend_Db::FETCH_OBJ);
    }

    public static function valor($sql, $params = null, $db = null)
    {
        $stmt = self::getStmt($sql, $params, $db);
        if (!$stmt) {
            return null;
        }
        $retorno = $stmt->fetch(Zend_Db::FETCH_NUM);

        if (is_array($retorno)) {
            return $retorno[0];
        }

        return $retorno;
    }

    public static function lastInsertId($db = null)
    {
        return self::valor("select LAST_INSERT_ID()", $db);
    }

    public static function listArray($sql, $params = null, $db = null)
    {
        $stmt = self::getStmt($sql, $params, $db);
        if (!$stmt) {
            return null;
        }
        $objs = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        if (!$objs) {
            return null;
        }

        $retorno = [];

        foreach ($objs as $ret) {
            if (is_array($ret)) {
                $retorno[] = $ret[0];
            } else {
                $retorno[] = $ret;
            }
        }

        return $retorno;
    }

    public static function query($sql, $params = null, $db = null)
    {
        if (!$db) {
            $db = Zend_Registry::get("db");
        }

        if ($params) {
            return $db->query($sql, $params);
        } else {
            return $db->query($sql);
        }
    }

    public static function insert($sql, $params = null, $db = null)
    {
        self::query($sql, $params, $db);

        if (!$db) {
            $db = Zend_Registry::get("db");
        }

        return $db->lastInsertId();
    }

    public static function copiaValor($id_valor_origem, $db = null)
    {
        if (!$id_valor_origem) {
            return 0;
        }

        $valor_origem = Escola_DbUtil::first("
            select * from valor where id_valor = ?
        ", [$id_valor_origem], $db);

        if (!$valor_origem) {
            return 0;
        }

        self::query("
            insert into valor 
            (id_moeda, valor)
            values
            (:id_moeda, :valor)
        ", [
            ":id_moeda" => $valor_origem->id_moeda,
            ":valor" => $valor_origem->valor
        ], $db);

        return Escola_DbUtil::lastInsertId($db);
    }

    public static function restaurarRegistro($tabela, $id, $popular = [])
    {

        if (!($tabela && $id)) {
            throw new Escola_Exception("Falha ao Recuperar Dados, Informe a tabela e o id!");
        }

        $dblegado = Zend_Registry::get("dblegado");
        if (!$dblegado) {
            throw new Escola_Exception("Falha ao Conectar ao Banco Legado!");
        }

        $pk = "id_" . $tabela;

        $obj_legado = self::first("
            select * from {$tabela} where {$pk} = ?
        ", [$id], $dblegado);

        if (!$obj_legado) {
            throw new Escola_Exception("Registro legado não localizado!");
        }

        $wheres = [];
        $params = [];
        $keys = [];

        $fields = get_object_vars($obj_legado);

        foreach ($fields as $field => $valor) {
            if (strtolower($field) == strtolower($pk)) {
                continue;
            }

            $wheres[] = $field . " = :" . $field;
            $params[":" . $field] = $valor;
            $keys[] = $field;
        }

        $obj = self::first("
            select * 
            from {$tabela}
            where ( " . implode(") and (", $wheres) .  " )
        ", $params);

        if ($popular && is_array($popular) && count($popular)) {
            foreach ($popular as $field => $valor) {
                $params[":" . $field] = $valor;
            }
        }

        if ($obj) {
            if ($popular && is_array($popular) && count($popular)) {
                $items = [];
                $params = [
                    ":id" => $obj->$pk
                ];
                foreach ($popular as $field => $valor) {
                    $items[] = $field . " = :" . $field;
                    $params[":" . $field] = $valor;
                }
                Escola_DbUtil::query("
                    update 
                    {$tabela}
                    set " . implode(", ", $items) . "
                    where {$pk} = :id
                ", $params);
            }
            return $obj;
        }

        $sql = "insert into {$tabela} (" . implode(", ", $keys) . ") values (:" . implode(", :", $keys) . ")";

        self::query($sql, $params);

        $novo_id = self::lastInsertId();

        return self::getRegistro($tabela, $novo_id);
    }

    public static function getRegistro($tabela, $id, $db = null)
    {
        $pk = "id_" . $tabela;

        return self::first("
            select * from {$tabela} where {$pk} = ?
        ", [$id], $db);
    }

    public static function inTransaction($func)
    {
        $db = Zend_Registry::get("db");

        $in = $db->getConnection()->inTransaction();

        if (!$in) {
            $db->beginTransaction();
        }

        try {

            $func();

            if (!$in) {
                $db->commit();
            }
        } catch (Exception $ex) {
            if (!$in) {
                $db->rollBack();
            }
            throw $ex;
        }
    }
}

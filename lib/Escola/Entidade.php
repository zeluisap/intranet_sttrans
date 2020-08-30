<?php

class Escola_Entidade extends Zend_Db_Table_Row_Abstract
{

    public function _delete()
    {
        parent::_delete();
        $tb = new TbLog();
        $tb->registraDelete($this);
    }

    public function getId()
    {
        $id = "";
        $key = $this->_getPrimaryKey();
        if ($key && is_array($key) && count($key)) {
            if (count($key) == 1) {
                $id = trim(implode("", $key));
            } else {
                $id = $key;
            }
        }
        if (!trim($id)) {
            $id = 0;
        }
        return $id;
    }

    public function getPkName()
    {
        $key = $this->_getPrimaryKey();
        if ($key && is_array($key) && count($key)) {
            if (count($key) == 1) {
                return trim(implode("", array_keys($key)));
            } else {
                return $key;
            }
        }
        return null;
    }

    public function getCleanData()
    {
        return $this->_cleanData;
    }

    public function getModifiedFields()
    {
        $padraos = $this->_cleanData;
        $dados = $this->toArray();
        if (count($dados)) {
            $fields = array();
            foreach ($dados as $k => $v) {
                if (isset($padraos[$k])) {
                    if ($v != $padraos[$k]) {
                        $fields[$k] = array("valor_anterior" => $padraos[$k], "valor_depois" => $v);
                    }
                } else {
                    $fields[$k] = array("valor_anterior" => "", "valor_depois" => $v);
                }
            }
            return $fields;
        }
        return false;
    }

    public function save($flag = false)
    {
        $antes = clone ($this);
        $id = parent::save();
        $class = get_called_class();
        if (($class != "Log") && ($class != "LogCampo")) {
            $tb = new TbLog();
            if ($antes->getId()) {
                $tb->registraUpdate($this, $antes);
            } else {
                $tb->registraInsert($this, $antes);
            }
        }
        return $id;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function getErrors()
    {
        return false;
    }

    public function getDeleteErrors()
    {
        return false;
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr["id"] = $this->getId();
        return $arr;
    }

    public function toObjeto()
    {
        return (object) $this->toArray();
    }
}

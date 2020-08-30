<?php
class Smtp extends Escola_Entidade {
    
    public function init() {
        parent::init();
        if (!$this->getId()) {
            $this->auth = "login";
        }
    }
    
    public function getErrors() {
		$msgs = array();
		if (!trim($this->host)) {
			$msgs[] = "CAMPO HOST OBRIGATÓRIO!";
		}
		if (!trim($this->username)) {
			$msgs[] = "CAMPO USUÁRIO OBRIGATÓRIO!";
		}
		if (!trim($this->password)) {
			$msgs[] = "CAMPO SENHA OBRIGATÓRIO!";
		}
		if (count($msgs)) {
			return $msgs;
		}
		return false;        
    }
    
    public function getTransport() {
        $errors = $this->getErrors();
        if (!$errors) {
            $config = array('auth' => 'login',
                            'username' => $this->username,
                            'password' => $this->password);
            if ($this->port) {
                $config["port"] = $this->port;
            }
            if ($this->security) {
                $config["ssl"] = $this->security;
            }
            return new Zend_Mail_Transport_Smtp($this->host, $config);
        } else {
            return implode("; ", $errors);
        }
    }
    
    public function sendMail($dados) {
        $transport = $this->getTransport();
        if (!$transport) {
            return "SMTP Não Configurado para o Sistema!";
        }
        if (!isset($dados["remetente"])) {
            return "Nenhum Remetente Disponível!";
        }
        if (!isset($dados["destinatario"])) {
            return "Nenhum Destinatário Disponível!";
        }
        if (!isset($dados["titulo"])) {
            return "Nenhum Título Disponível!";
        }
        if (!isset($dados["conteudo"])) {
            return "Nenhum Conteúdo Disponível!";
        }
        $mail = new Zend_Mail();
        $tb = new TbSistema();
        $sistema = $tb->pegaSistema();
        if ($sistema) {
            $pf = $sistema->findParentRow("TbPessoaJuridica");
            if ($pf) {
                $pessoa = $pf->findParentRow("TbPessoa");
                if ($pessoa) {
                    $mail->setFrom($pessoa->email, $pf->razao_social);
                }
            }
        }
        $mail->addTo($dados["destinatario"]["email"], $dados["destinatario"]["nome"]);
        if (isset($dados["titulo"]) && $dados["titulo"]) {
            $mail->setSubject($dados["titulo"]);
        }
        $mail->setBodyHtml($dados["conteudo"]["html"]);
        if (isset($dados["conteudo"]["text"])) {
            $mail->setBodyText($dados["conteudo"]["text"]);
        }
        try {
            $mail->send($transport);
            return false;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
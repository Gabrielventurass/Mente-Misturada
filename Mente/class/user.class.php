<?php
require_once "../config/config.inc.php";

class usuario {
    private $nome;
    private $email;
    private $senha;

    public function __construct($nome, $email, $senha) {
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
    }

    public function getNome(): string {
        return $this->nome;
    }
    public function getEmail(): string {
        return $this->email;
    }
    public function getSenha(): string {
        return $this->senha;
    }

    public function setNome($nome) {
        if (empty($nome))
            throw new Exception("Nome não pode ser vazio");
        $this->nome = $nome;
    }

    public function setEmail($email) {
        if (empty($email))
            throw new Exception("Email não pode ser vazio");
        $this->email = $email;
    }

    public function setSenha($senha) {
        if (empty($senha))
            throw new Exception("Senha não pode ser vazia");
        $this->senha = $senha;
    }


    private $erro = '';
    public function getErro(): string {
        return $this->erro;
    }

    public function inserir(): bool {
    try {
        echo "Iniciando inserção...<br>";

        $conexao = new PDO(DSN, USUARIO, SENHA);
        echo "Conectado com sucesso!<br>";

        $verifica = $conexao->prepare("SELECT email FROM usuario WHERE email = :email");
        $verifica->bindValue(':email', $this->getEmail());
        $verifica->execute();

        if ($verifica->rowCount() > 0) {
            echo "E-mail já cadastrado<br>";
            $this->erro = "Este e-mail já está cadastrado!";
            return false;
        }

        $sql = "INSERT INTO usuario (nome, email, senha) VALUES (:nome, :email, :senha)";
        $comando = $conexao->prepare($sql);

        $senhaHash = password_hash($this->senha, PASSWORD_DEFAULT);
        echo "Senha hash gerada.<br>";

        $comando->bindValue(':nome', $this->getNome());
        $comando->bindValue(':email', $this->getEmail());
        $comando->bindValue(':senha', $senhaHash);

        $resultado = $comando->execute();
        echo "Inserção executada.<br>";
        return $resultado;

    } catch (PDOException $e) {
        die("Erro no inserir(): " . $e->getMessage());
    }
}

    public static function buscarPorEmail($email) {
        $conexao = new PDO(DSN, USUARIO, SENHA);
    
        $sql = "SELECT nome, email, senha FROM usuario WHERE email = :email LIMIT 1";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);
        $comando->execute();
    
        $resultado = $comando->fetch(PDO::FETCH_ASSOC);
    
        if ($resultado) {
            $usuario = new usuario(
                $resultado['nome'],
                $resultado['email'],
                $resultado['senha'] 
            );
            return $usuario;
        }
        return null;
    }

    public function atualizarNome(): bool {
        try {
            $conexao = new PDO(DSN, USUARIO, SENHA);
    
            $sql = "UPDATE usuario SET nome = :nome WHERE email = :email";
            $comando = $conexao->prepare($sql);
    
            $comando->bindValue(':nome', $this->getNome());
            $comando->bindValue(':email', $this->getEmail());
    
            return $comando->execute();
        } catch (PDOException $e) {
            $this->erro = "Erro ao atualizar nome: " . $e->getMessage();
            return false;
        }
    }
    


public static function listarTodos() {
    $conexao = new PDO(DSN, USUARIO, SENHA);

    $sql = "SELECT nome, email FROM usuario ORDER BY nome";
    $comando = $conexao->prepare($sql);
    $comando->execute();

    return $comando->fetchAll(PDO::FETCH_ASSOC);
}

public static function atualizarNomePorEmail($email, $novoNome) {
    $conexao = new PDO(DSN, USUARIO, SENHA);

    $sql = "UPDATE usuario SET nome = :nome WHERE email = :email";
    $comando = $conexao->prepare($sql);
    $comando->bindValue(':nome', $novoNome);
    $comando->bindValue(':email', $email);

    return $comando->execute();
}

public static function excluirPorEmail($email) {
    try {
        $conexao = new PDO(DSN, USUARIO, SENHA);

        $sqlRespostas = "DELETE FROM resposta_usuario WHERE usuario_email = :email";
        $comandoRespostas = $conexao->prepare($sqlRespostas);
        $comandoRespostas->bindValue(':email', $email);
        $comandoRespostas->execute();

        $sql = "DELETE FROM usuario WHERE email = :email";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);

        return $comando->execute();
    } catch (PDOException $e) {
        return "Erro ao excluir usuário: " . $e->getMessage();
    }
}

}
?>
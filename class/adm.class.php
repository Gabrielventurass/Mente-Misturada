<?php
require_once "../config/config.inc.php";

class admin {
    private $codigo;
    private $nome;
    private $email;
    private $senha;

    public function __construct($codigo,$nome, $email, $senha) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
    }

    public static function adminPadrao() {
        return new Admin("admin@mente.com", "Administrador Principal", "senhaSegura123");
    }

    // Getters
    public function getCodigo(): string {
        return $this->codigo;
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

    // Setters
    public function setCodigo($codigo) {
        if (empty($codigo))
            throw new Exception("codigo não pode ser vazio");
        $this->codigo = $codigo;
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

    //
    private $erro = '';
    public function getErro(): string {
        return $this->erro;
    }

    // Método para inserir usuário
    public function inserir(): bool {
        try {
            $conexao = new PDO(DSN, ADMIN, SENHA);
    
            // Verificar se o e-mail já existe na tabela admin
            $verificaAdmin = $conexao->prepare("SELECT email FROM admin WHERE email = :email");
            $verificaAdmin->bindValue(':email', $this->getEmail());
            $verificaAdmin->execute();
    
            if ($verificaAdmin->rowCount() > 0) {
                $this->erro = "Este e-mail já está cadastrado como administrador!";
                return false;
            }
    
            // Verificar se já está pendente
            $verificaPendente = $conexao->prepare("SELECT email FROM admins_pendentes WHERE email = :email");
            $verificaPendente->bindValue(':email', $this->getEmail());
            $verificaPendente->execute();
    
            if ($verificaPendente->rowCount() > 0) {
                $this->erro = "Este e-mail já está aguardando aprovação!";
                return false;
            }
    
            // Inserir na tabela de pendentes
            $sql = "INSERT INTO admins_pendentes (nome, email, senha) VALUES (:nome, :email, :senha)";
            $comando = $conexao->prepare($sql);
    
            // Aqui você adiciona o hash da senha:
            $senhaHash = password_hash($this->senha, PASSWORD_DEFAULT);
            $comando->bindValue(':nome', $this->getNome());
            $comando->bindValue(':email', $this->getEmail());
            $comando->bindValue(':senha', $senhaHash);
    
            return $comando->execute();
    
        } catch (PDOException $e) {
            $this->erro = "Erro de banco de dados: " . $e->getMessage();
            return false;
        }
    }
    
    
    
    // Buscar usuário pelo e-mail
    public static function buscarPorEmail($email) {
        $conexao = new PDO(DSN, ADMIN, SENHA);
    
        $sql = "SELECT codigo, nome, email, senha FROM admin WHERE email = :email LIMIT 1";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);
        $comando->execute();
    
        $resultado = $comando->fetch(PDO::FETCH_ASSOC);
    
        if ($resultado) {
            $admin = new admin(
                $resultado['codigo'],
                $resultado['nome'],
                $resultado['email'],
                $resultado['senha']
            );
            return $admin;
        }
        return null;
    }

    public function atualizarNome(): bool {
        try {
            $conexao = new PDO(DSN, ADMIN, SENHA);
    
            $sql = "UPDATE admin SET nome = :nome WHERE email = :email";
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
    $conexao = new PDO(DSN, ADMIN, SENHA);

    $sql = "SELECT codigo, nome, email FROM admin ORDER BY nome";
    $comando = $conexao->prepare($sql);
    $comando->execute();

    return $comando->fetchAll(PDO::FETCH_ASSOC);
}

// Atualizar o nome de um usuário
public static function atualizarNomePorEmail($email, $novoNome) {
    $conexao = new PDO(DSN, ADMIN, SENHA);

    $sql = "UPDATE admin SET nome = :nome WHERE email = :email";
    $comando = $conexao->prepare($sql);
    $comando->bindValue(':nome', $novoNome);
    $comando->bindValue(':email', $email);

    return $comando->execute();
}

// Excluir um usuário
public static function excluirPorEmail($email) {
    $conexao = new PDO(DSN, ADMIN, SENHA);

    $sql = "DELETE FROM admin WHERE email = :email";
    $comando = $conexao->prepare($sql);
    $comando->bindValue(':email', $email);

    return $comando->execute();
}
}
?>
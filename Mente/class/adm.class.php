<?php
require_once "../config/config.inc.php";

class admin {
    private $codigo;
    private $nome;
    private $email;
    private $senha;
    private $erro = '';

    public function __construct($codigo, $nome, $email, $senha) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        admin::garantirAdminPadraoNoBanco();
    }

    // Admin padrão fixo (senha em texto puro)
    public static function adminPadrao() {
        return new admin(
            "0",
            "Administrador Principal",
            "admin1@gmail.com",
            "d3lta034"
        );
    }

    // Getters
    public function getCodigo(): string { return $this->codigo; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenha(): string { return $this->senha; }
    public function getErro(): string { return $this->erro; }

    // Setters com validação
    public function setCodigo($codigo) {
        if (empty($codigo)) throw new Exception("codigo não pode ser vazio");
        $this->codigo = $codigo;
    }
    public function setNome($nome) {
        if (empty($nome)) throw new Exception("Nome não pode ser vazio");
        $this->nome = $nome;
    }
    public function setEmail($email) {
        if (empty($email)) throw new Exception("Email não pode ser vazio");
        $this->email = $email;
    }
    public function setSenha($senha) {
        if (empty($senha)) throw new Exception("Senha não pode ser vazia");
        $this->senha = $senha;
    }

    // Método para verificar a senha na hora do login
    public function verificarSenha(string $senhaDigitada): bool {
        if ($this->codigo === "0") {
            // Admin padrão: compara texto puro
            return $senhaDigitada === $this->senha;
        }
        // Demais admins: compara senha digitada com hash no banco
        return password_verify($senhaDigitada, $this->senha);
    }

    // Inserir admin (pendente aprovação)
    public function inserir(): bool {
        try {
            $conexao = new PDO(DSN, ADMIN, SENHA);

            $verificaAdmin = $conexao->prepare("SELECT email FROM admin WHERE email = :email");
            $verificaAdmin->bindValue(':email', $this->getEmail());
            $verificaAdmin->execute();
            if ($verificaAdmin->rowCount() > 0) {
                $this->erro = "Este e-mail já está cadastrado como administrador!";
                return false;
            }

            $verificaPendente = $conexao->prepare("SELECT email FROM admins_pendentes WHERE email = :email");
            $verificaPendente->bindValue(':email', $this->getEmail());
            $verificaPendente->execute();
            if ($verificaPendente->rowCount() > 0) {
                $this->erro = "Este e-mail já está aguardando aprovação!";
                return false;
            }

            $sql = "INSERT INTO admins_pendentes (nome, email, senha) VALUES (:nome, :email, :senha)";
            $comando = $conexao->prepare($sql);

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

    // Buscar admin pelo email (retorna objeto admin ou null)
    public static function buscarPorEmail($email) {
        $adminFixo = self::adminPadrao();
        if ($email === $adminFixo->getEmail()) {
            return $adminFixo;
        }

        try {
            $conexao = new PDO(DSN, ADMIN, SENHA);
            $sql = "SELECT codigo, nome, email, senha FROM admin WHERE email = :email LIMIT 1";
            $comando = $conexao->prepare($sql);
            $comando->bindValue(':email', $email);
            $comando->execute();

            $resultado = $comando->fetch(PDO::FETCH_ASSOC);
            if ($resultado) {
                return new admin(
                    $resultado['codigo'],
                    $resultado['nome'],
                    $resultado['email'],
                    $resultado['senha']
                );
            }
        } catch (PDOException $e) {
            error_log("Erro: " . $e->getMessage());
        }

        return null;
    }

    // Atualizar nome do admin logado
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

    // Listar todos admins
    public static function listarTodos() {
        $conexao = new PDO(DSN, ADMIN, SENHA);
        $sql = "SELECT codigo, nome, email FROM admin ORDER BY nome";
        $comando = $conexao->prepare($sql);
        $comando->execute();
        return $comando->fetchAll(PDO::FETCH_ASSOC);
    }

    // Atualizar nome por email (estático)
    public static function atualizarNomePorEmail($email, $novoNome) {
        $conexao = new PDO(DSN, ADMIN, SENHA);
        $sql = "UPDATE admin SET nome = :nome WHERE email = :email";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':nome', $novoNome);
        $comando->bindValue(':email', $email);
        return $comando->execute();
    }

    // Excluir admin por email (não permite excluir admin padrão)
    public static function excluirPorEmail($email) {
        if ($email === self::adminPadrao()->getEmail()) {
            return false;
        }

        $conexao = new PDO(DSN, ADMIN, SENHA);
        $sql = "DELETE FROM admin WHERE email = :email";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);
        return $comando->execute();
    }

    // Garante que admin padrão exista no banco (inserção se não existir)
    public static function garantirAdminPadraoNoBanco(): bool {
        try {
            $conexao = new PDO(DSN, ADMIN, SENHA);
            $adminFixo = self::adminPadrao();

            $sql = "SELECT email FROM admin WHERE email = :email";
            $comando = $conexao->prepare($sql);
            $comando->bindValue(':email', $adminFixo->getEmail());
            $comando->execute();

            if ($comando->rowCount() === 0) {
                $sqlInserir = "INSERT INTO admin (nome, email, senha) VALUES (:nome, :email, :senha)";
                $comandoInserir = $conexao->prepare($sqlInserir);
                $comandoInserir->bindValue(':nome', $adminFixo->getNome());
                $comandoInserir->bindValue(':email', $adminFixo->getEmail());
                $comandoInserir->bindValue(':senha', password_hash($adminFixo->getSenha(), PASSWORD_DEFAULT));
                return $comandoInserir->execute();
            }
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao garantir admin fixo: " . $e->getMessage());
            return false;
        }
    }
}
?>

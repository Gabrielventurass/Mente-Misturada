<?php
require_once "database.php";

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

    public static function adminPadrao() {
        return new admin(
            "0",
            "Administrador Principal",
            "admin1@gmail.com",
            "d3lta034"
        );
    }

    public function getCodigo(): string { return $this->codigo; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
    public function getSenha(): string { return $this->senha; }
    public function getErro(): string { return $this->erro; }

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

    public function verificarSenha(string $senhaDigitada): bool {
        if ($this->codigo === "0") {
            return $senhaDigitada === $this->senha;
        }
        return password_verify($senhaDigitada, $this->senha);
    }

    public function inserir(): bool {
        try {
            $conexao = conectarPDO();

            $verificaAdmin = $conexao->prepare("SELECT email FROM admin WHERE email = :email");
            $verificaAdmin->bindValue(':email', $this->getEmail());
            $verificaAdmin->execute();
            if ($verificaAdmin->rowCount() > 0) {
                $this->erro = "Este e-mail já está cadastrado como administrador!";
                $conexao = null;
                return false;
            }

            $verificaPendente = $conexao->prepare("SELECT email FROM admins_pendentes WHERE email = :email");
            $verificaPendente->bindValue(':email', $this->getEmail());
            $verificaPendente->execute();
            if ($verificaPendente->rowCount() > 0) {
                $this->erro = "Este e-mail já está aguardando aprovação!";
                $conexao = null;
                return false;
            }

            $sql = "INSERT INTO admins_pendentes (nome, email, senha) VALUES (:nome, :email, :senha)";
            $comando = $conexao->prepare($sql);
            $senhaHash = password_hash($this->senha, PASSWORD_DEFAULT);
            $comando->bindValue(':nome', $this->getNome());
            $comando->bindValue(':email', $this->getEmail());
            $comando->bindValue(':senha', $senhaHash);

            $result = $comando->execute();
            $conexao = null;
            return $result;
        } catch (PDOException $e) {
            $this->erro = "Erro de banco de dados: " . $e->getMessage();
            return false;
        }
    }

    public static function buscarPorEmail($email) {
        $adminFixo = self::adminPadrao();
        if ($email === $adminFixo->getEmail()) {
            return $adminFixo;
        }

        try {
            $conexao = conectarPDO();
            $sql = "SELECT codigo, nome, email, senha FROM admin WHERE email = :email LIMIT 1";
            $comando = $conexao->prepare($sql);
            $comando->bindValue(':email', $email);
            $comando->execute();

            $resultado = $comando->fetch(PDO::FETCH_ASSOC);
            $conexao = null;

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

    public function atualizarNome(): bool {
        try {
            $conexao = conectarPDO();
            $sql = "UPDATE admin SET nome = :nome WHERE email = :email";
            $comando = $conexao->prepare($sql);
            $comando->bindValue(':nome', $this->getNome());
            $comando->bindValue(':email', $this->getEmail());
            $result = $comando->execute();
            $conexao = null;
            return $result;
        } catch (PDOException $e) {
            $this->erro = "Erro ao atualizar nome: " . $e->getMessage();
            return false;
        }
    }

    public static function listarTodos(): array {
        $conexao = conectarPDO();
        $sql = "SELECT codigo, nome, email FROM admin ORDER BY nome";
        $comando = $conexao->prepare($sql);
        $comando->execute();
        $result = $comando->fetchAll(PDO::FETCH_ASSOC);
        $conexao = null;
        return $result;
    }

    public static function atualizarNomePorEmail(string $email, string $novoNome): bool {
        $conexao = conectarPDO();
        $sql = "UPDATE admin SET nome = :nome WHERE email = :email";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':nome', $novoNome);
        $comando->bindValue(':email', $email);
        $result = $comando->execute();
        $conexao = null;
        return $result;
    }

    public static function excluirPorEmail(string $email): bool {
        if ($email === self::adminPadrao()->getEmail()) {
            return false;
        }
        $conexao = conectarPDO();
        $sql = "DELETE FROM admin WHERE email = :email";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);
        $result = $comando->execute();
        $conexao = null;
        return $result;
    }

    public static function garantirAdminPadraoNoBanco(): bool {
        try {
            $conexao = conectarPDO();
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
                $comandoInserir->execute();
            }
            $conexao = null;
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao garantir admin fixo: " . $e->getMessage());
            return false;
        }
    }

    public static function listarPendentes(): array {
        $conexao = conectarPDO();
        $sql = "SELECT codigo, nome, email FROM admins_pendentes ORDER BY nome";
        $comando = $conexao->prepare($sql);
        $comando->execute();
        $result = $comando->fetchAll(PDO::FETCH_ASSOC);
        $conexao->close();
        return $result;
    }
}
?>

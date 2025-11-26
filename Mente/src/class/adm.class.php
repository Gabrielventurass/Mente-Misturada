<?php
require_once "../config/database.php";

class admin {
    private $codigo;
    private $nome;
    private $email;
    private $senha;
    private $erro = '';
    private PDO $pdo;

    public function __construct($codigo, $nome, $email, $senha, PDO $pdo) {
        $this->codigo = $codigo;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
        $this->pdo = $pdo;
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

    public static function buscarPorEmail(string $email): ?admin {
    try {
        $conexao = conectarPDO();
        $sql = "SELECT codigo, nome, email, senha FROM admin WHERE email = :email LIMIT 1";
        $comando = $conexao->prepare($sql);
        $comando->bindValue(':email', $email);
        $comando->execute();

        $resultado = $comando->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            // Note: passamos o PDO ao construtor para manter compatibilidade com a sua classe
            return new admin(
                $resultado['codigo'],
                $resultado['nome'],
                $resultado['email'],
                $resultado['senha'],
                $conexao
            );
        }
    } catch (PDOException $e) {
        error_log("buscarPorEmail erro: " . $e->getMessage());
    }

    return null;
}

    // Retorna o admin padrão (instância válida). Nunca retorna null.
public static function adminPadrao(): admin {
    $emailPadrao = "admin@gmail"; // ajuste se for outro
    $admin = self::buscarPorEmail($emailPadrao);

    if ($admin instanceof admin) {
        return $admin;
    }

    // Se não existir no banco, devolve um objeto "placeholder" seguro
    // (passamos um PDO novo só para satisfazer o construtor)
    return new admin(
        0,
        "Administrador Padrão",
        $emailPadrao,
        "",            // senha vazia (não usada aqui)
        conectarPDO()
    );
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

    // Lista pendentes — versão corrigida (evita usar ->close() em PDO)
public static function listarPendentes(): array {
    try {
        $conexao = conectarPDO();
        $sql = "SELECT codigo, nome, email FROM admins_pendentes ORDER BY nome";
        $comando = $conexao->prepare($sql);
        $comando->execute();
        $result = $comando->fetchAll(PDO::FETCH_ASSOC);
        $conexao = null; // fecha a conexão PDO
        return $result;
    } catch (PDOException $e) {
        error_log("listarPendentes erro: " . $e->getMessage());
        return [];
    }
}
    
    // 🔹 Gera e salva token de recuperação
public static function gerarTokenRecuperacao(PDO $pdo, string $email): string|false {
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

    $sql = "UPDATE admin SET token_recuperacao = :token, token_expira = :expira WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':token' => $token,
        ':expira' => $expira,
        ':email' => $email
    ]);

    return $stmt->rowCount() > 0 ? $token : false;
}

public static function validarToken(PDO $pdo, string $token): array|false {
    $sql = "SELECT * FROM admin WHERE token_recuperacao = :token AND token_expira > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
}

public static function atualizarSenha(PDO $pdo, int $id, string $novaSenha): bool {
    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $sql = "UPDATE admin SET senha = :senha, token_recuperacao = NULL, token_expira = NULL WHERE codigo = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([':senha' => $hash, ':id' => $id]);
}

}
?>

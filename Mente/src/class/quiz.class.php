<?php
class Quiz {
    private $conn;
    private $jsonPath;

    public function __construct() {
        // Conexão MySQL
        $this->conn = new mysqli("localhost", "root", "", "mente");
        if ($this->conn->connect_error) {
            die("Erro: " . $this->conn->connect_error);
        }

        // Caminho dos quizzes JSON
        $this->jsonPath = __DIR__ . "/../quiz_admin/quizzes/68ec050f43a51.json";
    }

    // Retorna um quiz aleatório disponível
    public function obterQuizAleatorio($email_usuario) {
        // 1️⃣ Últimos 3 quizzes respondidos
        $stmt = $this->conn->prepare("SELECT quiz_id FROM resposta_usuario WHERE usuario_id = ? ORDER BY data_resposta DESC LIMIT 3");
        $stmt->bind_param("s", $email_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $ultimos = [];
        while ($row = $result->fetch_assoc()) $ultimos[] = $row['quiz_id'];

        // 2️⃣ Pega quizzes SQL disponíveis
        $sql = "SELECT * FROM quiz";
        if (!empty($ultimos)) {
            $placeholders = implode(',', array_fill(0, count($ultimos), '?'));
            $sql .= " WHERE id NOT IN ($placeholders)";
        }
        $stmt = $this->conn->prepare($sql);
        if (!empty($ultimos)) {
            $types = str_repeat('i', count($ultimos));
            $stmt->bind_param($types, ...$ultimos);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $quizzes = $result->fetch_all(MYSQLI_ASSOC);

        if (!empty($quizzes)) {
            // Escolhe um aleatório
            $quiz = $quizzes[array_rand($quizzes)];

            // Carrega perguntas
            $stmt = $this->conn->prepare("SELECT * FROM pergunta WHERE quiz_id = ?");
            $stmt->bind_param("i", $quiz['id']);
            $stmt->execute();
            $perguntas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($perguntas as &$p) {
                $stmt = $this->conn->prepare("SELECT * FROM alternativa WHERE pergunta_id = ?");
                $stmt->bind_param("i", $p['id']);
                $stmt->execute();
                $p['alternativas'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }

            $quiz['perguntas'] = $perguntas;
            return $quiz;
        }

        // 3️⃣ Se não houver quizzes SQL, tenta JSON
        if (file_exists($this->jsonPath)) {
            $quizzes_json = json_decode(file_get_contents($this->jsonPath), true);
            if (!empty($quizzes_json)) {
                $quiz = $quizzes_json[array_rand($quizzes_json)];

                // Garante IDs para perguntas JSON
                foreach ($quiz['perguntas'] as &$p) {
                    if (!isset($p['id'])) $p['id'] = uniqid();
                }

                return $quiz;
            }
        }

        return null; // Nenhum quiz disponível
    }

    // Salva respostas do usuário
    public function salvarRespostas($post, $usuario_id) {
        $quiz_id = $post['quiz_id'];
        $tempo = $post['tempo'];
        $respostas = $post['respostas'];

        // Conta acertos (simulação)
        $acertos = 0;
        foreach ($respostas as $pergunta_id => $resposta) {
            // Aqui você pode buscar a resposta correta real no SQL
            $acertos++; // só para teste
        }

        $stmt = $this->conn->prepare("INSERT INTO resposta_usuario (usuario_id, quiz_id, acertos, tempo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siii", $usuario_id, $quiz_id, $acertos, $tempo);
        $stmt->execute();

        return "Você acertou $acertos de " . count($respostas) . " perguntas em $tempo segundos.";
    }
}
?>

<?php
class Quiz {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "mente");
        if ($this->conn->connect_error) {
            die("Erro: " . $this->conn->connect_error);
        }
    }

    public function salvarRespostas($post, $usuario_email) {
        $quiz_id = intval($post['quiz_id']);
        $tempo = intval($post['tempo']);
        $respostas = $post['respostas'];

        // Simular respostas corretas
        $corretas = [1 => 'B', 2 => 'B'];
        $acertos = 0;

        foreach ($respostas as $pergunta_id => $resposta) {
            if (isset($corretas[$pergunta_id]) && $resposta === $corretas[$pergunta_id]) {
                $acertos++;
            }
        }

        $stmt = $this->conn->prepare("INSERT INTO resposta_usuario (usuario_email, quiz_id, acertos, tempo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siii", $usuario_email, $quiz_id, $acertos, $tempo);
        $stmt->execute();

        return "Você acertou $acertos de " . count($corretas) . " perguntas em $tempo segundos.";
    }

    // Exemplo de estrutura básica da função
public function criarQuiz($dados) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 1. Inserir quiz
        $stmt = $pdo->prepare("INSERT INTO quiz (tema, art, total_questoes) VALUES (:tema, :art, 0)");
        $stmt->execute([
            'tema' => $dados['tema'],
            'art' => $dados['art']
        ]);

        $quiz_id = $pdo->lastInsertId();

        $total_questoes = 0;

        // 2. Inserir perguntas e alternativas
        foreach ($dados['perguntas'] as $pergunta) {
            $stmt = $pdo->prepare("INSERT INTO pergunta (quiz_id, texto, resposta_correta) VALUES (:quiz_id, :texto, :resposta_correta)");
            $stmt->execute([
                'quiz_id' => $quiz_id,
                'texto' => $pergunta['texto'],
                'resposta_correta' => $pergunta['alternativas'][$pergunta['correta']]
            ]);

            $pergunta_id = $pdo->lastInsertId();

            foreach ($pergunta['alternativas'] as $i => $alt) {
                $stmt = $pdo->prepare("INSERT INTO alternativa (pergunta_id, texto, correta) VALUES (:pergunta_id, :texto, :correta)");
                $stmt->execute([
                    'pergunta_id' => $pergunta_id,
                    'texto' => $alt,
                    'correta' => $i == $pergunta['correta'] ? 1 : 0
                ]);
            }

            $total_questoes++; // conta cada pergunta
        }

        // 3. Atualizar o total de questões
        $stmt = $pdo->prepare("UPDATE quiz SET total_questoes = :total WHERE id = :id");
        $stmt->execute([
            'total' => $total_questoes,
            'id' => $quiz_id
        ]);

        return "Quiz criado com sucesso!";
    } catch (PDOException $e) {
        return "Erro ao criar quiz: " . $e->getMessage();
    }
}

    
    public function obterQuizAleatorio($email_usuario) {
        // Obter últimos 3 quizzes respondidos por este usuário
        $stmt = $this->conn->prepare("SELECT quiz_id FROM resposta_usuario WHERE usuario_email = ? ORDER BY data_resposta DESC LIMIT 3");
        $stmt->bind_param("s", $email_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $ultimosQuizzes = [];
        while ($row = $result->fetch_assoc()) {
            $ultimosQuizzes[] = $row['quiz_id'];
        }
    
        // Obter todos os quizzes disponíveis, exceto os últimos 3
        if (count($ultimosQuizzes) > 0) {
            $placeholders = implode(',', array_fill(0, count($ultimosQuizzes), '?'));
            $types = str_repeat('i', count($ultimosQuizzes));
    
            $sql = "SELECT id FROM quiz WHERE id NOT IN ($placeholders)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$ultimosQuizzes);
        } else {
            $stmt = $this->conn->prepare("SELECT id FROM quiz");
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        $ids = [];
        while ($row = $result->fetch_assoc()) {
            $ids[] = $row['id'];
        }
    
        if (empty($ids)) return null;
    
        $quiz_id = $ids[array_rand($ids)];
    
        // Buscar quiz
        $stmt = $this->conn->prepare("SELECT * FROM quiz WHERE id = ?");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $quiz = $stmt->get_result()->fetch_assoc();
    
        // Buscar perguntas e alternativas
        $stmt = $this->conn->prepare("SELECT * FROM pergunta WHERE quiz_id = ?");
        $stmt->bind_param("i", $quiz_id);
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
    
    public function buscarQuizCompleto($id) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            // Busca o quiz
            $stmtQuiz = $pdo->prepare("SELECT tema, art FROM quiz WHERE id = ?");
            $stmtQuiz->execute([$id]);
            $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);
    
            if (!$quiz) return false;
    
            // Busca as perguntas
            $stmtPerguntas = $pdo->prepare("SELECT id, texto FROM pergunta WHERE quiz_id = ?");
            $stmtPerguntas->execute([$id]);
            $perguntas = [];
    
            while ($pergunta = $stmtPerguntas->fetch(PDO::FETCH_ASSOC)) {
                // Busca alternativas da pergunta
                $stmtAlt = $pdo->prepare("SELECT texto, correta FROM alternativa WHERE pergunta_id = ?");
                $stmtAlt->execute([$pergunta['id']]);
                $alternativas = $stmtAlt->fetchAll(PDO::FETCH_ASSOC);
    
                $perguntas[] = [
                    'texto' => $pergunta['texto'],
                    'alternativas' => $alternativas
                ];
            }
    
            $quiz['perguntas'] = $perguntas;
            return $quiz;
    
        } catch (PDOException $e) {
            return false;
        }
    }

    public function atualizarQuiz($id, $dados) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=mente", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();
    
            // Atualiza tema e artigo
            $stmt = $pdo->prepare("UPDATE quiz SET tema = ?, art = ? WHERE id = ?");
            $stmt->execute([$dados['tema'], $dados['art'], $id]);
    
            // Remove perguntas e alternativas antigas
            $stmtPerguntas = $pdo->prepare("SELECT id FROM pergunta WHERE quiz_id = ?");
            $stmtPerguntas->execute([$id]);
            $perguntasAntigas = $stmtPerguntas->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($perguntasAntigas as $p) {
                $pid = $p['id'];
                $pdo->prepare("DELETE FROM alternativa WHERE pergunta_id = ?")->execute([$pid]);
            }
            $pdo->prepare("DELETE FROM pergunta WHERE quiz_id = ?")->execute([$id]);
    
            // Insere novas perguntas e alternativas
            $totalQuestoes = 0;
            foreach ($dados['perguntas'] as $p) {
                $stmt = $pdo->prepare("INSERT INTO pergunta (quiz_id, texto) VALUES (?, ?)");
                $stmt->execute([$id, $p['texto']]);
                $pergunta_id = $pdo->lastInsertId();
    
                foreach ($p['alternativas'] as $index => $alt) {
                    $correta = ($index == $p['correta']) ? 1 : 0;
                    $stmt = $pdo->prepare("INSERT INTO alternativa (pergunta_id, texto, correta) VALUES (?, ?, ?)");
                    $stmt->execute([$pergunta_id, $alt, $correta]);
                }
                $totalQuestoes++;
            }
    
            // Atualiza total de questões no quiz
            $pdo->prepare("UPDATE quiz SET total_questoes = ? WHERE id = ?")->execute([$totalQuestoes, $id]);
    
            $pdo->commit();
            return "Quiz atualizado com sucesso!";
        } catch (PDOException $e) {
            $pdo->rollBack();
            return "Erro ao atualizar quiz: " . $e->getMessage();
        }
    }
    
}
?>

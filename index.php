<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "cadastro";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

$mensagem = "";

if (isset($_GET["sucesso"])) {
    if ($_GET["sucesso"] == "cadastro") {
        $mensagem = "Cadastro realizado com sucesso!";
    } elseif ($_GET["sucesso"] == "editado") {
        $mensagem = "Registro editado com sucesso!";
    } elseif ($_GET["sucesso"] == "excluido") {
        $mensagem = "Registro excluído com sucesso!";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cpf = $_POST["cpf"];
    $creci = $_POST["creci"];
    $nome = $_POST["nome"];

    if (!empty($_POST["id"])) {
        $id = $_POST["id"];
        $stmt = $conn->prepare("UPDATE corretores SET cpf=?, creci=?, nome=? WHERE id=?");
        $stmt->bind_param("sssi", $cpf, $creci, $nome, $id);
        
        if ($stmt->execute()) {
            header("Location: index.php?sucesso=editado");
            exit();
        }
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM corretores WHERE cpf = ?");
        $checkStmt->bind_param("s", $cpf);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $mensagem = "Erro: CPF já cadastrado!";
        } else {
            $stmt = $conn->prepare("INSERT INTO corretores (cpf, creci, nome) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $cpf, $creci, $nome);

            if ($stmt->execute()) {
                header("Location: index.php?sucesso=cadastro");
                exit();
            }
        }
    }
}

if (isset($_GET["excluir"])) {
    $idExcluir = $_GET["excluir"];
    $stmt = $conn->prepare("DELETE FROM corretores WHERE id = ?");
    $stmt->bind_param("i", $idExcluir);

    if ($stmt->execute()) {
        header("Location: index.php?sucesso=excluido");
        exit();
    }
}

$query = "SELECT id, cpf, creci, nome FROM corretores ORDER BY nome ASC";
$result = $conn->query($query);

$editando = false;
$corretorEdit = ["id" => "", "cpf" => "", "creci" => "", "nome" => ""];

if (isset($_GET["editar"])) {
    $editando = true;
    $idEditar = $_GET["editar"];

    $stmt = $conn->prepare("SELECT id, cpf, creci, nome FROM corretores WHERE id = ?");
    $stmt->bind_param("i", $idEditar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $corretorEdit = $resultado->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Corretor</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <main>
        <h1>Cadastro de Corretor</h1>

        <?php if (!empty($mensagem)) : ?>
            <p class="mensagem-sucesso"><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <input type="hidden" name="id" value="<?php echo $corretorEdit["id"]; ?>">

            <div class="cpf-creci-area">
                <input type="text" name="cpf" placeholder="Digite seu CPF" maxlength="11" minlength="11" require
                    value="<?php echo $corretorEdit["cpf"]; ?>">

                <input type="text" name="creci" placeholder="Digite seu Creci" required minlength="2"
                    value="<?php echo $corretorEdit["creci"]; ?>">
            </div>

            <input type="text" name="nome" placeholder="Digite seu nome" required minlength="2"
                value="<?php echo $corretorEdit["nome"]; ?>">

            <button type="submit">
                <?php echo $editando ? "Salvar" : "Enviar"; ?>
            </button>
        </form>

        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>CPF</th>
                    <th>CRECI</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row["cpf"]; ?></td>
                        <td><?php echo $row["creci"]; ?></td>
                        <td><?php echo $row["nome"]; ?></td>
                        <td class="botoes-ed-ex">
                            <a class="botao-editar" href="index.php?editar=<?php echo $row['id']; ?>">Editar</a>
                            <a class="botao-excluir" href="index.php?excluir=<?php echo $row['id']; ?>">Excluir</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

        <?php else : ?>
            <p>Nenhum corretor cadastrado ainda.</p>
        <?php endif; ?>
    </main>
</body>
</html>
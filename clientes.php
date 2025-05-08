<?php
session_start();
require_once "conexao.php";

// Processar exclusão de cliente
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Verificar se o cliente tem veículos
    $check_veiculos = $conn->prepare("SELECT COUNT(*) as total FROM veiculo WHERE id_cliente = ?");
    $check_veiculos->bind_param("i", $id);
    $check_veiculos->execute();
    $result = $check_veiculos->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['total'] > 0) {
        $mensagem = "Não é possível excluir o cliente pois existem veículos associados.";
        $tipo = "danger";
    } else {
        $stmt = $conn->prepare("DELETE FROM cliente WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensagem = "Cliente excluído com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao excluir cliente: " . $conn->error;
            $tipo = "danger";
        }
        $stmt->close();
    }
}

// Processar adição ou edição de cliente
if (isset($_POST['salvar'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $cpf = $_POST['cpf'];
    $senha = $_POST['senha'];
    
    // Se for edição
    if (isset($_POST['id_cliente']) && !empty($_POST['id_cliente'])) {
        $id = intval($_POST['id_cliente']);
        $stmt = $conn->prepare("UPDATE cliente SET nome_completo = ?, email = ?, telefone = ?, cpf = ?, senha = ? WHERE id_cliente = ?");
        $stmt->bind_param("sssssi", $nome, $email, $telefone, $cpf, $senha, $id);
        
        if ($stmt->execute()) {
            $mensagem = "Cliente atualizado com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao atualizar cliente: " . $conn->error;
            $tipo = "danger";
        }
    } 
    // Se for adição
    else {
        $stmt = $conn->prepare("INSERT INTO cliente (nome_completo, email, telefone, cpf, senha) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nome, $email, $telefone, $cpf, $senha);
        
        if ($stmt->execute()) {
            $mensagem = "Cliente adicionado com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao adicionar cliente: " . $conn->error;
            $tipo = "danger";
        }
    }
    $stmt->close();
}

// Buscar cliente para edição, se necessário
$cliente = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM cliente WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $cliente = $result->fetch_assoc();
    }
    $stmt->close();
}

// Buscar todos os clientes
$sql = "SELECT * FROM cliente ORDER BY nome_completo";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLPaking - Gerenciar Clientes</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F3F4F6;
        }
        .sidebar {
            min-height: calc(100vh - 64px);
            background-color: #FFFFFF;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #6B7280;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 4px 8px;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover {
            background-color: #F3F4F6;
            color: #1E3A8A;
        }
        .sidebar .nav-link.active {
            background-color: #1E3A8A;
            color: #FFFFFF;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .btn-primary {
            background-color: #1E3A8A;
            border-color: #1E3A8A;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #1E40AF;
            border-color: #1E40AF;
        }
        .btn-warning, .btn-danger {
            transition: all 0.2s;
        }
        .btn-warning:hover {
            background-color: #D97706;
            border-color: #D97706;
        }
        .btn-danger:hover {
            background-color: #B91C1C;
            border-color: #B91C1C;
        }
        .table th, .table td {
            padding: 12px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #F3F4F6;
        }
        .form-control {
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            padding: 8px;
        }
        .form-select {
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            padding: 8px;
        }
        .modal-content {
            border-radius: 12px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand flex items-center space-x-2" href="index.php">
                <i class="bi bi-p-square-fill text-2xl text-blue-900"></i>
                <span class="text-xl font-bold text-blue-900">LTParking
                </span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-3 col-lg-2 sidebar">
                <div class="pt-4">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-house-door mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="clientes.php">
                                <i class="bi bi-people mr-2"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="veiculos.php">
                                <i class="bi bi-car-front mr-2"></i> Veículos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="estacionamento_diario.php">
                                <i class="bi bi-calendar-day mr-2"></i> Estacionamento Diário
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mensalistas.php">
                                <i class="bi bi-calendar-month mr-2"></i> Mensalistas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="relatorios.php">
                                <i class="bi bi-graph-up mr-2"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Gerenciar Clientes</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCliente">
                        <i class="bi bi-plus-circle mr-2"></i> Novo Cliente
                    </button>
                </div>

                <?php if (isset($mensagem)): ?>
                <div class="alert alert-<?php echo $tipo; ?> bg-<?php echo $tipo == 'success' ? 'green-100 text-green-800' : 'red-100 text-red-800'; ?> border border-<?php echo $tipo == 'success' ? 'green-300' : 'red-300'; ?> rounded-lg p-4 mb-4 alert-dismissible fade show" role="alert">
                    <?php echo $mensagem; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card bg-white">
                    <div class="card-header bg-blue-900 text-black rounded-t-lg py-3 px-4">
                        <i class="bi bi-people mr-2"></i> Lista de Clientes
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-gray-600">ID</th>
                                        <th class="text-gray-600">Nome</th>
                                        <th class="text-gray-600">Email</th>
                                        <th class="text-gray-600">Telefone</th>
                                        <th class="text-gray-600">CPF</th>
                                        <th class="text-gray-600">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["id_cliente"] . "</td>";
                                            echo "<td>" . $row["nome_completo"] . "</td>";
                                            echo "<td>" . $row["email"] . "</td>";
                                            echo "<td>" . $row["telefone"] . "</td>";
                                            echo "<td>" . $row["cpf"] . "</td>";
                                            echo "<td>
                                                    <a href='clientes.php?edit=" . $row["id_cliente"] . "' class='btn btn-sm btn-warning mr-1'><i class='bi bi-pencil'></i></a>
                                                    <a href='clientes.php?delete=" . $row["id_cliente"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Confirma exclusão?\")'><i class='bi bi-trash'></i></a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center text-gray-500'>Nenhum cliente cadastrado</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-blue-900 text-white rounded-t-lg py-3 px-4">
                    <h5 class="modal-title" id="modalClienteLabel">
                        <?php echo isset($cliente) ? 'Editar Cliente' : 'Novo Cliente'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="clientes.php" method="post">
                    <div class="modal-body p-4">
                        <?php if (isset($cliente)): ?>
                            <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="nome" class="form-label text-gray-700">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" required
                                value="<?php echo isset($cliente) ? $cliente['nome_completo'] : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="form-label text-gray-700">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                value="<?php echo isset($cliente) ? $cliente['email'] : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="telefone" class="form-label text-gray-700">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" required
                                value="<?php echo isset($cliente) ? $cliente['telefone'] : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="cpf" class="form-label text-gray-700">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" required
                                value="<?php echo isset($cliente) ? $cliente['cpf'] : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="senha" class="form-label text-gray-700">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required
                                value="<?php echo isset($cliente) ? $cliente['senha'] : ''; ?>">
                        </div>
                    </div>
                    <div class="modal-footer p-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="salvar" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if (isset($cliente)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalCliente'));
            myModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
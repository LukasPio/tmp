<?php
session_start();
require_once "conexao.php";

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM estacionamento_mensal WHERE id_mensalidade = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $mensagem = "Mensalidade excluída com sucesso!";
        $tipo = "success";
    } else {
        $mensagem = "Erro ao excluir mensalidade: " . $conn->error;
        $tipo = "danger";
    }
    $stmt->close();
}

if (isset($_POST['salvar'])) {
    $id_veiculo = $_POST['id_veiculo'];
    $inicio_periodo = $_POST['inicio_periodo'];
    $fim_periodo = $_POST['fim_periodo'];
    $valor_mensal = $_POST['valor_mensal'];
    $data_pagamento = !empty($_POST['data_pagamento']) ? $_POST['data_pagamento'] : null;
    $vencimento = $_POST['vencimento'];
    
    if (isset($_POST['id_mensalidade']) && !empty($_POST['id_mensalidade'])) {
        $id = intval($_POST['id_mensalidade']);
        $stmt = $conn->prepare("UPDATE estacionamento_mensal SET id_veiculo = ?, inicio_periodo = ?, fim_periodo = ?, valor_mensal = ?, data_pagamento = ?, vencimento = ? WHERE id_mensalidade = ?");
        $stmt->bind_param("isssdsi", $id_veiculo, $inicio_periodo, $fim_periodo, $valor_mensal, $data_pagamento, $vencimento, $id);
        
        if ($stmt->execute()) {
            $mensagem = "Mensalidade atualizada com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao atualizar mensalidade: " . $conn->error;
            $tipo = "danger";
        }
    } 

    else {
        $stmt = $conn->prepare("INSERT INTO estacionamento_mensal (id_veiculo, inicio_periodo, fim_periodo, valor_mensal, data_pagamento, vencimento) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssds", $id_veiculo, $inicio_periodo, $fim_periodo, $valor_mensal, $data_pagamento, $vencimento);
        
        if ($stmt->execute()) {
            $mensagem = "Mensalidade adicionada com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao adicionar mensalidade: " . $conn->error;
            $tipo = "danger";
        }
    }
    $stmt->close();
}

$mensalidade = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM estacionamento_mensal WHERE id_mensalidade = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mensalidade = $result->fetch_assoc();
    }
    $stmt->close();
}

$sql = "SELECT em.*, v.placa, c.nome_completo 
        FROM estacionamento_mensal em 
        JOIN veiculo v ON em.id_veiculo = v.id_veiculo 
        JOIN cliente c ON v.id_cliente = c.id_cliente 
        ORDER BY em.inicio_periodo DESC";
$result = $conn->query($sql);

$sql_veiculos = "SELECT v.id_veiculo, v.placa, c.nome_completo 
                 FROM veiculo v 
                 JOIN cliente c ON v.id_cliente = c.id_cliente 
                 ORDER BY c.nome_completo, v.placa";
$result_veiculos = $conn->query($sql_veiculos);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLPaking - Gerenciar Mensalistas</title>
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
                <span class="text-xl font-bold text-blue-900">SQLPaking</span>
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
                            <a class="nav-link" href="clientes.php">
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
                            <a class="nav-link active" href="mensalistas.php">
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
                    <h1 class="text-2xl font-bold text-gray-800">Gerenciar Mensalistas</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMensalidade">
                        <i class="bi bi-plus-circle mr-2"></i> Nova Mensalidade
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
                        <i class="bi bi-calendar-month mr-2"></i> Lista de Mensalidades
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-gray-600">ID</th>
                                        <th class="text-gray-600">Cliente</th>
                                        <th class="text-gray-600">Placa</th>
                                        <th class="text-gray-600">Início</th>
                                        <th class="text-gray-600">Fim</th>
                                        <th class="text-gray-600">Valor Mensal</th>
                                        <th class="text-gray-600">Pagamento</th>
                                        <th class="text-gray-600">Vencimento</th>
                                        <th class="text-gray-600">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["id_mensalidade"] . "</td>";
                                            echo "<td>" . $row["nome_completo"] . "</td>";
                                            echo "<td>" . $row["placa"] . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["inicio_periodo"])) . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["fim_periodo"])) . "</td>";
                                            echo "<td>R$ " . number_format($row["valor_mensal"], 2, ',', '.') . "</td>";
                                            echo "<td>" . ($row["data_pagamento"] ? date('d/m/Y', strtotime($row["data_pagamento"])) : '-') . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["vencimento"])) . "</td>";
                                            echo "<td>
                                                    <a href='mensalistas.php?edit=" . $row["id_mensalidade"] . "' class='btn btn-sm btn-warning mr-1'><i class='bi bi-pencil'></i></a>
                                                    <a href='mensalistas.php?delete=" . $row["id_mensalidade"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Confirma exclusão?\")'><i class='bi bi-trash'></i></a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center text-gray-500'>Nenhuma mensalidade cadastrada</td></tr>";
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

    <div class="modal fade" id="modalMensalidade" tabindex="-1" aria-labelledby="modalMensalidadeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-blue-900 text-white rounded-t-lg py-3 px-4">
                    <h5 class="modal-title" id="modalMensalidadeLabel">
                        <?php echo isset($mensalidade) ? 'Editar Mensalidade' : 'Nova Mensalidade'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="mensalistas.php" method="post">
                    <div class="modal-body p-4">
                        <?php if (isset($mensalidade)): ?>
                            <input type="hidden" name="id_mensalidade" value="<?php echo $mensalidade['id_mensalidade']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="id_veiculo" class="form-label text-gray-700">Veículo</label>
                            <select class="form-select" id="id_veiculo" name="id_veiculo" required>
                                <option value="">Selecione um veículo</option>
                                <?php
                                if ($result_veiculos->num_rows > 0) {
                                    while($row = $result_veiculos->fetch_assoc()) {
                                        $selected = isset($mensalidade) && $mensalidade['id_veiculo'] == $row['id_veiculo'] ? 'selected' : '';
                                        echo "<option value='" . $row["id_veiculo"] . "' $selected>" . $row["nome_completo"] . " - " . $row["placa"] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="inicio_periodo" class="form-label text-gray-700">Início do Período</label>
                            <input type="datetime-local" class="form-control" id="inicio_periodo" name="inicio_periodo" required
                                value="<?php echo isset($mensalidade) ? date('Y-m-d\TH:i', strtotime($mensalidade['inicio_periodo'])) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="fim_periodo" class="form-label text-gray-700">Fim do Período</label>
                            <input type="datetime-local" class="form-control" id="fim_periodo" name="fim_periodo" required
                                value="<?php echo isset($mensalidade) ? date('Y-m-d\TH:i', strtotime($mensalidade['fim_periodo'])) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="valor_mensal" class="form-label text-gray-700">Valor Mensal</label>
                            <input type="number" step="0.01" class="form-control" id="valor_mensal" name="valor_mensal" required
                                value="<?php echo isset($mensalidade) ? $mensalidade['valor_mensal'] : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="data_pagamento" class="form-label text-gray-700">Data de Pagamento (opcional)</label>
                            <input type="datetime-local" class="form-control" id="data_pagamento" name="data_pagamento"
                                value="<?php echo isset($mensalidade) && $mensalidade['data_pagamento'] ? date('Y-m-d\TH:i', strtotime($mensalidade['data_pagamento'])) : ''; ?>">
                        </div>
                        
                        <div class="mb-4">
                            <label for="vencimento" class="form-label text-gray-700">Vencimento</label>
                            <input type="datetime-local" class="form-control" id="vencimento" name="vencimento" required
                                value="<?php echo isset($mensalidade) ? date('Y-m-d\TH:i', strtotime($mensalidade['vencimento'])) : ''; ?>">
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
    
    <?php if (isset($mensalidade)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var myModal = new bootstrap.Modal(document.getElementById('modalMensalidade'));
            myModal.show();
        });
    </script>
    <?php endif; ?>
</body>
</html>
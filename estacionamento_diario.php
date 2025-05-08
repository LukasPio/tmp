<?php
session_start();
require_once "conexao.php";

if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM estacionamento_diario WHERE id_diaria = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $mensagem = "Registro diário excluído com sucesso!";
        $tipo = "success";
    } else {
        $mensagem = "Erro ao excluir registro diário: " . $conn->error;
        $tipo = "danger";
    }
    $stmt->close();
}

if (isset($_POST['salvar'])) {
    $id_veiculo = $_POST['id_veiculo'];
    $entrada = $_POST['entrada'];
    $saida = $_POST['saida'];
    $valor_hora = $_POST['valor_hora'];

    if (isset($_POST['id_diaria']) && !empty($_POST['id_diaria'])) {
        $id = intval($_POST['id_diaria']);
        $stmt = $conn->prepare("UPDATE estacionamento_diario SET id_veiculo = ?, entrada = ?, saida = ?, valor_hora = ? WHERE id_diaria = ?");
        $stmt->bind_param("issdi", $id_veiculo, $entrada, $saida, $valor_hora, $id);

        if ($stmt->execute()) {
            $mensagem = "Registro diário atualizado com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao atualizar registro diário: " . $conn->error;
            $tipo = "danger";
        }
    }

    else {
        $stmt = $conn->prepare("INSERT INTO estacionamento_diario (id_veiculo, entrada, saida, valor_hora) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $id_veiculo, $entrada, $saida, $valor_hora);

        if ($stmt->execute()) {
            $mensagem = "Registro diário adicionado com sucesso!";
            $tipo = "success";
        } else {
            $mensagem = "Erro ao adicionar registro diário: " . $conn->error;
            $tipo = "danger";
        }
    }
    $stmt->close();
}

$diaria = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM estacionamento_diario WHERE id_diaria = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $diaria = $result->fetch_assoc();
    }
    $stmt->close();
}

$sql = "SELECT ed.*, v.placa, c.nome_completo 
        FROM estacionamento_diario ed 
        JOIN veiculo v ON ed.id_veiculo = v.id_veiculo 
        JOIN cliente c ON v.id_cliente = c.id_cliente 
        ORDER BY ed.entrada DESC";
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
    <title>SQLPaking - Estacionamento Diário</title>
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

        .btn-warning,
        .btn-danger {
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

        .table th,
        .table td {
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand flex items-center space-x-2" href="index.php">
                <i class="bi bi-p-square-fill text-2xl text-blue-900"></i>
                <span class="text-xl font-bold text-blue-900">LTParking</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
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
                            <a class="nav-link active" href="estacionamento_diario.php">
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
                    <h1 class="text-2xl font-bold text-gray-800">Gerenciar Estacionamento Diário</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDiaria">
                        <i class="bi bi-plus-circle mr-2"></i> Novo Registro
                    </button>
                </div>

                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?php echo $tipo; ?> bg-<?php echo $tipo == 'success' ? 'green-100 text-green-800' : 'red-100 text-red-800'; ?> border border-<?php echo $tipo == 'success' ? 'green-300' : 'red-300'; ?> rounded-lg p-4 mb-4 alert-dismissible fade show"
                        role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card bg-white">
                    <div class="card-header bg-blue-900 text-black rounded-t-lg py-3 px-4">
                        <i class="bi bi-calendar-day mr-2"></i> Lista de Registros Diários
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-gray-600">ID</th>
                                        <th class="text-gray-600">Cliente</th>
                                        <th class="text-gray-600">Placa</th>
                                        <th class="text-gray-600">Entrada</th>
                                        <th class="text-gray-600">Saída</th>
                                        <th class="text-gray-600">Valor/Hora</th>
                                        <th class="text-gray-600">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["id_diaria"] . "</td>";
                                            echo "<td>" . $row["nome_completo"] . "</td>";
                                            echo "<td>" . $row["placa"] . "</td>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($row["entrada"])) . "</td>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($row["saida"])) . "</td>";
                                            echo "<td>R$ " . number_format($row["valor_hora"], 2, ',', '.') . "</td>";
                                            echo "<td>
                                                    <a href='estacionamento_diario.php?edit=" . $row["id_diaria"] . "' class='btn btn-sm btn-warning mr-1'><i class='bi bi-pencil'></i></a>
                                                    <a href='estacionamento_diario.php?delete=" . $row["id_diaria"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Confirma exclusão?\")'><i class='bi bi-trash'></i></a>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center text-gray-500'>Nenhum registro diário cadastrado</td></tr>";
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

    <div class="modal fade" id="modalDiaria" tabindex="-1" aria-labelledby="modalDiariaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-blue-900 text-white rounded-t-lg py-3 px-4">
                    <h5 class="modal-title" id="modalDiariaLabel">
                        <?php echo isset($diaria) ? 'Editar Registro Diário' : 'Novo Registro Diário'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="estacionamento_diario.php" method="post">
                    <div class="modal-body p-4">
                        <?php if (isset($diaria)): ?>
                            <input type="hidden" name="id_diaria" value="<?php echo $diaria['id_diaria']; ?>">
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="id_veiculo" class="form-label text-gray-700">Veículo</label>
                            <select class="form-select" id="id_veiculo" name="id_veiculo" required>
                                <option value="">Selecione um veículo</option>
                                <?php
                                if ($result_veiculos->num_rows > 0) {
                                    while ($row = $result_veiculos->fetch_assoc()) {
                                        $selected = isset($diaria) && $diaria['id_veiculo'] == $row['id_veiculo'] ? 'selected' : '';
                                        echo "<option value='" . $row["id_veiculo"] . "' $selected>" . $row["nome_completo"] . " - " . $row["placa"] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="entrada" class="form-label text-gray-700">Entrada</label>
                            <input type="datetime-local" class="form-control" id="entrada" name="entrada" required
                                value="<?php echo isset($diaria) ? date('Y-m-d\TH:i', strtotime($diaria['entrada'])) : ''; ?>">
                        </div>

                        <div class="mb-4">
                            <label for="saida" class="form-label text-gray-700">Saída</label>
                            <input type="datetime-local" class="form-control" id="saida" name="saida" required
                                value="<?php echo isset($diaria) ? date('Y-m-d\TH:i', strtotime($diaria['saida'])) : ''; ?>">
                        </div>

                        <div class="mb-4">
                            <label for="valor_hora" class="form-label text-gray-700">Valor por Hora</label>
                            <input type="number" step="0.01" class="form-control" id="valor_hora" name="valor_hora"
                                required value="<?php echo isset($diaria) ? $diaria['valor_hora'] : ''; ?>">
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

    <?php if (isset($diaria)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var myModal = new bootstrap.Modal(document.getElementById('modalDiaria'));
                myModal.show();
            });
        </script>
    <?php endif; ?>
</body>

</html>
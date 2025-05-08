<?php
session_start();
require_once "conexao.php";

// Consulta para total de mensalidades de abril de 2025
$sql_mensalidades_abril = "SELECT COUNT(*) as total 
                          FROM estacionamento_mensal 
                          WHERE MONTH(inicio_periodo) = 4 AND YEAR(inicio_periodo) = 2025";
$result_mensalidades_abril = $conn->query($sql_mensalidades_abril);
$total_mensalidades_abril = $result_mensalidades_abril->fetch_assoc()['total'];

// Consulta para média diária de veículos
$sql_media_diaria = "SELECT AVG(total_veiculos) as media 
                     FROM (
                         SELECT DATE(entrada) as dia, COUNT(*) as total_veiculos 
                         FROM estacionamento_diario 
                         GROUP BY DATE(entrada)
                     ) as subquery";
$result_media_diaria = $conn->query($sql_media_diaria);
$media_diaria_veiculos = $result_media_diaria->fetch_assoc()['media'];

// Consulta para total de veículos no 1º trimestre
$sql_veiculos_trimestre = "SELECT COUNT(*) as total 
                           FROM estacionamento_diario 
                           WHERE entrada BETWEEN '2025-01-01' AND '2025-03-31'";
$result_veiculos_trimestre = $conn->query($sql_veiculos_trimestre);
$total_veiculos_trimestre = $result_veiculos_trimestre->fetch_assoc()['total'];

// Consulta para tempo médio de permanência
$sql_tempo_medio = "SELECT AVG(TIMESTAMPDIFF(MINUTE, entrada, saida)) as tempo_medio 
                    FROM estacionamento_diario";
$result_tempo_medio = $conn->query($sql_tempo_medio);
$tempo_medio_minutos = $result_tempo_medio->fetch_assoc()['tempo_medio'];

// Consulta para dia com mais veículos
$sql_dia_mais_veiculos = "SELECT DATE(entrada) as dia, COUNT(*) as total 
                          FROM estacionamento_diario 
                          GROUP BY DATE(entrada) 
                          ORDER BY total DESC 
                          LIMIT 1";
$result_dia_mais_veiculos = $conn->query($sql_dia_mais_veiculos);
$dia_mais_veiculos = $result_dia_mais_veiculos->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLPaking - Relatórios</title>
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
        .table th, .table td {
            padding: 12px;
            vertical-align: middle;
        }
        .table-hover tbody tr:hover {
            background-color: #F3F4F6;
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
                            <a class="nav-link" href="mensalistas.php">
                                <i class="bi bi-calendar-month mr-2"></i> Mensalistas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="relatorios.php">
                                <i class="bi bi-graph-up mr-2"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Relatórios</h1>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-calendar-month mr-2 text-blue-900"></i> Mensalidades Abril 2025
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_mensalidades_abril; ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-car-front mr-2 text-green-600"></i> Média Diária Veículos
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($media_diaria_veiculos, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-car-front mr-2 text-blue-500"></i> Veículos 1º Trimestre
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_veiculos_trimestre; ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-clock mr-2 text-yellow-600"></i> Tempo Médio (min)
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($tempo_medio_minutos, 2, ',', '.'); ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-calendar-day mr-2 text-red-600"></i> Dia Mais Movimentado
                            </h5>
                            <p class="text-lg font-medium text-gray-800 mt-2">
                                <?php echo $dia_mais_veiculos ? date('d/m/Y', strtotime($dia_mais_veiculos['dia'])) . ': ' . $dia_mais_veiculos['total'] : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card bg-white">
                    <div class="card-header bg-blue-900 text-black rounded-t-lg py-3 px-4">
                        <i class="bi bi-graph-up mr-2"></i> Relatório Detalhado de Mensalidades
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-gray-600">Cliente</th>
                                        <th class="text-gray-600">Placa</th>
                                        <th class="text-gray-600">Início</th>
                                        <th class="text-gray-600">Fim</th>
                                        <th class="text-gray-600">Valor Mensal</th>
                                        <th class="text-gray-600">Pagamento</th>
                                        <th class="text-gray-600">Vencimento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_detalhado = "SELECT c.nome_completo, v.placa, em.inicio_periodo, em.fim_periodo, em.valor_mensal, em.data_pagamento, em.vencimento 
                                                      FROM estacionamento_mensal em 
                                                      JOIN veiculo v ON em.id_veiculo = v.id_veiculo 
                                                      JOIN cliente c ON v.id_cliente = c.id_cliente 
                                                      ORDER BY em.inicio_periodo DESC";
                                    $result_detalhado = $conn->query($sql_detalhado);

                                    if ($result_detalhado->num_rows > 0) {
                                        while ($row = $result_detalhado->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $row["nome_completo"] . "</td>";
                                            echo "<td>" . $row["placa"] . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["inicio_periodo"])) . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["fim_periodo"])) . "</td>";
                                            echo "<td>R$ " . number_format($row["valor_mensal"], 2, ',', '.') . "</td>";
                                            echo "<td>" . ($row["data_pagamento"] ? date('d/m/Y', strtotime($row["data_pagamento"])) : '-') . "</td>";
                                            echo "<td>" . date('d/m/Y', strtotime($row["vencimento"])) . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center text-gray-500'>Nenhum registro encontrado</td></tr>";
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
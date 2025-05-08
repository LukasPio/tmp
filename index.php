<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLPaking - Dashboard</title>
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
                <span class="text-xl font-bold text-blue-900">TLParking</span>
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
                            <a class="nav-link active" href="index.php">
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
                            <a class="nav-link" href="relatorios.php">
                                <i class="bi bi-graph-up mr-2"></i> Relatórios
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 py-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                </div>

                <?php
                require_once "conexao.php";

 
                // Total de clientes
                $sql_clientes = "SELECT COUNT(*) as total FROM cliente";
                $result_clientes = $conn->query($sql_clientes);
                $total_clientes = $result_clientes->fetch_assoc()['total'];

                // Total de veículos
                $sql_veiculos = "SELECT COUNT(*) as total FROM veiculo";
                $result_veiculos = $conn->query($sql_veiculos);
                $total_veiculos = $result_veiculos->fetch_assoc()['total'];

                // Total de uso diário (hoje)
                $hoje = date('Y-m-d');
                $sql_diario = "SELECT COUNT(*) as total FROM estacionamento_diario WHERE DATE(entrada) = '$hoje'";
                $result_diario = $conn->query($sql_diario);
                $total_diario = $result_diario->fetch_assoc()['total'];

                // Total de mensalistas
                $sql_mensalistas = "SELECT COUNT(DISTINCT id_veiculo) as total FROM estacionamento_mensal";
                $result_mensalistas = $conn->query($sql_mensalistas);
                $total_mensalistas = $result_mensalistas->fetch_assoc()['total'];

                // Mensalistas com pagamento em atraso
                $sql_atraso = "SELECT COUNT(*) as total FROM estacionamento_mensal 
                WHERE data_pagamento > vencimento OR data_pagamento IS NULL";
                $result_atraso = $conn->query($sql_atraso);
                $total_atraso = $result_atraso->fetch_assoc()['total'];
                ?>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-people mr-2 text-blue-900"></i> Clientes
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_clientes; ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-car-front mr-2 text-green-600"></i> Veículos
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_veiculos; ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-calendar-day mr-2 text-blue-500"></i> Diários Hoje
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_diario; ?></p>

                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-calendar-month mr-2 text-yellow-600"></i> Mensalistas
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_mensalistas; ?></p>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-body p-4">
                            <h5 class="text-sm font-semibold text-gray-600 flex items-center">
                                <i class="bi bi-exclamation-triangle mr-2 text-red-600"></i> Em Atraso
                            </h5>
                            <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_atraso; ?></p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="card bg-white">
                        <div class="card-header bg-blue-900 text-black rounded-t-lg py-3 px-4">
                            <i class="bi bi-exclamation-circle mr-2"></i> Mensalidades em Atraso
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-gray-600">Cliente</th>
                                            <th class="text-gray-600">Vencimento</th>
                                            <th class="text-gray-600">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_lista_atraso = "SELECT cl.nome_completo, em.vencimento, em.valor_mensal
                                                             FROM cliente cl
                                                             JOIN veiculo ve ON cl.id_cliente = ve.id_cliente
                                                             JOIN estacionamento_mensal em ON ve.id_veiculo = em.id_veiculo
                                                             WHERE em.data_pagamento > em.vencimento OR em.data_pagamento IS NULL
                                                             ORDER BY em.valor_mensal DESC, cl.nome_completo DESC
                                                             LIMIT 5";
                                        $result_lista_atraso = $conn->query($sql_lista_atraso);

                                        if ($result_lista_atraso->num_rows > 0) {
                                            while ($row = $result_lista_atraso->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row["nome_completo"] . "</td>";
                                                echo "<td>" . date('d/m/Y', strtotime($row["vencimento"])) . "</td>";
                                                echo "<td>R$ " . number_format($row["valor_mensal"], 2, ',', '.') . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='3' class='text-center text-gray-500'>Nenhum registro encontrado</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card bg-white">
                        <div class="card-header bg-green-600 text-black rounded-t-lg py-3 px-4">
                            <i class="bi bi-calendar-check mr-2"></i> Últimos Registros Diários
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-gray-600">Placa</th>
                                            <th class="text-gray-600">Entrada</th>
                                            <th class="text-gray-600">Saída</th>
                                            <th class="text-gray-600">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql_ultimos = "SELECT v.placa, ed.entrada, ed.saida, 
                                                        TIMESTAMPDIFF(HOUR, ed.entrada, ed.saida) * ed.valor_hora AS valor_total
                                                        FROM estacionamento_diario ed
                                                        JOIN veiculo v ON ed.id_veiculo = v.id_veiculo
                                                        ORDER BY ed.entrada DESC
                                                        LIMIT 5";
                                        $result_ultimos = $conn->query($sql_ultimos);

                                        if ($result_ultimos->num_rows > 0) {
                                            while ($row = $result_ultimos->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $row["placa"] . "</td>";
                                                echo "<td>" . date('d/m/Y H:i', strtotime($row["entrada"])) . "</td>";
                                                echo "<td>" . date('d/m/Y H:i', strtotime($row["saida"])) . "</td>";
                                                echo "<td>R$ " . number_format($row["valor_total"], 2, ',', '.') . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='4' class='text-center text-gray-500'>Nenhum registro encontrado</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
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
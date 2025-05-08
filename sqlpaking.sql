-- Criação do banco de dados e seleção
CREATE DATABASE IF NOT EXISTS estacionamento_db;
USE estacionamento_db;

-- Definição da tabela de clientes
CREATE TABLE cliente (
    id_cliente BIGINT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL,
    nome_completo VARCHAR(100) NOT NULL,
    telefone VARCHAR(15) NOT NULL,
    cpf VARCHAR(14) NOT NULL,
    senha VARCHAR(50) NOT NULL
);

-- Tabela de veículos associados aos clientes
CREATE TABLE veiculo (
    id_veiculo BIGINT AUTO_INCREMENT PRIMARY KEY,
    id_cliente BIGINT,
    placa VARCHAR(7) UNIQUE,
    modelo VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

-- Tabela para registros de estacionamento diário
CREATE TABLE estacionamento_diario (
    id_diaria BIGINT PRIMARY key AUTO_INCREMENT ,
    id_veiculo BIGINT NOT NULL,
    entrada DATETIME NOT NULL,
    saida DATETIME NOT NULL,
    valor_hora DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_veiculo) REFERENCES veiculo(id_veiculo)
);

-- Tabela para controle de mensalistas
CREATE TABLE estacionamento_mensal (
    id_mensalidade BIGINT PRIMARY key AUTO_INCREMENT,
    id_veiculo BIGINT,
    inicio_periodo DATETIME NOT NULL,
    fim_periodo DATETIME NOT NULL,
    valor_mensal DECIMAL(10,2) NOT NULL,
    data_pagamento DATETIME,
    vencimento DATETIME,
    FOREIGN KEY (id_veiculo) REFERENCES veiculo(id_veiculo)
);

-- Dados fictícios de clientes
INSERT INTO cliente (email, nome_completo, telefone, cpf, senha) VALUES
('joana@mail.com', 'Joana Ribeiro', '11912345678', '00011122233', 'joana@123'),
('marcos@mail.com', 'Marcos Pereira', '11923456789', '11122233344', 'marcos@321'),
('luana@mail.com', 'Luana Torres', '11934567890', '22233344455', 'luana@456'),
('henrique@mail.com', 'Henrique Dias', '11945678901', '33344455566', 'henrique@789'),
('sandra@mail.com', 'Sandra Costa', '11956789012', '44455566677', 'sandra@123');

-- Dados fictícios de veículos
INSERT INTO veiculo (id_cliente, placa, modelo) VALUES
(1, 'QWE1234', 'Onix'),
(2, 'RTY5678', 'Palio'),
(3, 'UIO9012', 'Celta'),
(4, 'ASD3456', 'Fusca'),
(5, 'FGH7890', 'Fiesta');

-- Registros fictícios de estacionamento diário
INSERT INTO estacionamento_diario (id_veiculo, entrada, saida, valor_hora) VALUES
(1, '2025-04-10 07:45:00', '2025-04-10 09:15:00', 8.50),
(2, '2025-04-11 08:30:00', '2025-04-11 11:00:00', 10.00),
(3, '2025-04-12 13:00:00', '2025-04-12 15:30:00', 12.00),
(4, '2025-04-13 10:15:00', '2025-04-13 12:45:00', 9.50),
(5, '2025-02-18 12:00:00', '2025-02-18 14:00:00', 11.00);

-- Registros fictícios de mensalistas
INSERT INTO estacionamento_mensal (id_veiculo, inicio_periodo, fim_periodo, valor_mensal, data_pagamento, vencimento) VALUES
(1, '2025-04-01', '2025-04-30', 210.00, '2025-04-04', '2025-04-01'),
(2, '2025-04-01', '2025-04-30', 200.00, NULL, '2025-04-01'),
(3, '2025-03-01', '2025-03-31', 220.00, '2025-03-05', '2025-03-01'),
(4, '2025-01-01', '2025-01-31', 190.00, '2025-01-03', '2025-01-01'),
(5, '2025-02-01', '2025-02-28', 230.00, '2025-02-02', '2025-02-01');

-- Listar clientes mensalistas e seus veículos por nome
SELECT cl.nome_completo, ve.placa, ve.modelo
FROM cliente cl
JOIN veiculo ve ON cl.id_cliente = ve.id_cliente
JOIN estacionamento_mensal em ON ve.id_veiculo = em.id_veiculo
ORDER BY cl.nome_completo;

-- Clientes com mensalidade em atraso
SELECT cl.nome_completo, cl.email, em.data_pagamento, em.vencimento
FROM cliente cl
JOIN veiculo ve ON cl.id_cliente = ve.id_cliente
JOIN estacionamento_mensal em ON ve.id_veiculo = em.id_veiculo
WHERE em.data_pagamento > em.vencimento OR em.data_pagamento IS NULL;

-- Pagamentos feitos em abril de 2025 em ordem decrescente de valor
SELECT cl.nome_completo, ve.placa, em.valor_mensal, em.data_pagamento
FROM cliente cl
JOIN veiculo ve ON cl.id_cliente = ve.id_cliente
JOIN estacionamento_mensal em ON ve.id_veiculo = em.id_veiculo
WHERE MONTH(em.data_pagamento) = 4 AND YEAR(em.data_pagamento) = 2025
ORDER BY em.valor_mensal DESC;

-- Quantidade de mensalidades com início em abril de 2025
SELECT COUNT(*) AS total_mensalidades_abril
FROM estacionamento_mensal
WHERE MONTH(inicio_periodo) = 4 AND YEAR(inicio_periodo) = 2025;

-- Média de veículos registrados por dia
SELECT AVG(qtd_veiculos) AS media_diaria_veiculos
FROM (
    SELECT DATE(entrada) AS data, COUNT(*) AS qtd_veiculos
    FROM estacionamento_diario
    GROUP BY DATE(entrada)
) AS contagem_diaria;

-- Quantidade de veículos únicos no 1º trimestre de 2025
SELECT COUNT(DISTINCT id_veiculo) AS total_veiculos_trimestre
FROM estacionamento_diario
WHERE MONTH(entrada) BETWEEN 1 AND 3 AND YEAR(entrada) = 2025;

-- Receita gerada por dia no estacionamento
SELECT DATE(entrada) AS dia, SUM(TIMESTAMPDIFF(HOUR, entrada, saida) * valor_hora) AS faturamento
FROM estacionamento_diario
GROUP BY DATE(entrada);

-- Dia com maior número de veículos
SELECT dia, total
FROM (
    SELECT DATE(entrada) AS dia, COUNT(*) AS total
    FROM estacionamento_diario
    GROUP BY DATE(entrada)
) AS resumo_diario
ORDER BY total DESC
LIMIT 1;

-- Tempo médio de permanência no estacionamento (em minutos)
SELECT AVG(TIMESTAMPDIFF(MINUTE, entrada, saida)) AS tempo_medio_minutos
FROM estacionamento_diario;

-- Datas de pagamento mensal e valores correspondentes
SELECT DATE(data_pagamento) AS data_pagamento, valor_mensal
FROM estacionamento_mensal
ORDER BY data_pagamento;

-- Clientes inadimplentes, ordenados por valor e nome decrescente
SELECT cl.nome_completo, em.valor_mensal, em.data_pagamento, em.vencimento
FROM cliente cl
JOIN veiculo ve ON cl.id_cliente = ve.id_cliente
JOIN estacionamento_mensal em ON ve.id_veiculo = em.id_veiculo
WHERE em.data_pagamento > em.vencimento OR em.data_pagamento IS NULL
ORDER BY em.valor_mensal DESC, cl.nome_completo DESC;

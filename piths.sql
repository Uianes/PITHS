-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 24/01/2026 às 02:48
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `piths`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `ATIVIDADE`
--

CREATE TABLE `ATIVIDADE` (
  `ID_ATIVIDADE` int(11) NOT NULL,
  `PATH_ATIVIDADE` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ATIVIDADE_STATUS`
--

CREATE TABLE `ATIVIDADE_STATUS` (
  `ID_ATIVIDADE` int(11) NOT NULL,
  `MATRICULA` int(11) NOT NULL,
  `TEMPO` time NOT NULL,
  `STATUS` enum('COMPLETO','INCOMPLETO','REFAZER') NOT NULL,
  `TENTATIVAS` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ATIVIDADE_TURMA`
--

CREATE TABLE `ATIVIDADE_TURMA` (
  `ID_TURMA` int(11) NOT NULL,
  `ID_ATIVIDADE` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ESCOLA`
--

CREATE TABLE `ESCOLA` (
  `ID_ESCOLA` int(11) NOT NULL,
  `NOME_ESCOLA` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ESCOLA`
--

INSERT INTO `ESCOLA` (`ID_ESCOLA`, `NOME_ESCOLA`) VALUES
(1, 'E.M.E.F. Sol Nascente'),
(2, 'E.M.E.F. Antônio João'),
(3, 'E.M.E.F. Antonio Liberato'),
(4, 'E.M.E.F. Rui Barbosa'),
(5, 'E.M.C.M.E.F. São João'),
(6, 'S.M.E.');

-- --------------------------------------------------------

--
-- Estrutura para tabela `GAMIFICACAO`
--

CREATE TABLE `GAMIFICACAO` (
  `MATRICULA` int(11) NOT NULL,
  `TOTAL_XP` int(11) NOT NULL DEFAULT 0,
  `LEVEL` int(11) NOT NULL DEFAULT 1,
  `TITLE` enum('INICIANTE','APRENDIZ','PRATICANTE','EXPLORADOR','DESBRAVADOR','MESTRE') DEFAULT 'INICIANTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `PROF_TURMA`
--

CREATE TABLE `PROF_TURMA` (
  `ID_TURMA` int(11) NOT NULL,
  `MATRICULA` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Estrutura para tabela `TURMA`
--

CREATE TABLE `TURMA` (
  `ID_TURMA` int(11) NOT NULL,
  `ANO` year(4) NOT NULL,
  `NOME_TURMA` varchar(120) NOT NULL,
  `SERIE` varchar(120) NOT NULL,
  `ID_ESCOLA` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--

-- --------------------------------------------------------

--
-- Estrutura para tabela `USERS`
--

CREATE TABLE `USERS` (
  `MATRICULA` int(11) NOT NULL,
  `TIPO` enum('ADM','PROF','ALUNO') NOT NULL,
  `NOME` varchar(120) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
  `AVATAR_URL` varchar(120) NOT NULL,
  `BIRTH_DATE` date NOT NULL,
  `ID_ESCOLA` int(11) NOT NULL,
  `ID_TURMA` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ATIVIDADE`
--
ALTER TABLE `ATIVIDADE`
  ADD PRIMARY KEY (`ID_ATIVIDADE`);

--
-- Índices de tabela `ATIVIDADE_STATUS`
--
ALTER TABLE `ATIVIDADE_STATUS`
  ADD KEY `FK_STATUS_USERS` (`MATRICULA`),
  ADD KEY `ID_ATIVIDADE` (`ID_ATIVIDADE`);

--
-- Índices de tabela `ATIVIDADE_TURMA`
--
ALTER TABLE `ATIVIDADE_TURMA`
  ADD UNIQUE KEY `uq_turma_atividade` (`ID_TURMA`,`ID_ATIVIDADE`),
  ADD KEY `ID_ATIVIDADE` (`ID_ATIVIDADE`),
  ADD KEY `ID_TURMA` (`ID_TURMA`);

--
-- Índices de tabela `ESCOLA`
--
ALTER TABLE `ESCOLA`
  ADD PRIMARY KEY (`ID_ESCOLA`);

--
-- Índices de tabela `GAMIFICACAO`
--
ALTER TABLE `GAMIFICACAO`
  ADD KEY `MATRICULA` (`MATRICULA`);

--
-- Índices de tabela `PROF_TURMA`
--
ALTER TABLE `PROF_TURMA`
  ADD KEY `FK_PROF_MATRICULA` (`MATRICULA`),
  ADD KEY `ID_TURMA` (`ID_TURMA`);

--
-- Índices de tabela `TURMA`
--
ALTER TABLE `TURMA`
  ADD PRIMARY KEY (`ID_TURMA`),
  ADD KEY `ID_ESCOLA` (`ID_ESCOLA`);

--
-- Índices de tabela `USERS`
--
ALTER TABLE `USERS`
  ADD PRIMARY KEY (`MATRICULA`),
  ADD KEY `ID_ESCOLA` (`ID_ESCOLA`),
  ADD KEY `ID_TURMA` (`ID_TURMA`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ATIVIDADE`
--
ALTER TABLE `ATIVIDADE`
  MODIFY `ID_ATIVIDADE` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ESCOLA`
--
ALTER TABLE `ESCOLA`
  MODIFY `ID_ESCOLA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `TURMA`
--
ALTER TABLE `TURMA`
  MODIFY `ID_TURMA` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `ATIVIDADE_STATUS`
--
ALTER TABLE `ATIVIDADE_STATUS`
  ADD CONSTRAINT `FK_STATUS_USERS` FOREIGN KEY (`MATRICULA`) REFERENCES `USERS` (`MATRICULA`),
  ADD CONSTRAINT `atividade_status_ibfk_1` FOREIGN KEY (`ID_ATIVIDADE`) REFERENCES `ATIVIDADE` (`ID_ATIVIDADE`);

--
-- Restrições para tabelas `ATIVIDADE_TURMA`
--
ALTER TABLE `ATIVIDADE_TURMA`
  ADD CONSTRAINT `atividade_turma_ibfk_1` FOREIGN KEY (`ID_ATIVIDADE`) REFERENCES `ATIVIDADE` (`ID_ATIVIDADE`),
  ADD CONSTRAINT `atividade_turma_ibfk_2` FOREIGN KEY (`ID_TURMA`) REFERENCES `TURMA` (`ID_TURMA`);

--
-- Restrições para tabelas `GAMIFICACAO`
--
ALTER TABLE `GAMIFICACAO`
  ADD CONSTRAINT `gamificacao_ibfk_1` FOREIGN KEY (`MATRICULA`) REFERENCES `USERS` (`MATRICULA`);

--
-- Restrições para tabelas `PROF_TURMA`
--
ALTER TABLE `PROF_TURMA`
  ADD CONSTRAINT `FK_PROF_MATRICULA` FOREIGN KEY (`MATRICULA`) REFERENCES `USERS` (`MATRICULA`),
  ADD CONSTRAINT `prof_turma_ibfk_1` FOREIGN KEY (`ID_TURMA`) REFERENCES `TURMA` (`ID_TURMA`);

--
-- Restrições para tabelas `TURMA`
--
ALTER TABLE `TURMA`
  ADD CONSTRAINT `turma_ibfk_1` FOREIGN KEY (`ID_ESCOLA`) REFERENCES `ESCOLA` (`ID_ESCOLA`);

--
-- Restrições para tabelas `USERS`
--
ALTER TABLE `USERS`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`ID_ESCOLA`) REFERENCES `ESCOLA` (`ID_ESCOLA`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`ID_TURMA`) REFERENCES `TURMA` (`ID_TURMA`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 25, 2025 at 01:30 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u390193918_Agreval`
--

-- --------------------------------------------------------

--
-- Table structure for table `DEPARTAMENTO`
--

CREATE TABLE `DEPARTAMENTO` (
  `id_departamento` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `nombre_departamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `DEPARTAMENTO`
--

INSERT INTO `DEPARTAMENTO` (`id_departamento`, `tipo`, `nombre_departamento`) VALUES
(1, 'admin', 'Administración'),
(2, 'empleado general', 'mantenimiento'),
(3, 'directivo', 'Dirección General'),
(4, 'directivo', 'Dirección Académica'),
(5, 'administrativo', 'Recursos Humanos'),
(6, 'administrativo', 'Secretaría Académica'),
(7, 'administrativo', 'Contabilidad'),
(8, 'administrativo', 'Prefectura'),
(9, 'docente', 'Maestros Primaria'),
(10, 'docente', 'Maestros Secundaria'),
(11, 'docente', 'Maestros Preparatoria'),
(12, 'apoyo', 'Orientación Educativa'),
(13, 'apoyo', 'Psicopedagogía'),
(14, 'apoyo', 'Sistemas'),
(15, 'servicios', 'Cafetería'),
(16, 'servicios', 'Seguridad');

-- --------------------------------------------------------

--
-- Table structure for table `EMPLEADOS`
--

CREATE TABLE `EMPLEADOS` (
  `id_empleado` int(11) NOT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_ingreso_escuela` date DEFAULT NULL,
  `rfc` varchar(13) DEFAULT NULL,
  `estado_activo` tinyint(1) DEFAULT NULL,
  `nss` varchar(20) DEFAULT NULL,
  `domicilio` varchar(255) DEFAULT NULL,
  `telefono_personal` varchar(20) DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `id_departamento` int(11) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `contraseña` varchar(255) DEFAULT NULL,
  `foto_de_perfil` blob DEFAULT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `historial_permisos` text DEFAULT NULL,
  `rol` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `EMPLEADOS`
--

INSERT INTO `EMPLEADOS` (`id_empleado`, `cargo`, `fecha_nacimiento`, `fecha_ingreso_escuela`, `rfc`, `estado_activo`, `nss`, `domicilio`, `telefono_personal`, `curp`, `id_departamento`, `correo`, `contraseña`, `foto_de_perfil`, `nickname`, `historial_permisos`, `rol`) VALUES
(1, 'Administrador del sistema', NULL, '2025-03-21', '', 1, '', '', '', '', 1, 'admin@elagreval.icu', '$2y$10$gjYfVHKTWk1.XkWCOWmdiuLk4vuav6SoTCrmldTE431UMPcLHPL/.', NULL, 'Admin', NULL, 'Administrador'),
(2, 'probar', '1980-03-18', NULL, 'prubaprueba', 1, '299389389', 'probando ', '2399843', 'pruebapruebaa', 2, 'juanprueba@elagreval.icu', '$2y$10$xJonTEIaxZl09g1tyBPwHu4u2QG8.WlApopp5SCshRR6CdioklK76', NULL, 'juanprueba', NULL, 'Empleado'),
(3, 'probar baja', '2025-03-13', '2025-03-25', 'ksadjfjalksf', 0, 'asdkjf', 'asldjflkajsldf', '828373', 'asdlkjflajslkdf', 8, 'pruebabaja@elagreval.icu', '$2y$10$8SWpSFDf/sIyvtC6uLD9veARHkNMYlJcCQ3uQxZsBBTTYCRv4K0mC', NULL, 'prubea baja', NULL, 'Empleado'),
(4, 'ingeniera', '2004-09-30', '2025-02-22', '45656789', 1, '3456678', 'sutaj', '3218675647', '10283856', 1, 'jvalenmartinez30@elagreval.icu', '$2y$10$8c72/sM2kkKqLRXHf5zjSudfIgikAjR4Cplg4xo63/1rDtkIJDMcu', NULL, 'valen', NULL, 'Empleado'),
(5, 'Director General', '1970-05-15', '2015-01-10', '0', 1, '12345678901', 'Calle Principal 123, Centro', '4771234567', '0', 3, 'roberto.fernandez@elagreval.icu', '$2y$10$rvQFVWSgq7rMow6RUh2bVetNFcTabqCldaVXMBxmbcf82p8BRpcJq', NULL, 'Roberto fernandez', NULL, 'Empleado'),
(6, 'Directora Académica', '1975-07-22', '2017-03-01', 'LOMA750722XYZ', 1, '23456789012', 'Av. Reforma 456, Jardines, 5', '4772345678', 'LOMA750722MDFPZR02', 4, 'maria.lopez@elagreval.icu', '$2y$10$osoQKfYKSp3712YO27NIneJMwZHgQDBMu1OfXRUpbTFFGXUUJ7YRC', NULL, 'María López', NULL, 'Empleado'),
(7, 'Jefa de Recursos Humanos', '1980-11-30', '2018-05-15', 'RAMP801130DEF', 1, '34567890123', 'Blvd. Torres 789, Las Flores', '4773456789', 'RAMP801130MDFRTR01', 5, 'patricia.ramirez@elagreval.icu', '$2y$10$6VA2t6o6Gt96nTJvUJWJQ.2oJTc8EfPX9EJzD8DkGkzDRLr7iqk1K', NULL, 'Patricia Ramírez', NULL, 'Empleado'),
(8, 'Secretaria Académica', '1985-03-17', '2019-01-20', 'GARA850317GHI', 1, '45678901234', 'Paseo del Río 234, Arboledas', '4774567890', 'GARA850317MDFRCN06', 6, 'ana.garcia@elagreval.icu', '$2y$10$gK.DtdoGp/kP0wJwqKmzZ.R.u/KDpJIaoudaoJk2Yx9mdgpV106om', NULL, 'Ana García', NULL, 'Empleado'),
(9, 'Contador', '1982-09-05', '2019-06-10', 'MARP820905JKL', 1, '56789012345', 'Calle Pinos 567, Bosques', '4775678901', 'MARP820905HDFRTD07', 7, 'pedro.martinez@elagreval.icu', '$2y$10$k2dAREiDcD/udeWIl4gpb.x6fI2Bukbmrxt45UzD1G31Kp23wj7nW', NULL, 'Pedro Martínez', NULL, 'Empleado'),
(10, 'Prefecto General', '1978-02-12', '2018-01-15', 'TORC780212MNO', 1, '67890123456', 'Av. Los Lagos 890, Del Valle', '4776789012', 'TORC780212HDFRRL03', 8, 'carlos.torres@elagreval.icu', '$2y$10$wdtWX6QrDUxhhTxftJfUL.fcnTrKUPec4qTJz2WIgHIU/VBRUXFzG', NULL, 'Carlos Torres', NULL, 'Empleado'),
(11, 'Maestra de 1° Primaria', '1988-06-28', '2020-08-01', 'SANL880628PQR', 1, '78901234567', 'Calle Educación 123, Magisterial', '4777890123', 'SANL880628MDFNCS08', 9, 'luisa.sanchez@elagreval.icu', '$2y$10$QZzc0GBjjGVoKNxwRfjnNeVpwUcfcdARGpQod5g5rMeqUFjXAmqVm', NULL, 'Luisa Sánchez', NULL, 'Empleado'),
(12, 'Maestro de 2° Primaria', '1990-09-18', '2020-08-01', 'DIAA900918ABC', 1, '78901234568', 'Calle Progreso 234, Educación', '4777890124', 'DIAA900918HDFLZL03', 9, 'alejandro.diaz@elagreval.icu', '$2y$10$irBnyxgFDfBGomJaKxI3fu155Ml2jKZYb1qmWZYvj2Q2CPZzxdMO.', NULL, 'Alejandro Díaz', NULL, 'Empleado'),
(13, 'Maestra de 3° Primaria', '1987-11-12', '2019-08-01', 'GUTI871112DEF', 1, '78901234569', 'Av. Conocimiento 345, Saber', '4777890125', 'GUTI871112MDFTZS05', 9, 'isabel.gutierrez@elagreval.icu', '$2y$10$QvrdxeXEJ.rxTVsifnqHKumgoklqkXyrTxawszjIjeLc7ESmDv1He', NULL, 'Isabel Gutiérrez', NULL, 'Empleado'),
(14, 'Maestro de 4° Primaria', '1985-07-25', '2018-08-01', 'VEGF850725GHI', 1, '78901234570', 'Calle Aprendizaje 456, Escolar', '4777890126', 'VEGF850725HDFRFR01', 9, 'fernando.vega@elagreval.icu', '$2y$10$e4bETxkLHqc8qTIwyXBtmOQNRkTbqpyr/E3ydJbb9m1PCaHHWCL96', NULL, 'Fernando Vega', NULL, 'Empleado'),
(15, 'Maestra de 5° Primaria', '1989-03-08', '2020-08-01', 'MORC890308JKL', 1, '78901234571', 'Blvd. Sabiduría 567, Academia', '4777890127', 'MORC890308MDFRRR09', 9, 'carmen.morales@elagreval.icu', '$2y$10$mtujRc.IYMeMeqIaZuEmcOa09wqhwqgi6Tz.Q9UkVjf9pdm2yQS/W', NULL, 'Carmen Morales', NULL, 'Empleado'),
(16, 'Maestro de 6° Primaria', '1986-12-19', '2019-08-01', 'FLOR861219MNO', 1, '78901234572', 'Av. Ciencia 678, Didáctica', '4777890128', 'FLOR861219HDFCRC04', 9, 'ricardo.flores@elagreval.icu', '$2y$10$05G35cj5SUQeVVHbSiRYYevCEqlVyTLVeepKcEeR2PGqGKqYJjwj2', NULL, 'Ricardo Flores', NULL, 'Empleado'),
(17, 'Maestro de Matemáticas', '1983-12-09', '2019-03-15', 'HERJ831209STU', 1, '89012345678', 'Blvd. Educativo 456, Moderna', '4778901234', 'HERJ831209HDFRNV04', 10, 'javier.hernandez@elagreval.icu', '$2y$10$ApmGb.TznfofmydBt.h2Y.orcTeL5Xz/lPQMWcFD.9QvSFIMV9TAO', NULL, 'Javier Hernández', NULL, 'Empleado'),
(18, 'Maestra de Español', '1984-05-22', '2019-08-15', 'MENL840522PQR', 1, '89012345679', 'Calle Literatura 234, Letras', '4778901235', 'MENL840522MDFNDR03', 10, 'laura.mendoza@elagreval.icu', '$2y$10$22E1e75sBnVk4YIZj8x7seWvRxtDWmH/oOnBERbEdasRwy.LZrU8S', NULL, 'Laura Mendoza', NULL, 'Empleado'),
(19, 'Maestro de Ciencias', '1982-02-15', '2018-08-15', 'JIMA820215STU', 1, '89012345680', 'Av. Cientifica 345, Laboratorio', '4778901236', 'JIMA820215HDFRNT08', 10, 'antonio.jimenez@elagreval.icu', '$2y$10$ipFFopBGxlXw8Tvn93KQAeoWHHLztK.dTI/SN2EewliaQx3m0FYMu', NULL, 'Antonio Jiménez', NULL, 'Empleado'),
(20, 'Maestra de Historia', '1986-09-12', '2020-08-15', 'ORTV860912VWX', 1, '89012345681', 'Calle Memoria 456, Pasado', '4778901237', 'ORTV860912MDFRR09', 10, 'veronica.ortiz@elagreval.icu', '$2y$10$AFut2/WFMoECkVtCrSxyHO5RfeQSLTqYnnftXb6WK85Z8rS48z7au', NULL, 'Verónica Ortiz', NULL, 'Empleado'),
(21, 'Maestro de Educación Física', '1988-11-28', '2020-08-15', 'VARO881128YZA', 1, '89012345682', 'Av. Deportes 567, Olímpica', '4778901238', 'VARO881128HDFRRK07', 10, 'Oscar.vargas@elagreval.icu', '$2y$10$upVVuQsgs8iZ/kaouC8RAOrJkirjAiAKYgktwUJJrnuPKiBAyQHVG', NULL, 'Óscar Vargas', NULL, 'Empleado'),
(22, 'Maestro de Química', '1979-08-21', '2018-09-01', 'GOGM790821VWX', 1, '90123456789', 'Av. Central 789, Bachillerato', '4779012345', 'GOGM790821HDFNNV05', 11, 'miguel.gonzalez@elagreval.icu', '$2y$10$d4DDLNizjXyEeILGuBwes.FwfPAMm4rM0t94.MQ.wggQuarmeTK.2', NULL, 'Miguel González', NULL, 'Empleado'),
(23, 'Maestra de Biología', '1980-04-15', '2018-09-01', 'RODS800415ABC', 0, '90123456790', 'Calle Ciencias 234, Vida', '4779012346', 'RODS800415MDFDLV02', 11, 'silvia.rodriguez@elagreval.icu', '$2y$10$RCe.pEtH1ZmU7NbyDpORGukrRpirL6Gn5xruvG4QITXgx/AEfdygW', NULL, 'Silvia Rodríguez', NULL, 'Empleado'),
(24, 'Maestro de Física', '1978-06-29', '2017-09-01', 'NAVH780629DEF', 1, '90123456791', 'Av. Newton 345, Einstein', '4779012347', 'NAVH780629HDFVRC06', 11, 'hector.navarro@elagreval.icu', '$2y$10$iIgyfu5jJyj3gF.vQ7sf3u1NteMAc35gFQTPJryoic6No0SkPIiga', NULL, 'Héctor Navarro', NULL, 'Empleado'),
(25, 'Maestra de Literatura', '1982-10-05', '2018-09-01', 'TOTG821005GHI', 1, '90123456792', 'Calle Letras 456, Poesía', '4779012348', 'TOTG821005MDFRR08', 11, 'gabriela.torres@elagreval.icu', '$2y$10$BFsAPtLxYe5SjR/KFlGKJO0Dr7U8n9dOICXK6HOnMkN99Zvq42eUC', NULL, 'Gabriela Torres', NULL, 'Empleado'),
(26, 'Maestro de Matemáticas Avanzadas', '1981-12-12', '2018-09-01', 'CRUR811212JKL', 1, '90123456793', 'Blvd. Álgebra 567, Cálculo', '4779012349', 'CRUR811212HDFRRB09', 11, 'roberto.cruz@elagreval.icu', '$2y$10$CDCduNYnVQcZDBtfe6eqHOrm28hfKvSBQH48.ZTP2gNkxwYtM2ymO', NULL, 'Roberto Cruz', NULL, 'Empleado'),
(27, 'Orientadora Educativa', '1986-04-18', '2020-05-01', 'FLOD860418YZA', 1, '01234567890', 'Callejón Educativo 234, Panorama', '4770123456', 'FLOD860418MDFLDN09', 12, 'daniela.flores@elagreval.icu', '$2y$10$ffhE8Q9TMziCxWPYQSX5PeLS66oe8TE09h/7FSEDOyWj0vOcqLes6', NULL, 'Daniela Flores', NULL, 'Empleado'),
(28, 'Psicopedagoga', '1987-09-15', '2019-08-10', 'PERS870915ASD', 1, '12233445566', 'Calle Psicología 123, Desarrollo', '4771122334', 'PERS870915MDFRZF07', 13, 'sofia.perez@elagreval.icu', '$2y$10$B/UDKJ19DHAlqAf//BPbMO7qMIa4AEtAHTOIpc4rauekOpdtZs2dq', NULL, 'Sofía Pérez', NULL, 'Empleado'),
(29, 'Encargado de Sistemas', '1984-05-25', '2019-01-15', 'CAGE840525QWE', 1, '98877665544', 'Av. Tecnológica 456, Digital', '4779988776', 'CAGE840525HDFSRD03', 14, 'eduardo.castro@elagreval.icu', '$2y$10$h0ON1fLU/0GisWnnle5nve4ZAxWkTBT4WEUI2QrsTGfBOAcTi8x76', NULL, 'Eduardo Castro', NULL, 'Empleado'),
(30, 'Encargada de Cafetería', '1980-07-30', '2018-08-15', 'TOMA800730FGH', 1, '11223344556', 'Av. Alimentos 123, Gastronómica', '4771212123', 'TOMA800730MDFRRT08', 15, 'martha.torres@elagreval.icu', '$2y$10$woigYjuAznb7XfoV7f8WYe80GYsUODOYhY.mxWjb2DYL9M9DUYqoe', NULL, 'Martha Torres', NULL, 'Empleado'),
(31, 'Jefe de Seguridad', '1973-03-05', '2017-05-20', 'GURA730305RTY', 1, '99887766554', 'Calle Vigilancia 456, Protección', '4779876543', 'GURA730305HDFTTL02', 16, 'raul.gutierrez@elagreval.icu', '$2y$10$Izq9OOY0PAIyjZ3Rg2w2ze58OJAE5WZBR2A548JK2bZMFRzZrlbde', NULL, 'Raúl Gutiérrez', NULL, 'Empleado'),
(32, 'Administrador de nomina', '1998-02-25', '2022-06-07', '45656789', 1, '', '', '3218659743', '10283856', 5, 'rr.hh@elagreval.icu', '$2y$10$0Rn1Jaz9gDzQ0UDFaYrmae82wqtouM1T/o7pa6ShQ4oahLzf4z1bW', NULL, 'RR.HH', NULL, 'RRHH administrador'),
(33, 'empleado', '2000-09-12', NULL, 'shshhs', 0, '2ujsjsjs', 'abxeie', '3333333333', 'shhh', 15, 'ahdhdhjd@elagreval.icu', '$2y$10$5Y/PGF/KH3ppCW.oRmYy5.wlLGdVxzdcg/wR5HJGCIHhNLFMRy8qe', NULL, 'msshdh', NULL, 'Empleado'),
(34, 'sddfasfd', '2025-04-17', '2025-04-16', 'nsdknfkl2938', 1, 'kdfkljas23', 'asdfasdf', '2439490', 'kjahsdf28934', 15, 'julianprueba@elagreval.icu', '$2y$10$.g3w5ATlkyd6IUX2CWoBUuT5MrflDQzX.LNylMOabelEMAffXenKu', NULL, 'julian prueba', NULL, 'Empleado'),
(35, 'probar rrhh', '2025-04-22', NULL, 'cdc', 1, '353', '53443', '3543', 'dcd', 4, 'rrhh@elagreval.icu', '$2y$10$8fpGeIGwoGaVibIL7BN2mOhWRTtnw8Z/d/BFBMUHQdxS0fTlKDP6C', NULL, 'rrhh', NULL, 'RRHH administrador');

-- --------------------------------------------------------

--
-- Table structure for table `INCAPACIDADES`
--

CREATE TABLE `INCAPACIDADES` (
  `id_incapacidad` int(11) NOT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `documento_justificativo` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `estado_aprobacion` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp(),
  `comentario_rrhh` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `INCAPACIDADES`
--

INSERT INTO `INCAPACIDADES` (`id_incapacidad`, `id_empleado`, `fecha_inicio`, `fecha_finalizacion`, `tipo`, `documento_justificativo`, `estado`, `estado_aprobacion`, `fecha_solicitud`, `comentario_rrhh`) VALUES
(1, 5, '2025-04-22', '2025-04-24', 'Enfermedad General', 'prueba', 1, 'aprobada', '2025-04-22 20:48:28', 'mandas foto'),
(2, 5, '2025-04-24', '2025-06-21', 'Maternidad', 'prueba 2', 0, 'rechazada', '2025-04-22 20:49:09', 'eres hombre\r\n'),
(3, 6, '2025-04-20', '2025-04-21', 'Accidente de Trabajo', 'uploads/incapacidades/680803ca28a9c_6.pdf', 1, 'aprobada', '2025-04-22 21:02:02', 'okas'),
(4, 6, '2025-04-18', '2025-04-20', 'Maternidad', 'uploads/incapacidades/680804fc62db6_6.jpg', 0, 'rechazada', '2025-04-22 21:07:08', 'mentirosa\r\n'),
(5, 6, '2025-04-21', '2025-04-26', 'Maternidad', 'uploads/incapacidades/680806fc1ca58_6.png', 0, 'rechazada', '2025-04-22 21:15:40', 'esa foto que'),
(6, 7, '2025-04-24', '2025-04-30', 'Enfermedad General', 'uploads/incapacidades/68086f8049553_7.png', 0, 'rechazada', '2025-04-23 04:41:36', 'falta archivo adjunto\r\n'),
(7, 7, '2025-04-17', '2025-04-26', 'Accidente de Trabajo', 'uploads/incapacidades/680874255f21b_7.png', 1, 'pendiente', '2025-04-23 05:01:25', NULL),
(8, 4, '2025-04-12', '2025-04-14', 'Enfermedad General', 'uploads/incapacidades/680906628bc8a_4.png', 1, 'aprobada', '2025-04-23 15:25:22', '');

-- --------------------------------------------------------

--
-- Table structure for table `NOMINAS`
--

CREATE TABLE `NOMINAS` (
  `id_nomina` int(11) NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `salario_bruto` float DEFAULT NULL,
  `impuesto` float DEFAULT NULL,
  `salario_neto` float DEFAULT NULL,
  `fecha_inicio_trabajo` date DEFAULT NULL,
  `fecha_final_trabajo` date DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `estado_activo` tinyint(1) DEFAULT NULL,
  `historial_nomina` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `id_empleado`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(2, 4, '$2y$10$QVYZxVIZ5qGfb1ODW5Tow.YDBGF6IiLErQ9NxIQ9Iw5LDl9T/8zL2', '2025-04-24 05:37:51', NULL, '2025-04-23 05:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `PERMISOS`
--

CREATE TABLE `PERMISOS` (
  `id_permiso` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `VACACIONES`
--

CREATE TABLE `VACACIONES` (
  `id_vacaciones` int(11) NOT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `dias_totales` int(11) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `estado_aprobacion` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp(),
  `comentario_rrhh` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `VACACIONES`
--

INSERT INTO `VACACIONES` (`id_vacaciones`, `id_empleado`, `fecha_inicio`, `fecha_finalizacion`, `estado`, `dias_totales`, `comentarios`, `estado_aprobacion`, `fecha_solicitud`, `comentario_rrhh`) VALUES
(1, 14, '2025-04-26', '2025-05-09', 1, 14, 'voy voy de vacas con mi familia', 'aprobada', '2025-04-24 23:54:50', 'vas pues'),
(2, 14, '2025-05-17', '2025-05-18', 1, 2, 'de fin de semana', 'rechazada', '2025-04-24 23:55:43', 'un mes???????'),
(3, 15, '2025-04-25', '2025-04-28', 1, 4, 'de fin de samana', 'aprobada', '2025-04-24 23:58:42', 'si bueno'),
(4, 15, '2025-07-05', '2025-07-25', 0, 21, 'de chill', 'aprobada', '2025-04-24 23:59:20', 'no'),
(5, 16, '2025-04-25', '2025-04-26', 1, 2, '', 'aprobada', '2025-04-25 00:05:52', 'vas pues'),
(6, 16, '2025-04-26', '2025-04-29', 1, 4, 'si??????', 'aprobada', '2025-04-25 00:36:39', 'okis'),
(7, 16, '2025-05-16', '2025-05-23', 1, 8, 'que tal?', 'aprobada', '2025-04-25 00:36:57', 'pues poquito mejor que dos meses'),
(8, 16, '2025-07-18', '2025-09-13', 0, 58, 'que te parece?', 'rechazada', '2025-04-25 00:37:17', 'dos meses, tas locoooooooo?????'),
(9, 17, '2025-04-25', '2025-04-26', 1, 2, '', 'aprobada', '2025-04-25 00:56:39', 'ok'),
(10, 17, '2025-04-30', '2025-05-09', 0, 10, '', 'rechazada', '2025-04-25 00:57:04', 'no'),
(11, 17, '2025-04-30', '2025-05-09', 1, 10, '', 'aprobada', '2025-04-25 00:58:53', 'v'),
(12, 17, '2025-04-26', '2025-05-23', 0, 28, 'asdf', 'rechazada', '2025-04-25 01:15:48', 'no'),
(13, 17, '2025-05-23', '2025-06-27', 1, 36, 'asdf', 'aprobada', '2025-04-25 01:15:59', 'vas'),
(14, 17, '2025-06-13', '2025-07-18', 1, 36, '', 'pendiente', '2025-04-25 01:16:07', NULL),
(15, 17, '2025-11-15', '2026-04-24', 1, 161, 'que tal', 'rechazada', '2025-04-25 01:16:25', 'medio añoooooooo tas loco');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `DEPARTAMENTO`
--
ALTER TABLE `DEPARTAMENTO`
  ADD PRIMARY KEY (`id_departamento`);

--
-- Indexes for table `EMPLEADOS`
--
ALTER TABLE `EMPLEADOS`
  ADD PRIMARY KEY (`id_empleado`),
  ADD KEY `id_departamento` (`id_departamento`);

--
-- Indexes for table `INCAPACIDADES`
--
ALTER TABLE `INCAPACIDADES`
  ADD PRIMARY KEY (`id_incapacidad`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `NOMINAS`
--
ALTER TABLE `NOMINAS`
  ADD PRIMARY KEY (`id_nomina`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `PERMISOS`
--
ALTER TABLE `PERMISOS`
  ADD PRIMARY KEY (`id_permiso`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indexes for table `VACACIONES`
--
ALTER TABLE `VACACIONES`
  ADD PRIMARY KEY (`id_vacaciones`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `DEPARTAMENTO`
--
ALTER TABLE `DEPARTAMENTO`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `EMPLEADOS`
--
ALTER TABLE `EMPLEADOS`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `INCAPACIDADES`
--
ALTER TABLE `INCAPACIDADES`
  MODIFY `id_incapacidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `NOMINAS`
--
ALTER TABLE `NOMINAS`
  MODIFY `id_nomina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `PERMISOS`
--
ALTER TABLE `PERMISOS`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `VACACIONES`
--
ALTER TABLE `VACACIONES`
  MODIFY `id_vacaciones` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `EMPLEADOS`
--
ALTER TABLE `EMPLEADOS`
  ADD CONSTRAINT `EMPLEADOS_ibfk_1` FOREIGN KEY (`id_departamento`) REFERENCES `DEPARTAMENTO` (`id_departamento`) ON DELETE SET NULL;

--
-- Constraints for table `INCAPACIDADES`
--
ALTER TABLE `INCAPACIDADES`
  ADD CONSTRAINT `INCAPACIDADES_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADOS` (`id_empleado`) ON DELETE CASCADE;

--
-- Constraints for table `NOMINAS`
--
ALTER TABLE `NOMINAS`
  ADD CONSTRAINT `NOMINAS_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADOS` (`id_empleado`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADOS` (`id_empleado`) ON DELETE CASCADE;

--
-- Constraints for table `PERMISOS`
--
ALTER TABLE `PERMISOS`
  ADD CONSTRAINT `PERMISOS_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADOS` (`id_empleado`) ON DELETE CASCADE;

--
-- Constraints for table `VACACIONES`
--
ALTER TABLE `VACACIONES`
  ADD CONSTRAINT `VACACIONES_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `EMPLEADOS` (`id_empleado`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

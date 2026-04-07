-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Gegenereerd op: 07 apr 2026 om 12:08
-- Serverversie: 8.4.8
-- PHP-versie: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaraunt`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `categorieen`
--

CREATE TABLE `categorieen` (
  `id` int UNSIGNED NOT NULL,
  `naam` varchar(100) NOT NULL,
  `icoon` varchar(50) NOT NULL DEFAULT 'fa-star',
  `volgorde` int UNSIGNED NOT NULL DEFAULT '99'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Gegevens worden geëxporteerd voor tabel `categorieen`
--

INSERT INTO `categorieen` (`id`, `naam`, `icoon`, `volgorde`) VALUES
(1, 'Friet', 'fa-fire-flame-curved', 1),
(2, 'Gezinszakken', 'fa-users', 2),
(3, 'Sauzen', 'fa-jar', 3),
(4, 'Snacks', 'fa-drumstick-bite', 4),
(5, 'Vegetarisch', 'fa-leaf', 5),
(6, 'Broodjes', 'fa-burger', 6),
(7, 'Menus', 'fa-plate-wheat', 7),
(8, 'Dranken', 'fa-glass-water', 8);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `orders`
--

CREATE TABLE `orders` (
  `ordernummer` int NOT NULL,
  `klant-naam` text NOT NULL,
  `ophaaltijd` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Gegevens worden geëxporteerd voor tabel `orders`
--

INSERT INTO `orders` (`ordernummer`, `klant-naam`, `ophaaltijd`) VALUES
(69, 'Karlen Dagdas', '17:45:00'),
(67, 'Kerem chatchatrian', '19:00:00'),
(21, 'Abshir Adani', '18:00:00');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `producten`
--

CREATE TABLE `producten` (
  `id` int UNSIGNED NOT NULL,
  `categorie_id` int UNSIGNED NOT NULL,
  `naam` varchar(200) NOT NULL,
  `beschrijving` text,
  `maat` varchar(50) DEFAULT NULL,
  `prijs` decimal(6,2) NOT NULL DEFAULT '0.00',
  `vega` tinyint(1) NOT NULL DEFAULT '0',
  `volgorde` int UNSIGNED NOT NULL DEFAULT '99'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Gegevens worden geëxporteerd voor tabel `producten`
--

INSERT INTO `producten` (`id`, `categorie_id`, `naam`, `beschrijving`, `maat`, `prijs`, `vega`, `volgorde`) VALUES
(1, 1, 'Friet Zonder', NULL, 'Klein', 2.60, 1, 1),
(2, 1, 'Friet Zonder', NULL, 'Normaal', 2.85, 1, 1),
(3, 1, 'Friet Zonder', NULL, 'Groot', 3.30, 1, 1),
(4, 1, 'Friet Zonder', NULL, 'Jumbo', 3.85, 1, 1),
(5, 1, 'Friet Met', NULL, 'Klein', 2.95, 1, 2),
(6, 1, 'Friet Met', NULL, 'Normaal', 3.20, 1, 2),
(7, 1, 'Friet Met', NULL, 'Groot', 3.75, 1, 2),
(8, 1, 'Friet Met', NULL, 'Jumbo', 4.35, 1, 2),
(9, 1, 'Friet Echte Mayo', NULL, 'Klein', 3.15, 1, 4),
(10, 1, 'Friet Echte Mayo', NULL, 'Normaal', 3.50, 1, 4),
(11, 1, 'Friet Echte Mayo', NULL, 'Groot', 4.10, 1, 4),
(12, 1, 'Friet Echte Mayo', NULL, 'Jumbo', 4.60, 1, 4),
(13, 1, 'Friet Satésaus', NULL, 'Klein', 3.50, 1, 6),
(14, 1, 'Friet Satésaus', NULL, 'Normaal', 3.85, 1, 6),
(15, 1, 'Friet Satésaus', NULL, 'Groot', 4.40, 1, 6),
(16, 1, 'Friet Satésaus', NULL, 'Jumbo', 4.85, 1, 6),
(17, 1, 'Friet Speciaal', NULL, 'Klein', 3.40, 1, 7),
(18, 1, 'Friet Speciaal', NULL, 'Normaal', 3.80, 1, 7),
(19, 1, 'Friet Speciaal', NULL, 'Groot', 4.35, 1, 7),
(20, 1, 'Friet Speciaal', NULL, 'Jumbo', 4.75, 1, 7),
(21, 1, 'Friet Zigeunersaus', NULL, 'Klein', 3.45, 1, 9),
(22, 1, 'Friet Zigeunersaus', NULL, 'Normaal', 3.85, 1, 9),
(23, 1, 'Friet Zigeunersaus', NULL, 'Groot', 4.40, 1, 9),
(24, 1, 'Friet Zigeunersaus', NULL, 'Jumbo', 4.85, 1, 9),
(25, 1, 'Friet Champignonsaus', NULL, 'Klein', 3.60, 1, 10),
(26, 1, 'Friet Champignonsaus', NULL, 'Normaal', 3.95, 1, 10),
(27, 1, 'Friet Champignonsaus', NULL, 'Groot', 4.50, 1, 10),
(28, 1, 'Friet Champignonsaus', NULL, 'Jumbo', 5.05, 1, 10),
(29, 1, 'Friet Stoofvlees', NULL, 'Klein', 3.95, 0, 12),
(30, 1, 'Friet Stoofvlees', NULL, 'Normaal', 4.55, 0, 12),
(31, 1, 'Friet Stoofvlees', NULL, 'Groot', 5.10, 0, 12),
(32, 1, 'Friet Stoofvlees', NULL, 'Jumbo', 5.60, 0, 12),
(33, 1, 'Friet Goulash', NULL, 'Klein', 4.20, 0, 13),
(34, 1, 'Friet Goulash', NULL, 'Normaal', 4.80, 0, 13),
(35, 1, 'Friet Goulash', NULL, 'Groot', 5.25, 0, 13),
(36, 1, 'Friet Goulash', NULL, 'Jumbo', 5.70, 0, 13),
(37, 1, 'Friet Joppie', NULL, 'Klein', 3.50, 1, 11),
(38, 1, 'Friet Joppie', NULL, 'Normaal', 3.85, 1, 11),
(39, 1, 'Friet Joppie', NULL, 'Groot', 4.40, 1, 11),
(40, 1, 'Friet Joppie', NULL, 'Jumbo', 4.90, 1, 11),
(41, 1, 'Friet Oorlog', NULL, 'Klein', 3.70, 1, 8),
(42, 1, 'Friet Oorlog', NULL, 'Normaal', 4.00, 1, 8),
(43, 1, 'Friet Oorlog', NULL, 'Groot', 4.55, 1, 8),
(44, 1, 'Friet Oorlog', NULL, 'Jumbo', 5.10, 1, 8),
(45, 1, 'Puntzak Met', NULL, 'Klein', 2.95, 1, 3),
(46, 1, 'Puntzak Met', NULL, 'Groot', 3.40, 1, 3),
(47, 1, 'Hete Kip', NULL, 'Groot', 6.95, 0, 14),
(48, 1, 'Friet Pangang', NULL, 'Groot', 7.10, 0, 15),
(49, 1, 'Super Speciaal', NULL, 'Groot', 5.80, 0, 16),
(50, 1, 'Kapsalon', NULL, 'Groot', 8.25, 0, 17),
(64, 2, 'Gezinszak 2 Personen', NULL, NULL, 5.00, 1, 99),
(65, 2, 'Gezinszak 3 Personen', NULL, NULL, 6.85, 1, 99),
(66, 2, 'Gezinszak 4 Personen', NULL, NULL, 8.90, 1, 99),
(67, 2, 'Gezinszak 5 Personen', NULL, NULL, 10.75, 1, 99),
(71, 3, 'Mayo / Curry', NULL, 'Klein', 1.25, 1, 99),
(72, 3, 'Mayo / Curry', NULL, 'Groot', 1.95, 1, 99),
(73, 3, 'Satesaus', NULL, 'Klein', 1.35, 1, 99),
(74, 3, 'Satesaus', NULL, 'Groot', 2.05, 1, 99),
(75, 3, 'Joppiesaus', NULL, 'Klein', 1.50, 1, 99),
(76, 3, 'Joppiesaus', NULL, 'Groot', 2.20, 1, 99),
(77, 3, 'Pangangsaus', NULL, 'Klein', 1.20, 1, 99),
(78, 3, 'Pangangsaus', NULL, 'Groot', 1.85, 1, 99),
(79, 3, 'Uitjes', NULL, 'Klein', 0.90, 1, 99),
(80, 3, 'Uitjes', NULL, 'Groot', 1.30, 1, 99),
(81, 3, 'Stoofvlees', NULL, 'Klein', 2.30, 0, 99),
(82, 3, 'Stoofvlees', NULL, 'Groot', 3.15, 0, 99),
(83, 3, 'Goulash', NULL, 'Klein', 2.40, 0, 99),
(84, 3, 'Goulash', NULL, 'Groot', 3.30, 0, 99),
(85, 3, 'Champignons', NULL, 'Klein', 1.80, 1, 99),
(86, 3, 'Champignons', NULL, 'Groot', 2.50, 1, 99),
(102, 4, 'Frikandel', NULL, NULL, 2.25, 0, 99),
(103, 4, 'Frikandel Speciaal', NULL, NULL, 2.95, 0, 99),
(104, 4, 'Frikandel XXL', NULL, NULL, 4.65, 0, 99),
(105, 4, 'Langelummel', NULL, NULL, 3.65, 0, 99),
(106, 4, 'Langelummel Speciaal', NULL, NULL, 4.20, 0, 99),
(107, 4, 'Kroket', NULL, NULL, 2.25, 0, 99),
(108, 4, 'Goulash Kroket', NULL, NULL, 2.60, 0, 99),
(109, 4, 'Dobben Kroket', NULL, NULL, 2.75, 0, 99),
(110, 4, 'Bamischijf', NULL, NULL, 2.60, 0, 99),
(111, 4, 'Twijfelaar', NULL, NULL, 4.65, 0, 99),
(112, 4, 'Nasischijf', NULL, NULL, 2.65, 0, 99),
(113, 4, 'Lihan Boutje', NULL, NULL, 2.50, 0, 99),
(114, 4, 'Mexicano', NULL, NULL, 2.90, 0, 99),
(115, 4, 'Gehaktstaaf', NULL, NULL, 2.75, 0, 99),
(116, 4, 'Ham Kaassoufle', NULL, NULL, 2.40, 0, 99),
(117, 4, 'Saterol / Smulrol', NULL, NULL, 3.10, 0, 99),
(118, 4, 'Shoarmarol', NULL, NULL, 3.10, 0, 99),
(119, 4, 'Viandel', NULL, NULL, 2.60, 0, 99),
(120, 4, 'Spicy Viandel', NULL, NULL, 2.70, 0, 99),
(121, 4, 'Zigeunerstick', NULL, NULL, 2.85, 0, 99),
(122, 4, 'Hete Donder', NULL, NULL, 2.95, 0, 99),
(123, 4, 'Berehap', NULL, NULL, 2.85, 0, 99),
(124, 4, 'Grizly', NULL, NULL, 3.10, 0, 99),
(125, 4, 'Kalfskroket', NULL, NULL, 2.50, 0, 99),
(126, 4, 'Ragoezie', NULL, NULL, 2.75, 0, 99),
(127, 4, 'Gehaktbal', NULL, NULL, 2.60, 0, 99),
(128, 4, 'Vlampijp', NULL, NULL, 2.85, 0, 99),
(129, 4, 'Bamioriental', NULL, NULL, 2.70, 0, 99),
(130, 4, 'Ribster', NULL, NULL, 2.70, 0, 99),
(131, 4, 'Hotwings', NULL, NULL, 4.90, 0, 99),
(132, 4, 'Chicken Wings', NULL, NULL, 4.90, 0, 99),
(133, 4, 'Lucifer', NULL, NULL, 2.95, 0, 99),
(134, 4, 'Portie Hapjes (8 st.)', NULL, NULL, 3.50, 0, 99),
(135, 4, 'Vlammetjes (6 st.)', NULL, NULL, 3.40, 0, 99),
(136, 4, 'Knakworst', NULL, NULL, 2.80, 0, 99),
(137, 4, 'Visstick', NULL, NULL, 2.75, 0, 99),
(138, 4, 'Loempia', NULL, NULL, 3.65, 0, 99),
(139, 4, 'Bitterballen (6 st.)', NULL, NULL, 2.70, 0, 99),
(140, 4, 'Bourgondische Kroket', NULL, NULL, 2.95, 0, 99),
(141, 4, 'Braadworst', NULL, NULL, 2.75, 0, 99),
(142, 4, 'Speksnek', NULL, NULL, 3.25, 0, 99),
(143, 4, 'Kipsaté', NULL, NULL, 5.20, 0, 99),
(144, 4, 'Schnitzel', NULL, NULL, 5.30, 0, 99),
(145, 4, 'Halvehaan', NULL, NULL, 6.80, 0, 99),
(146, 4, 'Shaslick', NULL, NULL, 3.50, 0, 99),
(147, 4, 'Satekroket', NULL, NULL, 2.50, 0, 99),
(148, 4, 'Sitostick', NULL, NULL, 3.10, 0, 99),
(149, 4, 'Kipcorns', NULL, NULL, 2.75, 0, 99),
(150, 4, 'Kipnuggets / Kipvingers', NULL, NULL, 3.10, 0, 99),
(151, 4, 'Uienringen', NULL, NULL, 2.55, 0, 99),
(152, 4, 'Kipburger', NULL, NULL, 3.30, 0, 99),
(153, 4, 'Loempidel', NULL, NULL, 2.95, 0, 99),
(165, 5, 'Mini Loempia\'s (8 st.)', NULL, NULL, 2.70, 1, 99),
(166, 5, 'Vietnamse Loempia\'s', NULL, NULL, 1.95, 1, 99),
(167, 5, 'Souflesse', NULL, NULL, 2.40, 1, 99),
(168, 5, 'Souflesse Mozzarella', NULL, NULL, 2.50, 1, 99),
(169, 5, 'Bamiblok', NULL, NULL, 2.60, 1, 99),
(170, 5, 'Groente Kroket', NULL, NULL, 2.40, 1, 99),
(171, 5, 'Cheese Crack', NULL, NULL, 2.50, 1, 99),
(172, 5, 'Bonita', NULL, NULL, 2.45, 1, 99),
(180, 6, 'Hamburger', NULL, NULL, 4.50, 0, 99),
(181, 6, 'Cheeseburger', NULL, NULL, 5.00, 0, 99),
(182, 6, 'Kipburger', NULL, NULL, 4.50, 0, 99),
(183, 6, 'Joppieburger', NULL, NULL, 4.50, 0, 99),
(184, 6, 'Hawaïburger', NULL, NULL, 4.70, 0, 99),
(185, 6, 'Cornerburger', NULL, NULL, 5.50, 0, 99),
(186, 6, 'Baconburger', NULL, NULL, 5.00, 0, 99),
(187, 6, 'Broodje Kroket', NULL, NULL, 2.85, 0, 99),
(188, 6, 'Broodje Frikandel', NULL, NULL, 2.85, 0, 99),
(189, 6, 'Broodje Ham / Kaas', NULL, NULL, 2.20, 0, 99),
(190, 6, 'Pistolets Ham / Kaas', NULL, NULL, 2.50, 0, 99),
(191, 6, 'Pistolets Gezond', NULL, NULL, 4.25, 0, 99),
(192, 6, 'Pistolets Hetenkip', NULL, NULL, 5.95, 0, 99),
(193, 6, 'Pistolets Beenham', NULL, NULL, 4.95, 0, 99),
(194, 6, 'Pistolets Mexicano', NULL, NULL, 4.85, 0, 99),
(195, 6, 'Kippito', NULL, NULL, 3.95, 0, 99),
(196, 6, 'Slaatje', NULL, NULL, 2.20, 1, 99),
(197, 6, 'Rauwkost', NULL, NULL, 1.95, 1, 99),
(211, 7, 'Menu Pangang / Halve Haan', NULL, NULL, 11.00, 0, 99),
(212, 7, 'Menu Wiener Schnitzel', NULL, NULL, 9.75, 0, 99),
(213, 7, 'Menu Zigeuner / Champignons Schnitzel', NULL, NULL, 10.75, 0, 99),
(214, 7, 'Menu Nasi / Bami / Hete Kip', NULL, NULL, 10.25, 0, 99),
(215, 7, 'Menu Sate Spies', NULL, NULL, 10.45, 0, 99),
(216, 7, 'Kip Shoarma Menu', NULL, NULL, 9.75, 0, 99),
(217, 7, 'Kinder Smulbox', NULL, NULL, 5.95, 0, 99),
(218, 8, 'Blikje', NULL, NULL, 2.60, 1, 99),
(219, 8, 'Koffie', NULL, NULL, 2.00, 1, 99),
(220, 8, 'Warme Chocomelk', NULL, NULL, 2.50, 1, 99),
(221, 8, 'Milkshake', NULL, NULL, 2.75, 1, 99),
(222, 8, 'Cappuccino', NULL, NULL, 2.20, 1, 99),
(223, 8, 'Latte Macchiato', NULL, NULL, 2.50, 1, 99);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `categorieen`
--
ALTER TABLE `categorieen`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `producten`
--
ALTER TABLE `producten`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_categorie` (`categorie_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `categorieen`
--
ALTER TABLE `categorieen`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT voor een tabel `producten`
--
ALTER TABLE `producten`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `producten`
--
ALTER TABLE `producten`
  ADD CONSTRAINT `fk_categorie` FOREIGN KEY (`categorie_id`) REFERENCES `categorieen` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

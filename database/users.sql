-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql100.infinityfree.com
-- Generation Time: Oct 07, 2025 at 01:47 AM
-- Server version: 10.6.22-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39682404_kinglang_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contact_number` varchar(16) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Client','Admin','Super Admin') NOT NULL DEFAULT 'Client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expiry` datetime DEFAULT NULL,
  `company_name` varchar(50) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `contact_number`, `password`, `role`, `created_at`, `reset_token`, `reset_expiry`, `company_name`, `deleted_at`, `google_id`, `profile_picture`) VALUES
(33, 'Juan', 'Dela Cruz', 'juan@gmail.com', '+63 989 234 8239', '$2y$10$MqrG8RiJJtoBp7mlYZ.IiuhjHlS4ZcetHHdcq2IUhAH1qOpVzbXTe', 'Client', '2025-05-05 22:48:41', NULL, NULL, '', NULL, NULL, NULL),
(34, 'Benjamin', 'Millamina', 'bsmillamina@yahoo.com', '+63 933 862 4323', '$2y$10$ZMkj1zcwNm1/yviz320aZuH5.QNxXWNiSj9yv6NtlJtQRPa6AAksu', 'Super Admin', '2025-05-05 22:50:27', NULL, NULL, '', NULL, NULL, NULL),
(35, 'Maria Angelica', 'Reyes', 'angelica.reyes@yahoo.com', '+63 928 598 4353', '$2y$10$XCsNkjb9OW9mb2yBNSjD1e8T8.0.vrwjWqLSprj5c/TDx5xNXSSe6', 'Client', '2025-05-05 22:57:31', NULL, NULL, '', NULL, NULL, NULL),
(36, 'John Carlo', 'Mendoza', 'jc.mendoza88@gmail.com', '+63 948 534 7578', '$2y$10$pHRT2Eu8XwOhg2E.bkrDPeFdQ1UNsGCyz2rghrrjhC0hb1S7rBzty', 'Client', '2025-05-05 22:58:23', NULL, NULL, '', NULL, NULL, NULL),
(37, 'Kritine Joy', 'Santos', 'kristinejoysantos@yahoo.com', '+63 934 578 5102', '$2y$10$z/QL1jLj0wOowQ5WpChJf.pTtl1RKc0FM/1BERHZGC9BMkDySyarq', 'Client', '2025-05-05 22:59:27', NULL, NULL, '', NULL, NULL, NULL),
(38, 'James Benedict', 'Ramirez', 'james.ramirez21@gmail.com', '+63 984 395 4758', '$2y$10$/JAaNSHEVsIe8hUjuSsnUe6nwk6gxYWBTJrtXbESpUV1Rgy3OwcFC', 'Client', '2025-05-05 23:00:56', NULL, NULL, 'Lakbay Aral', NULL, NULL, NULL),
(39, 'Angel Mae', 'Torres', 'angelmae.torres@yahoo.com', '+63 938 439 2353', '$2y$10$w.osaEHEflr1yDQtKQM0ju4jylhaSUGqI2GCQToyvMk2BNoL4fyEm', 'Client', '2025-05-05 23:02:49', NULL, NULL, '', NULL, NULL, NULL),
(40, 'Rafael Lorenzo', 'Garcia', 'rafael.l.garcia.ph@gmail.com', '+63 984 387 5498', '$2y$10$3Pv2VZff8Z1QbrmyyhfVueFMhXxw3bWSpfbrJuKq3I6wAy8CVNlCC', 'Client', '2025-05-05 23:05:39', NULL, NULL, '', NULL, NULL, NULL),
(41, 'Shaira Nicole', 'Villanueva', 'shaira.villanueva@yahoo.com', '+63 984 932 5745', '$2y$10$NbwwfH0lQ1qpe6Bqi7lKgugcyYjAVGybsFjRVgdzC3GOYxbL4xJS6', 'Client', '2025-05-05 23:06:13', NULL, NULL, '', NULL, NULL, NULL),
(42, 'Miguel Andres', 'Bautista', 'miguel.bautista1999@gmail.com', '+63 985 394 7682', '$2y$10$SRZ32eDEWvPDggtOUvUlku43QCF/0CQHRRAiBlsHfvEPFuhdRyV0q', 'Client', '2025-05-05 23:07:10', NULL, NULL, '', NULL, NULL, NULL),
(43, 'Clarisse Anne', 'Mercado', 'clarisse.mercado@gmail.com', '+63 948 534 9588', '$2y$10$TbKwu8EE8bowk1fnZLnM4e2ltarjo/KyzwBzUzCYiSxVldBLqf3K6', 'Client', '2025-05-05 23:08:14', NULL, NULL, '', NULL, NULL, NULL),
(44, 'Rolando', 'Balucan', 'rolando.balucan@gmail.com', '+63 958 938 5204', '$2y$10$rncjAknkLdmDX8a9K7/pE.2lnWBBddeSGU5ZfZ.cDgu08v6frXiZG', 'Admin', '2025-05-05 23:09:27', NULL, NULL, '', NULL, NULL, NULL),
(45, 'Jeric Ken', 'Verano', 'vjericken@gmail.com', '+63 934 939 8939', '$2y$10$.YfIOrWamntOTxkwvN.4tuAJiOsBrJ6hzLeRvDQgkhr44Pt2i1c7K', 'Client', '2025-05-13 08:09:44', 'a7aa59d8ab0336046018592ff585a3ea', '2025-09-26 19:07:02', '', NULL, 'google_aea9c3941018c5832c5b1b489755a8b3', 'https://lh3.googleusercontent.com/a/ACg8ocLhAllHuhkfvqouwuJSNO8iH4TrZpBWfxzwicxup1Ot1nQ0zMcn=s96-c'),
(46, 'John', 'Doe', 'john@gmail.com', '+63 958 389 5838', '$2y$10$G4G1K9gif16REUKQXBGfBOWwgLKSV.4G/nat8DeACUX08evE00Chm', 'Client', '2025-05-20 10:23:23', NULL, NULL, '', NULL, NULL, NULL),
(47, 'Maria', 'Gomez', 'maria@gmail.com', '+63 948 384 5738', '$2y$10$1j8Rf.jNXEbkiS9YXdXwbu8wof5NDTe.DlJX7MRthakQ/aEUk9q/a', 'Client', '2025-05-20 10:28:15', NULL, NULL, '', NULL, NULL, NULL),
(48, 'Kenichi', 'Shirahama', 'kenichishirahama369@gmail.com', '+63 948 934 2395', '$2y$10$suuxRs6kbchh0hPcfmAbdu2KlGLkQs39UaYW7TXxEFc9pFI.4qzga', 'Client', '2025-06-12 03:32:54', NULL, NULL, '', NULL, 'google_4efe411d967f9e09afe6f22a4cfcfd7d', 'https://lh3.googleusercontent.com/a/ACg8ocLp0ZVbeLKuZwIIk3Bbv96IzDz7DVxK389mwHnf3NTz_XQxp-8=s96-c'),
(53, 'Shogo', 'Kai', 'shogokai31@gmail.com', '+63 912 230 9590', '$2y$10$pRWlY5aBxPpgVGN2n/Eo4u.7RQvyqFMe7mnUT3pdV2gLkM7CIAiti', 'Client', '2025-06-21 01:31:42', NULL, NULL, '', NULL, 'google_67ae42bba6334595613849ecf0208b85', 'https://lh3.googleusercontent.com/a/ACg8ocJKUtfuMewezQJO-ykEysv3kdfQ6_PRDaMLqr3Je2p59wX_s-17=s96-c'),
(54, 'Jervis', 'Verano', 'verano.136535121003@depedqc.ph', '+63 943 903 4923', '$2y$10$o3ghjM0nPAROlaegjeS4n.RwOIzkMLh.r40yt38fWSw9MzXqY67im', 'Client', '2025-06-26 15:47:04', NULL, NULL, 'Lakbay Aral', NULL, 'google_4e1b4142a5267e5c3eeef8ef5b873247', 'https://lh3.googleusercontent.com/a/ACg8ocIy0kxuEhHNhcLc6s7fImvo1DbCC2Phkj1Mx9yh7M8o2mMpj0ZK=s96-c'),
(55, 'Tom', 'Sawyer', 'tomsawyerhuckfinn246@gmail.com', '+63 393 283 8483', '$2y$10$6tb5qsyjxnhenyhoGdqgre..BF25d92ynpE.4q8J6HcitlgALNCJ.', 'Client', '2025-07-15 08:21:12', NULL, NULL, '', NULL, 'google_209c1e438e5a0089cfcab26da910bc86', 'https://lh3.googleusercontent.com/a/ACg8ocLY5A4q944nXsoErvAT5Qnd6k5haodTxbkJt8qYmJ8TMbnMTw=s96-c'),
(56, 'test', 'test', 'test@gmail.com', '+63 997 869 7896', '$2y$10$4RSYgSucLlGGJWfAN4zktuc7kMankzCe7RMXxvKMtPlU8eGDQs.eW', 'Admin', '2025-07-15 08:54:18', NULL, NULL, '', NULL, NULL, NULL),
(59, 'Jericken', 'Verano', 'jerickenverano@gmail.com', '+63 943 589 3458', '$2y$10$I7S2jhhcqzlTzooqQVoJU.hi9tGO4VbvzjKeZbYk4h0CwLpv2a6.S', 'Client', '2025-07-18 13:21:17', NULL, NULL, '', NULL, 'google_870c37f9e68fc623cec11c50aaca5c40', 'https://lh3.googleusercontent.com/a/ACg8ocKZWBbsfZHOB2Gnc27ZpAcH9PgVsCImi_cyFWxUfW3gefNO6i1d=s96-c'),
(60, 'Jervis', 'Verano', 'jervisverano004@gmail.com', '+63 394 392 4928', '$2y$10$SGR8161gTFtKURy1Dom96e/1Y/onO5fB2YlUYqfw7WzRWL.87vRnG', 'Client', '2025-08-04 06:04:01', '31dc82d5453e07509e781d55ee7ab966', '2025-08-30 04:15:37', '', NULL, 'google_0778a9c607a434cba4ae832fe10bde19', 'https://lh3.googleusercontent.com/a/ACg8ocJNKhdxj375KTUBK4NbFT85yKDYMyPNfyjXD_wyYoVWs9MZsK_P=s96-c'),
(61, 'testing', 'tests', 'testing@gmail.com', '+63 393 458 3945', '$2y$10$5vQKw8lh2riqSMEFUiXEquBaEWB8QV66zRyHNLk5j79LCw5FP6x8a', 'Client', '2025-08-22 11:42:38', NULL, NULL, '', NULL, NULL, NULL),
(62, 'kirito', 'kirigaya', 'erenxyz0123@gmail.com', '+63 396 269 4275', '$2y$10$vkf.SLEQYP9.8doogD7NSu0/49KrPmZlg4i5rBDbgsW7WVTuvGcCC', 'Client', '2025-08-22 13:09:58', NULL, NULL, '', NULL, NULL, NULL),
(63, 'Jervis', 'Verano', 'jervisverano138@gmail.com', NULL, '$2y$10$xaionOmb.3g9ZyDiF5aYDOSb1ncyvY29yO1og3bypgf/cbDvv5c8a', 'Client', '2025-08-22 15:06:00', NULL, NULL, NULL, NULL, 'google_6b0ee68f44a45b33f812eeb7be9857a6', 'https://lh3.googleusercontent.com/a/ACg8ocIKKW8_dm1CRe3XlBYRIqhP5V-4_jRQC6lvyvf41jtw4MePgnva=s96-c'),
(64, 'paeng', 'balucan', 'rapbalucan@gmail.com', '+63 339 123 4567', '$2y$10$/nLp.T2wdxfMhss2Ua10I.T0jAgZckrZH7uBxw7MQqCRvHtqIp7p.', 'Client', '2025-08-22 21:23:40', NULL, NULL, '', NULL, 'google_ef046e1568d53fb52b86bfb0d3e27956', 'https://lh3.googleusercontent.com/a/ACg8ocKHaoNUnFOI2H8yaHR1iFIP8tPoUSEfLpMPUszxRQSshdlw_Mnt=s96-c'),
(65, 'Alexandra', 'Gonzales', 'xixitenshi03@gmail.com', '', '$2y$10$GaovewCRiqjlpN.zf2RHRefvknkrv8T9MUrom/bOKhMKH6sv2Eg5.', 'Client', '2025-08-27 10:21:52', NULL, NULL, '', NULL, 'google_c645eb245a64d2639cbeecbd64f27b6a', 'https://lh3.googleusercontent.com/a/ACg8ocKvZRzHz2rHAvoVM6LKXdnOlHxUjYyOPZn4mp5AgwhLSPy5OWg=s96-c'),
(66, 'test', 'test', 'agullon@gmail.com', '+63 394 585 9485', '$2y$10$D5n1fYIVrXoffsoKo6w3q.10fbZk2oNxunigBgbTcx1R3CXa0.Vam', 'Client', '2025-08-28 02:15:12', NULL, NULL, '', NULL, NULL, NULL),
(67, 'Mark', 'Duran', 'markduran225@gmail.com', '+63 391 236 7121', '$2y$10$HTQJc2ygSRJfmxVy4ivmCONMjNZzgYNaObwDPzl5KP93H3gPXMSd2', 'Client', '2025-08-28 02:16:37', NULL, NULL, '', NULL, NULL, NULL),
(68, 'thesis', '101', 'thesis.def.storage@gmail.com', NULL, '$2y$10$W1fAS/tFFHC9/mMrjjoYpe8FSTxKufH4WV5A6sIN1aldWGIld9D6m', 'Client', '2025-08-28 02:21:33', NULL, NULL, NULL, NULL, 'google_647edb77b969e879565a3ab50e4fbac5', 'https://lh3.googleusercontent.com/a/ACg8ocJy-3uK3ffapH_Ya24iPpYNYR0VrVz9dT3KZRbrd_8bxDbddQ=s96-c'),
(69, 'Yapcine', '', 'web3dave.eth.sol@gmail.com', NULL, '$2y$10$T8GzGs8EH6ah/XlSq.8lVO.uHtky3LTIz2eW8bdcjeEXtAN35cj3a', 'Client', '2025-09-04 03:56:53', NULL, NULL, NULL, NULL, 'google_510e9b40edaec14886a3fd0a50e4cb2b', 'https://lh3.googleusercontent.com/a/ACg8ocLF5QHRCOR8JuM-yEttdQbAOW7gDPl8mLv0a0JPvaI9IuqmSgk=s96-c'),
(70, 'test', 'test', 'tests@gmail.com', '+63 392 589 3453', '$2y$10$fvTjEx65fhdN0.uEtqRpoOmHHuy/LYbvnRG86rIjK6LUWqUp5Bcma', 'Client', '2025-09-24 17:35:55', NULL, NULL, '', NULL, NULL, NULL),
(75, 'Lester', 'Sakuragi', 'miligod2211@gmail.com', '+63 931 231 3214', '$2y$10$KeNXta7mp5TA/CycuwbI8O9oasuBSpU7JYeJe7yCLSuQZJyKIVUOa', 'Client', '2025-09-25 04:33:25', NULL, NULL, '', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `contact_number` (`contact_number`),
  ADD KEY `google_id_index` (`google_id`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

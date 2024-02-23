DROP TABLE IF EXISTS `mythicaldash_linkvertise`;
CREATE TABLE `mythicaldash_linkvertise` (
  `id` int(11) NOT NULL,
  `skey` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `mythicaldash_linkvertise`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mythicaldash_linkvertise`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
COMMIT;

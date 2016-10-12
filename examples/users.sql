CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `login` varchar(30) NOT NULL,
  `password` char(64) NOT NULL,
  `session_key` char(32) DEFAULT NULL,
  `group` tinyint(4) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `mail` varchar(30) DEFAULT NULL
) DEFAULT CHARSET=utf8;

ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT;

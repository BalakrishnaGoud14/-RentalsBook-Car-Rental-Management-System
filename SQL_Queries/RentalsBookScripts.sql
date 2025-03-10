
--MYSQL Scripts

CREATE TABLE `carbookings` (
  `BookingID` int(11) NOT NULL AUTO_INCREMENT,
  `CarID` int(11) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `FromDate` date NOT NULL,
  `ToDate` date NOT NULL,
  `USERID` int(11) NOT NULL,
  PRIMARY KEY (`BookingID`),
  KEY `carbookings_userfk_idx` (`USERID`),
  KEY `carbookings_ibfk_1` (`CarID`),
  CONSTRAINT `carbookings_ibfk_1` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carbookings_userfk` FOREIGN KEY (`USERID`) REFERENCES `users` (`userid`) ON DELETE NO ACTION ON UPDATE NO ACTION
) 
INSERT INTO `carbookings` VALUES (5,2,18.00,'2024-11-26','2024-11-30',1);

CREATE TABLE `carinfo` (
  `CarID` int(11) NOT NULL,
  `CarStatus` varchar(45) NOT NULL,
  PRIMARY KEY (`CarID`),
  CONSTRAINT `fk_carIDStatus` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`) ON DELETE CASCADE ON UPDATE CASCADE
) 
INSERT INTO `carinfo` VALUES (1,'Good'),(2,'Good'),(3,'Good'),(4,'Good');

CREATE TABLE `carratings` (
  `CarID` int(11) NOT NULL,
  `CarRating` varchar(45) NOT NULL,
  PRIMARY KEY (`CarID`),
  CONSTRAINT `fk_carratings` FOREIGN KEY (`CarID`) REFERENCES `cars` (`CarID`) ON DELETE CASCADE ON UPDATE CASCADE
) 
INSERT INTO `carratings` VALUES (1,'3.5'),(2,'4.5'),(3,'4.8'),(4,'4.5');

CREATE TABLE `cars` (
  `CarID` int(11) NOT NULL,
  `CarModelName` varchar(45) NOT NULL,
  `CarSeaterCapacity` varchar(45) NOT NULL,
  `Price` int(11) NOT NULL,
  `LocationID` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `FromDate` date DEFAULT NULL,
  `ToDate` date DEFAULT NULL,
  PRIMARY KEY (`CarID`),
  KEY `fk_locationID_idx` (`LocationID`),
  CONSTRAINT `fk_locationID` FOREIGN KEY (`LocationID`) REFERENCES `locationinfo` (`LocationID`) ON DELETE CASCADE ON UPDATE CASCADE
)
INSERT INTO `cars` VALUES (1,'ToyotaCorolla','4',14,1,'ToyotaCorolla.jpg','2024-11-12','2024-12-30'),(2,'Hyundai Santa Fe','7',18,2,'HyundaiSanta.jpg','2024-11-26','2024-11-30'),(3,'Ford Expedition','8',22,4,'FordExpedition.jpg','2024-11-12','2024-12-30'),(4,'Genesis GV80','4',16,1,'GenesisGV80.jpg','2024-12-12','2024-12-23');

CREATE TABLE `locationinfo` (
  `LocationID` int(11) NOT NULL,
  `LocationName` varchar(45) NOT NULL,
  `PhoneNumber` varchar(45) NOT NULL,
  `ManagerName` varchar(45) NOT NULL,
  `Address` varchar(45) NOT NULL,
  PRIMARY KEY (`LocationID`)
) 


INSERT INTO `locationinfo` VALUES (1,'New York','789-456-6789','Marlon Samuel','3rd Street New York'),(2,'Chicago','458 678 9787','Aaron Smith','8302 32th Street New York'),(3,'Los Angeles','989-466-6749','Jos Stokes','6753 9th Street LA'),(4,'Dallas','789-466-6749','Steve Root','2753 36th Street Dallas');





CREATE TABLE `users` (
  `full_name` varchar(200) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(200) NOT NULL,
  `dob` date NOT NULL,
  `userid` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`userid`)
)


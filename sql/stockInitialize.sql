-- Execute this before running StockLoader.loadStockInfo();
-- The table can be modified to include more information from IEX

DROP DATABASE IF EXISTS StockApp;
CREATE DATABASE StockApp;
USE StockApp;
CREATE TABLE Stocks (
	stockId INT(11) PRIMARY KEY AUTO_INCREMENT,
	companyName VARCHAR(500) NULL,
    symbol VARCHAR(10) NOT NULL,
    companyDescription VARCHAR(9999) NULL
);
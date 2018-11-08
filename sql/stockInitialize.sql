-- Execute this before running StockLoader.loadStockInfo();
-- The table can be modified to include more information from IEX

DROP DATABASE IF EXISTS StockApp;
CREATE DATABASE StockApp;
USE StockApp;
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root';
CREATE TABLE Stocks (
	stockId INT(11) PRIMARY KEY AUTO_INCREMENT,
	companyName VARCHAR(500) NULL,
    symbol VARCHAR(10) NOT NULL,
    companyDescription VARCHAR(9999) NULL, 
    CEO VARCHAR(100) NULL,
    industry VARCHAR(100) NULL,
    market  VARCHAR(100) NULL
);
CREATE TABLE Users (
	u_Id INT(11) PRIMARY KEY AUTO_INCREMENT,
    u_username VARCHAR(20) NOT NULL,
    u_password VARCHAR(255) NOT NULL,
    u_balance INT(20) NOT NULL
);
CREATE TABLE Investments (
	i_Id INT(11) PRIMARY KEY AUTO_INCREMENT,
    i_userId INT(11) NOT NULL,
    i_stockId INT(11) NOT NULL,
    i_amount INT(11) NOT NULL,
    FOREIGN KEY (i_userId) REFERENCES Users(u_Id),
    FOREIGN KEY (i_stockId) REFERENCES Stocks(stockId)
);
CREATE TABLE Rankings (
	r_ranking INT(11) PRIMARY KEY,
    r_userId INT(11) NOT NULL,
    r_money INT(20) NOT NULL
);
    
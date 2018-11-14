package finalproject;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class TestCode {
	public TestCode()
	{
		
	}
	
	// Checks the length of the username
	// Used for Login page tests, Register page tests
	public Boolean checkUsername(String username) 
	{
		if (username.length() < 8 || username.length() > 20)
		{
			return false; 
		}
		return true; 
	}
	
	// Checks the length and character requirements for password
	// Used for Login Page tests, Register page tests
	public Boolean checkPassword(String password)
	{
		if (password.length() < 8 || password.length() > 20)
		{
			System.out.println("bad password1");
			return false;
		}
		
		// make sure password has at least 1 uppercase letter and
		// one special character using regex
		Pattern regex = Pattern.compile("^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,20}$");
		Matcher matcher = regex.matcher(password);
		while (matcher.find())
		{
			System.out.println("good password");
			return true;
		}
		
		System.out.println("bad password2");
		return false; 
		
	}
	
	// Verify that user exists in the database
	// Used for Login page tests to make sure that the user gives valid login info
	// Used to check that Validate class validateUser() function returns correct result
	// Used to check that LoginServlet validate() function returns correct result
	public Boolean CheckValidateUser(String username, String password)
	{
		Connection conn = null; 
		Statement st = null; 
		ResultSet rs = null; 
		Boolean found = true; 
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/StockAppDatabase?user=root&password=root&useSSL=false");
			
			// Search for word1 substring in the fname column
			st = conn.createStatement();
			rs = st.executeQuery("Select * FROM Users where username='" + username + "' AND password='" + password + "'");
			
			while (rs.next()) { // iterates from first to last row
				found = true; 
			}
		} catch (SQLException sqle) {
			System.out.println("sqle: " + sqle.getMessage());
		} catch (ClassNotFoundException cnfe) {
			System.out.println("cnfe: " + cnfe.getMessage());
		} finally {
			// close files and sockets here in the finally block
			try {
				if (rs != null) {
				 	rs.close();
				 }
				if (st != null) {
					st.close(); 
				}
				if (conn != null) {
					conn.close(); 
				}
			} catch (SQLException sqle) {
				System.out.println("sqle closing streams " + sqle.getMessage());
			}
		}
		
		Validation validationClass = new Validation();
		Boolean vcResult = validationClass.validateUser(username, password);
		
		if (vcResult == found)
			return true;
		return false; 
	}
	
	// Used for Register page tests to make sure that a user with this username
			// does not already exist in the database and that the password and
			// confirmPassword are exact matches
	// User should only be entered into the data base or allowed to proceed in
			// creating an account when this returns true
	// Use to check that RegisterServlet validate() function returns correct value
	public Boolean checkRegisterInfo(String username, String password, String confirmPassword)
	{
		Boolean canRegister = true; 
		
		// See if someone with this username already exists in database
		Connection conn = null; 
		Statement st = null; 
		ResultSet rs = null;  
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/StockAppDatabase?user=root&password=root&useSSL=false");
			
			// Search for word1 substring in the fname column
			st = conn.createStatement();
			rs = st.executeQuery("Select * FROM Users where username='" + username + "'");
			
			while (rs.next()) { // iterates from first to last row
				canRegister = false; 
			}
		} catch (SQLException sqle) {
			System.out.println("sqle: " + sqle.getMessage());
		} catch (ClassNotFoundException cnfe) {
			System.out.println("cnfe: " + cnfe.getMessage());
		} finally {
			// close files and sockets here in the finally block
			try {
				if (rs != null) {
				 	rs.close();
				 }
				if (st != null) {
					st.close(); 
				}
				if (conn != null) {
					conn.close(); 
				}
			} catch (SQLException sqle) {
				System.out.println("sqle closing streams " + sqle.getMessage());
			}
		}
		
		Boolean goodUsername = checkUsername(username);
		if (!goodUsername)
		{
			canRegister = false; 
		}
		
		Boolean goodPassword = checkPassword(password);
		if (!goodPassword)
		{
			canRegister = false;
		}
		
		if (!password.equals(confirmPassword))
		{
			canRegister = false; 
		}
		
		return canRegister; 
	}
	
	// Used on the Home page to see if new stocks can be added
	public Boolean controlNumStocksOnGraph(int num)
	{
		Boolean canAddNewStock = false;
		if (num >= 0 && num < 5)
		{
			canAddNewStock = true;
		}
		return canAddNewStock; 
	}
	
	// Used on the home page to see if hte user is searching for a valid stock symbol
	public Boolean IsValidStockQuery(String symbol)
	{
		Boolean validStockSymbol = false;
		Connection conn = null; 
		Statement st = null; 
		ResultSet rs = null;  
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/StockAppDatabase?user=root&password=root&useSSL=false");
			
			// Search for word1 substring in the fname column
			st = conn.createStatement();
			rs = st.executeQuery("Select * FROM Stocks where symbol='" + symbol + "'");
			
			while (rs.next()) { // iterates from first to last row
				validStockSymbol = true; 
			}
		} catch (SQLException sqle) {
			System.out.println("sqle: " + sqle.getMessage());
		} catch (ClassNotFoundException cnfe) {
			System.out.println("cnfe: " + cnfe.getMessage());
		} finally {
			// close files and sockets here in the finally block
			try {
				if (rs != null) {
				 	rs.close();
				 }
				if (st != null) {
					st.close(); 
				}
				if (conn != null) {
					conn.close(); 
				}
			} catch (SQLException sqle) {
				System.out.println("sqle closing streams " + sqle.getMessage());
			}
		}
		
		return validStockSymbol; 
	}
	
	// Checks the result of validateUsernameChange() on User Profile page
	public Boolean verifyChangeUsername(String prevUsername, String newUsername)
	{
		Boolean canChangeUsername = true; 
		
		// See if someone with this username already exists in database
		Connection conn = null; 
		Statement st = null; 
		ResultSet rs = null;  
		
		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager.getConnection("jdbc:mysql://localhost:3306/StockAppDatabase?user=root&password=root&useSSL=false");
			
			// Search for word1 substring in the fname column
			st = conn.createStatement();
			rs = st.executeQuery("Select * FROM Users where username='" + newUsername + "'");
			
			while (rs.next()) { // iterates from first to last row
				canChangeUsername = false; 
			}
		} catch (SQLException sqle) {
			System.out.println("sqle: " + sqle.getMessage());
		} catch (ClassNotFoundException cnfe) {
			System.out.println("cnfe: " + cnfe.getMessage());
		} finally {
			// close files and sockets here in the finally block
			try {
				if (rs != null) {
				 	rs.close();
				 }
				if (st != null) {
					st.close(); 
				}
				if (conn != null) {
					conn.close(); 
				}
			} catch (SQLException sqle) {
				System.out.println("sqle closing streams " + sqle.getMessage());
			}
		}
		
		Boolean goodUsername = checkUsername(newUsername);
		if (!goodUsername)
		{
			canChangeUsername = false; 
		}
		
		Validation vc = new Validation();
		Boolean vcResult = vc.validateUsernameChange(); 
		
		if (vcResult == canChangeUsername)
			return true;
		return false;
	}
	
	// Checks the result of validatePasswordChange() on User Profile page
	public Boolean validateChangePassword(String newPassword, String confirmPassword)
	{
		Boolean canChangePassword = false; 
		
		Boolean goodPassword = checkPassword(newPassword);
		if (!goodPassword)
		{
			canChangePassword = false;
		}
		
		if (!newPassword.equals(confirmPassword))
		{
			canChangePassword = false; 
		}
		
		Validation vc = new Validation();
		Boolean vcResult = vc.validateUsernameChange();
		if (vc == canChangePassword)
			return true;
		return false; 
	}
	
	public Boolean checkGetUsername(User user, String username)
	{
		return user.getUsername().equals(username);
	}
	
	public Boolean checkSetUsername(User user, String username)
	{
		user.setUsername(username);
		return username.equals(user.getUsername());
	}
	
	public Boolean checkSetPassword(User user, String password)
	{
		return user.getPassword().equals(password);
	}
	
	public Boolean checkSetPassword(User user, String password)
	{
		user.setPassword(password);
		return username.equals(user.getPassword());
	}
	
	public Boolean checkCalcProfit(int numberOfStocks, double priceOfStock)
	{
		double calculatedProfit = 0.0;
		
		calculatedProfit = numberOfStocks * priceOfStock; 
		
		Calculation cc = new Calculation();
		double ccResult = cc.calculateProfit(numberOfStocks, priceOfStock);
		return calculatedProfit == ccResult;
	}
	
	public Boolean checkSetProfit(Bank bank, double newProfit)
	{
		bank.setProfit(newProfit);
		return bank.getProfit() == newProfit; 
	}
	
	public Boolean checkSetBalance(Bank bank, double newBalance)
	{
		bank.setBalance(newBalance);
		return bank.getBalance() == newBalance; 
	}
	
	public Boolean checkBuyResult(User user, String symbol, int numberOfStocks, double priceOfStock)
	{
		double prevBal = user.getBalance();
		double prevProfit = user.getProfit(); 
		user.setBalance(user.getBalance() - (numberOfStocks * priceOfStock));
		user.setProfit(user.getProfit() + (numberOfStocks * priceOfStock));
		
		if (user.getBalance() == (prevBal - (numberOfStocks * priceOfStock) && user.getProfit() == (prevProfit + (numberOfStocks * priceOfStock)))
			return true;
		else
			return false;
		// confirm database
	}
	
	public static void main(String [] args)
	{
		
	}
}

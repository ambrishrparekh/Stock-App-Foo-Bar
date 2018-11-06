import java.io.IOException;
import java.io.InputStream;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.Scanner;
import java.util.Vector;

import org.apache.commons.text.StringEscapeUtils;

import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
/* 
 * Build dependencies:
 * 		gson-2.6.2.jar
 * 		mysql-connector-javva-5.1.46.jar
 * 		commons-text-1.6.jar
 * 		commons-lang3-3.1.jar
 * 
 * Has static function loadStockInfo() that
 * loads all stock symbols and information about each symbol into the database.
 * Please check the global variables before running.
 * -----------------------------------------------------
 * This version loads the following information:
 * 		{symbol, companyName, description}
 */
public class StockLoader {
	/*
	 * DB: MySQL server address
	 * USER, PASSWORD: username and password for the database server
	 */
	private static final String DB = "mysql://localhost:3306/stockapp";
	private static final String USER = "root";
	private static final String PASSWORD = "root";

	public static void main(String[] args) {
		loadStockInfo();
	}
	
	public static void loadStockInfo() {
		Connection conn = null;
		Statement st = null;
		ResultSet rs = null;

		try {
			Class.forName("com.mysql.jdbc.Driver");
			conn = DriverManager
					.getConnection("jdbc:"+ DB + "?user=" + USER + "&password=" + PASSWORD + "&useSSL=false");

			String json = jsonGetRequest("https://api.iextrading.com/1.0/ref-data/symbols?filter=symbol");
			
			JsonElement root = new JsonParser().parse(json);
			JsonArray arr = root.getAsJsonArray();
			
			Vector<String> symbols = new Vector<String>();
			
			for (int i = 0; i < arr.size(); i++) {
				JsonObject curr = arr.get(i).getAsJsonObject();
				String symbol = curr.getAsJsonPrimitive("symbol").getAsString();
				symbols.add(symbol);
			}
			
			int start = 0;
			String url = "https://api.iextrading.com/1.0/stock/market/batch?types=company&symbols=";
			for (int i = 0; i <= symbols.size(); i++) {
				 if ((i != 0 && i % 100 == 0) || i == symbols.size()) {
					url = url.substring(0,url.length()-1);
					String dJson = jsonGetRequest(url);
					
					// reset the url string and query string
					String query = "INSERT INTO Stocks (companyName, symbol, companyDescription) VALUES";
					if (i != symbols.size()) {
						url = "https://api.iextrading.com/1.0/stock/market/batch?types=company&symbols=";
						try {
							String encodedSymbol = URLEncoder.encode(symbols.get(i), "UTF-8");
							url += encodedSymbol + ",";
						} catch (UnsupportedEncodingException e) {
							System.out.println(e.getMessage());
						}
					}
					
					// parse retrieved JSON
					JsonObject obj = new JsonParser().parse(dJson).getAsJsonObject();
					
					for (int j = start; j < i; j++) {
						JsonObject curr = obj.getAsJsonObject(symbols.get(j)).getAsJsonObject("company");
						if (!curr.get("companyName").isJsonNull()) {
							String companyName = curr.getAsJsonPrimitive("companyName").getAsString();
							if (companyName.length() != 0) {
								String symbol = curr.getAsJsonPrimitive("symbol").getAsString();
								String description = curr.getAsJsonPrimitive("description").getAsString();
								description = StringEscapeUtils.escapeJava(description);
								query += " (\"" + companyName + "\",\"" + symbol + "\",\"" + description + "\"),";
								System.out.println(symbol + ": " + companyName);
							}
						}
					}
					query = query.substring(0, query.length()-1) + ";";
					st = conn.createStatement();
					st.executeUpdate(query);
					
					// Add the information about the company to the sql string
					start = i;
				}
				else {
					try {
						String encodedSymbol = URLEncoder.encode(symbols.get(i), "UTF-8");
						url += encodedSymbol + ",";
					} catch (UnsupportedEncodingException e) {
						System.out.println(e.getMessage());
					}		
				}
			}
			
		} catch (SQLException sqle) {
			System.out.println("sqle: " + sqle.getMessage());
		} catch (ClassNotFoundException cnfe) {
			System.out.println("cnfe: " + cnfe.getMessage());
		} finally {
			try {
				if (rs != null) {
					conn.close();
				}
				if (st != null) {
					st.close();
				}
				if (conn != null) {
					conn.close();
				}
			} catch (SQLException sqle) {
				System.out.println("sqle closing streams: " + sqle.getMessage());
			}
		}
	}

	private static String jsonGetRequest(String urlQueryString) {
		String json = null;
		try {
			URL url = new URL(urlQueryString);
			HttpURLConnection connection = (HttpURLConnection) url.openConnection();
			connection.setDoOutput(true);
			connection.setInstanceFollowRedirects(false);
			connection.setRequestMethod("GET");
			connection.setRequestProperty("Content-Type", "application/json");
			connection.setRequestProperty("charset", "utf-8");
			connection.connect();
			InputStream inStream = connection.getInputStream();
			json = streamToString(inStream); // input stream to string
		} catch (IOException ex) {
			ex.printStackTrace();
		}
		return json;
	}

	private static String streamToString(InputStream inputStream) {
		Scanner scan = new Scanner(inputStream, "UTF-8");
		String text = scan.useDelimiter("\\Z").next();
		scan.close();
		return text;
	}
}

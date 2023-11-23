using System.Net.Http.Headers;
using MySqlConnector;
using YamlDotNet.Serialization;

namespace MythicalDash
{
    public class SettingsHandler
    {
        FileManager fm = new FileManager();
#pragma warning disable
        public static string connectionString;

        public void Setup()
        {
            if (fm.ConfigExists() == true)
            {
                Program.logger.Log(LogType.Info, "Hi, and welcome to the automated installer for MythicalDash.");
                Program.logger.Log(LogType.Info, "This installer will help you set up your dashboard with no problem and is easy to follow. \n");
                Console.Write("Name [");
                Console.ForegroundColor = ConsoleColor.Yellow;
                Console.Write($"MythicalSystems");
                Console.ResetColor();
                Console.Write("]: ");

                string name = Console.ReadLine();
                if (string.IsNullOrWhiteSpace(name))
                {
                    name = "MythicalSystems";
                }

                Console.Write("Logo [");
                Console.ForegroundColor = ConsoleColor.Yellow;
                Console.Write($"https://avatars.githubusercontent.com/u/117385445");
                Console.ResetColor();
                Console.Write("]: ");

                string logo = Console.ReadLine();
                if (string.IsNullOrWhiteSpace(logo))
                {
                    logo = "https://avatars.githubusercontent.com/u/117385445";
                }

                Console.Write("Pterodactyl URL [");
                Console.ForegroundColor = ConsoleColor.Yellow;
                Console.Write($"https://panel.yourhost.net");
                Console.ResetColor();
                Console.Write("]: ");

                string panelurl = Console.ReadLine();
                if (string.IsNullOrWhiteSpace(panelurl))
                {
                    panelurl = "https://panel.yourhost.net";
                }

                Console.Write("Pterodactyl API [");
                Console.ForegroundColor = ConsoleColor.Yellow;
                Console.Write($"ptla_000000000000000000000000000000000000000000");
                Console.ResetColor();
                Console.Write("]: ");

                string panelkey = Console.ReadLine();
                if (string.IsNullOrWhiteSpace(panelkey))
                {
                    panelkey = "ptla_000000000000000000000000000000000000000000";
                }
                try
                {
                    TestPterodactylConnection(panelurl, panelkey);
                    getConnection();
                    using (var connection = new MySqlConnection(connectionString))
                    {
                        connection.Open();
                        ExecuteSQLScript(connection, "INSERT INTO `mythicaldash_settings` (`name`, `logo`, `PterodactylURL`, `PterodactylAPIKey`) VALUES ('" + name + "', '" + logo + "', '" + panelurl + "','" + panelkey + "');");
                        connection.Close();
                    }
                }
                catch (Exception ex)
                {
                    Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                }

            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
        public void setLang(string lang) {
            if (fm.ConfigExists() == true)
            { 
                
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
        public void SetMaintenance(bool status)
        {
            try
            {
                if (status == true)
                {
                    if (fm.ConfigExists() == true)
                    {
                        try
                        {
                            getConnection();
                            using (var connection = new MySqlConnection(connectionString))
                            {
                                connection.Open();
                                ExecuteSQLScript(connection, "UPDATE `mythicaldash_settings` SET `maintenance` = 'true' WHERE `mythicaldash_settings`.`id` = 1;");
                                connection.Close();
                            }
                            string logDirectory = "logs";
                            string[] logFiles = Directory.GetFiles(logDirectory);
                            foreach (string logFile in logFiles)
                            {
                                File.Delete(logFile);
                            }
                            Program.logger.Log(LogType.Info, "Done");
                        }
                        catch (Exception ex)
                        {
                            Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                        }
                    }
                    else
                    {
                        Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
                    }
                }
                else if (status == false)
                {
                    if (fm.ConfigExists() == true)
                    {
                        try
                        {
                            getConnection();
                            using (var connection = new MySqlConnection(connectionString))
                            {
                                connection.Open();
                                ExecuteSQLScript(connection, "UPDATE `mythicaldash_settings` SET `maintenance` = 'false' WHERE `mythicaldash_settings`.`id` = 1;");
                                connection.Close();
                            }
                            string logDirectory = "logs";
                            string[] logFiles = Directory.GetFiles(logDirectory);
                            foreach (string logFile in logFiles)
                            {
                                File.Delete(logFile);
                            }
                            Program.logger.Log(LogType.Info, "Done");
                        }
                        catch (Exception ex)
                        {
                            Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                        }
                    }
                    else
                    {
                        Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
                    }
                }
            }
            catch (Exception ex)
            {
                Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
            }
        }

        public void PurgeCache()
        {
            if (fm.ConfigExists() == true)
            {
                try
                {
                    getConnection();
                    using (var connection = new MySqlConnection(connectionString))
                    {
                        connection.Open();
                        ExecuteSQLScript(connection, "TRUNCATE `mythicaldash_servers_logs`");
                        ExecuteSQLScript(connection, "TRUNCATE `mythicaldash_resetpasswords`");
                        ExecuteSQLScript(connection, "TRUNCATE `mythicaldash_login_logs`");
                        connection.Close();
                    }
                    string logDirectory = "logs";
                    string[] logFiles = Directory.GetFiles(logDirectory);
                    foreach (string logFile in logFiles)
                    {
                        File.Delete(logFile);
                    }
                    Program.logger.Log(LogType.Info, "Done");
                }
                catch (Exception ex)
                {
                    Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                }
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }

        }
        public void DisableAntiVPN()
        {
            if (fm.ConfigExists() == true)
            {
                try
                {
                    getConnection();
                    using (var connection = new MySqlConnection(connectionString))
                    {
                        connection.Open();
                        ExecuteSQLScript(connection, "UPDATE `mythicaldash_settings` SET `enable_anti_vpn` = 'false' WHERE `mythicaldash_settings`.`id` = 1;");
                        connection.Close();
                    }
                    string logDirectory = "logs";
                    string[] logFiles = Directory.GetFiles(logDirectory);
                    foreach (string logFile in logFiles)
                    {
                        File.Delete(logFile);
                    }
                    Program.logger.Log(LogType.Info, "Done");
                }
                catch (Exception ex)
                {
                    Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                }
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
        public void DisableTurnstile()
        {
            if (fm.ConfigExists() == true)
            {
                try
                {
                    getConnection();
                    using (var connection = new MySqlConnection(connectionString))
                    {
                        connection.Open();
                        ExecuteSQLScript(connection, "UPDATE `mythicaldash_settings` SET `enable_turnstile` = 'false' WHERE `mythicaldash_settings`.`id` = 1;");
                        connection.Close();
                    }
                    string logDirectory = "logs";
                    string[] logFiles = Directory.GetFiles(logDirectory);
                    foreach (string logFile in logFiles)
                    {
                        File.Delete(logFile);
                    }
                    Program.logger.Log(LogType.Info, "Done");
                }
                catch (Exception ex)
                {
                    Program.logger.Log(LogType.Error, "Sorry but the auto settings throws this error: " + ex.Message);
                }
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }

        }
        private static bool TestPterodactylConnection(string panelUrl, string panelApiKey)
        {
            try
            {
                using (var httpClient = new HttpClient())
                {
                    string usersEndpoint = "/api/application/users";

                    httpClient.BaseAddress = new Uri(panelUrl);
                    httpClient.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));
                    httpClient.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", panelApiKey);

                    HttpResponseMessage response = httpClient.GetAsync(usersEndpoint).Result;

                    if (response.IsSuccessStatusCode)
                    {
                        Program.logger.Log(LogType.Info, "Pterodactyl panel connection test successful.");
                        return true;
                    }
                    else
                    {
                        Program.logger.Log(LogType.Error, "Pterodactyl panel connection test failed. Status code: " + response.StatusCode);
                        return false;
                    }
                }
            }
            catch (Exception ex)
            {
                Program.logger.Log(LogType.Error, "Pterodactyl panel connection test failed with an exception: " + ex.Message);
                return false;
            }
        }

        private void ExecuteSQLScript(MySqlConnection connection, string scriptContent)
        {
            using (var command = new MySqlCommand(scriptContent, connection))
            {
                command.ExecuteNonQuery();
            }
        }
        private void getConnection()
        {
            if (fm.ConfigExists() == true)
            {
                string filePath = "config.yml";
                string yamlContent = File.ReadAllText(filePath);

                var deserializer = new DeserializerBuilder().Build();
                var yamlObject = deserializer.Deserialize(new StringReader(yamlContent));
#pragma warning disable
                var databaseSettings = (yamlObject as dynamic)["database"];
#pragma warning restore
                string dbHost = databaseSettings["host"];
                string dbPort = databaseSettings["port"];
                string dbUsername = databaseSettings["username"];
                string dbPassword = databaseSettings["password"];
                string dbName = databaseSettings["database"];
                connectionString = $"Server={dbHost};Port={dbPort};User ID={dbUsername};Password={dbPassword};Database={dbName}";
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
#pragma warning restore
    }
}
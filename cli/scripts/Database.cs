using MySqlConnector;
using YamlDotNet.RepresentationModel;

namespace MythicalDash
{
    public class Database
    {
        FileManager fm = new FileManager();
        private static string ReadPasswordInput()
        {
            string password = "";
            while (true)
            {
                ConsoleKeyInfo key = Console.ReadKey(true);
                if (key.Key == ConsoleKey.Enter)
                {
                    Console.WriteLine();
                    break;
                }
                else if (key.Key == ConsoleKey.Backspace)
                {
                    if (password.Length > 0)
                    {
                        password = password.Remove(password.Length - 1);
                        Console.Write("\b \b");
                    }
                }
                else
                {
                    password += key.KeyChar;
                    Console.Write("*");
                }
            }
            return password;
        }
        public void Configurator()
        {
#pragma warning disable
            Program.logger.Log(LogType.Info, "Hi, please fill in your database configuration for MythicalDash.");
            string defaultHost = "localhost";
            string defaultPort = "3306";
            string defaultUsername = "mythicaldash";
            string deafultdbName = "mythicaldash";

            Console.Write("Host [");
            Console.ForegroundColor = ConsoleColor.Yellow;
            Console.Write($"{defaultHost}");
            Console.ResetColor();
            Console.Write("]: ");

            string host = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(host))
            {
                host = defaultHost;
            }

            Console.Write("Port [");
            Console.ForegroundColor = ConsoleColor.Yellow;
            Console.Write($"{defaultPort}");
            Console.ResetColor();
            Console.Write("]: ");

            string port = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(port))
            {
                port = defaultPort;
            }

            Console.Write("Username [");
            Console.ForegroundColor = ConsoleColor.Yellow;
            Console.Write($"{defaultUsername}");
            Console.ResetColor();
            Console.Write("]: ");

            string username = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(username))
            {
                username = defaultUsername;
            }

            Console.Write("Password: ");
            string password = ReadPasswordInput();

            Console.Write("Database Name [");
            Console.ForegroundColor = ConsoleColor.Yellow;
            Console.Write($"{deafultdbName}");
            Console.ResetColor();
            Console.Write("]: ");

            string dbName = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(dbName))
            {
                dbName = deafultdbName;
            }
#pragma warning restore
            if (string.IsNullOrEmpty(host) || string.IsNullOrEmpty(port) || string.IsNullOrEmpty(username) || string.IsNullOrEmpty(password) || string.IsNullOrEmpty(dbName))
            {
                Program.logger.Log(LogType.Error, "Invalid input. Please provide all the required values.");
                Environment.Exit(0x0);
            }
            try
            {
                using var connection = new MySqlConnection($"Server={host};Port={port};User ID={username};Password={password};Database={dbName}");
                connection.Open();
                Program.logger.Log(LogType.Info, "Connected to MySQL, saving database configuration to config.");
                connection.Close();
                UpdateConfig(host, port, username, password, dbName);
                Program.logger.Log(LogType.Info, "Done we saved your MySQL connection to your config file");
                Environment.Exit(0x0);
            }
            catch (Exception ex)
            {
                Program.logger.Log(LogType.Error, $"Failed to connect to MySQL: {ex.Message}");
                Environment.Exit(0x0);
            }
        }
        public void Rebuild()
        {
            if (fm.ConfigExists() == true)
            {
                if (File.Exists("/var/www/mythicaldash/migrates.ini")) {
                    File.Delete("/var/www/mythicaldash/migrates.ini");
                }
                Migrate m = new Migrate();
                m.Now();
                Environment.Exit(0x0);
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
        public void UpdateConfig(string host, string port, string username, string password, string dbname)
        {
            if (fm.ConfigExists() == true)
            {
                string filePath = "/var/www/mythicaldash/config.yml";
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["database"];

                appSection.Children[new YamlScalarNode("host")] = new YamlScalarNode(host);
                appSection.Children[new YamlScalarNode("port")] = new YamlScalarNode(port);
                appSection.Children[new YamlScalarNode("username")] = new YamlScalarNode(username);
                appSection.Children[new YamlScalarNode("password")] = new YamlScalarNode(password);
                appSection.Children[new YamlScalarNode("database")] = new YamlScalarNode(dbname);

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                }
                Program.RemoveTrailingDots();
                Program.logger.Log(LogType.Info, "We updated the settings");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
    }
}
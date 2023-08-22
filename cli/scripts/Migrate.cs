using MySqlConnector;
using YamlDotNet.Serialization;

namespace MythicalDash
{
    public class Migrate
    {
        FileManager fm = new FileManager();
#pragma warning disable
        public static string connectionString;
#pragma warning restore
        public void Now()
        {
            if (fm.MFolderExists() == true)
            {
                ExecuteScripts();
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like you are missing some important core files; please redownload MythicalDash!!");
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
        private void ExecuteScript(MySqlConnection connection, string scriptContent)
        {
            using (var command = new MySqlCommand(scriptContent, connection))
            {
                command.ExecuteNonQuery();
            }
        }
        private void ExecuteScripts()
        {
            try
            {
                getConnection();
                string[] scriptFiles = Directory.GetFiles("migrate/", "*.sql")
                    .OrderBy(scriptFile => Convert.ToInt32(Path.GetFileNameWithoutExtension(scriptFile)))
                    .ToArray();

                using (var connection = new MySqlConnection(connectionString))
                {
                    connection.Open();

                    foreach (string scriptFile in scriptFiles)
                    {
                        string scriptContent = File.ReadAllText(scriptFile);
                        Program.logger.Log(LogType.Info, "We executed: " + scriptFile);
                        ExecuteScript(connection, scriptContent);
                    }

                    connection.Close();
                }

            }
            catch (Exception ex)
            {
                Program.logger.Log(LogType.Error, "Sorry but the migration throws this error: " + ex.Message);
            }
        }
    }
}
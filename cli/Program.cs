using System;
using System.Text.RegularExpressions;
using YamlDotNet.RepresentationModel;


namespace MythicalDash
{
    public class Program
    {
        public static string ascii = @" 
  __  __       _   _     _           _ _____            _     
 |  \/  |     | | | |   (_)         | |  __ \          | |    
 | \  / |_   _| |_| |__  _  ___ __ _| | |  | | __ _ ___| |__  
 | |\/| | | | | __| '_ \| |/ __/ _` | | |  | |/ _` / __| '_ \ 
 | |  | | |_| | |_| | | | | (_| (_| | | |__| | (_| \__ \ | | |
 |_|  |_|\__, |\__|_| |_|_|\___\__,_|_|_____/ \__,_|___/_| |_|
          __/ |                                               
         |___/                                                                                                                 
";
        public static string version = "1.0.0";
        public static bool skiposcheck = false;
        public static Logger logger = new Logger();
        public static void Main(string[] args)
        {
            Console.Clear();
            Console.WriteLine(ascii);
            if (skiposcheck == false)
            {
                if (!System.OperatingSystem.IsLinux())
                {
                    logger.Log(LogType.Error, "Sorry but this app runs on linux!");
                    Environment.Exit(0x0);
                }
            }
            if (args.Contains("-generate-config"))
            {
                try
                {   string filePath = "config.yml";

                    if (File.Exists(filePath))
                    {
                        logger.Log(LogType.Error,"Sorry, but it looks like you already have the conifg.yml file. We can not execute this command, or there is going to be a huge data loss!");
                        Environment.Exit(0x0);
                    }
                    var configData = new YamlMappingNode
                    {
                        {"app", new YamlMappingNode
                            {
                                {"debug", "false"},
                                {"silent_debug", "false"},
                                {"encryptionkey", "<keyhere>"},
                                {"disable_console", "false"}
                            }
                        },
                        {"database", new YamlMappingNode
                            {
                                {"host", "127.0.0.1"},
                                {"port", "3306"},
                                {"username", "<usernamehere>"},
                                {"password", "<passwordhere>"},
                                {"database", "mythicaldash"}
                            }
                        }
                    };

                    var yaml = new YamlStream(new YamlDocument(configData));

                    using (var writer = new StreamWriter(filePath))
                    {
                        yaml.Save(writer, false);
                    }
                    RemoveTrailingDots(filePath);
                    static void RemoveTrailingDots(string filePath)
                    {
                        string yamlContent = File.ReadAllText(filePath);
                        string pattern = @"(?<=\S)\s*\.\.\.\s*$";
                        string replacement = string.Empty;
            
                        string newContent = Regex.Replace(yamlContent, pattern, replacement, RegexOptions.Multiline);
                        File.WriteAllText(filePath, newContent);
                    }
                }
                catch (Exception ex)
                {
                    logger.Log(LogType.Error, "Faild to create config: " + ex.Message);
                }
                logger.Log(LogType.Info, "Configuration file generated.");
                Environment.Exit(0x0);
            }
            if (args.Contains("-version"))
            {
                logger.Log(LogType.Info, "You are running version: " + version);
                Environment.Exit(0x0);
            }
            else if (args.Length > 0)
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            }
            else
            {
                logger.Log(LogType.Error, "This is an invalid startup argument. Please use '-help' to get more information");
                Environment.Exit(0x0);
            }
        }

    }

}
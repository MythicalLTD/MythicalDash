using System.Text.RegularExpressions;
using YamlDotNet.RepresentationModel;
using System;

namespace MythicalDash
{
    public class ConfigHandler
    {
        public void DeleteConfig() {
            string filePath = "config.yml"; 
            if (File.Exists(filePath)) {
                File.Delete(filePath);
                Program.logger.Log(LogType.Info, "Done we purged the config.yml file");
            } else {
                Program.logger.Log(LogType.Error,"We cant find the default config file please make sure you read our docs");
                Environment.Exit(0x0);
            }
        }
        public void CreateConfig()
        {
            string filePath = "config.yml";

            if (File.Exists(filePath))
            {
                Program.logger.Log(LogType.Error, "Sorry, but it looks like you already have the conifg.yml file. We can not execute this command, or there is going to be a huge data loss!");
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
            Program.logger.Log(LogType.Info,"Done we created the config file");
            static void RemoveTrailingDots(string filePath)
            {
                string yamlContent = File.ReadAllText(filePath);
                string pattern = @"(?<=\S)\s*\.\.\.\s*$";
                string replacement = string.Empty;

                string newContent = Regex.Replace(yamlContent, pattern, replacement, RegexOptions.Multiline);
                File.WriteAllText(filePath, newContent);
            }
        }
    }
}
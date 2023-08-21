using YamlDotNet.RepresentationModel;

namespace MythicalDash
{
    public class Debug
    {
        FileManager fm = new FileManager();
        public void disable()
        {
            if (fm.Exist() == true)
            {
                string filePath = "config.yml"; 
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("debug")] = new YamlScalarNode("false"); 

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                } 
                Program.logger.Log(LogType.Info,"We updated the settings");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }

        public void disable_silent()
        {
            if (fm.Exist() == true)
            {
                string filePath = "config.yml"; 
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("silent_debug")] = new YamlScalarNode("false"); 

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                } 
                Program.logger.Log(LogType.Info,"We updated the settings");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
        public void enable_silent()
        {
           if (fm.Exist() == true)
            {
                string filePath = "config.yml"; 
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("silent_debug")] = new YamlScalarNode("true"); 

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                }
                Program.logger.Log(LogType.Warning,"We updated the settings please make sure to not use this as a production environment");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }

        public void enable()
        {
           if (fm.Exist() == true)
            {
                string filePath = "config.yml"; 
                var yaml = new YamlStream();

                using (var reader = new StreamReader(filePath))
                {
                    yaml.Load(reader);
                }

                var mapping = (YamlMappingNode)yaml.Documents[0].RootNode;
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("debug")] = new YamlScalarNode("true"); 

                using (var writer = new StreamWriter(filePath))
                {
                    yaml.Save(writer, false);
                }
                Program.logger.Log(LogType.Warning,"We updated the settings please make sure to not use this as a production environment");
            }
            else
            {
                Program.logger.Log(LogType.Error, "It looks like the config file does not exist!");
            }
        }
    }
}
using YamlDotNet.RepresentationModel;

namespace MythicalDash
{
    public class IConsole
    {
        FileManager fm = new FileManager();
        public void disable()
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
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("disable_console")] = new YamlScalarNode("true");

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
        public void enable()
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
                var appSection = (YamlMappingNode)mapping["app"];

                appSection.Children[new YamlScalarNode("disable_console")] = new YamlScalarNode("false");

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